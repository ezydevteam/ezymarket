<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Enums\Product\ProductReportStatus;
use App\Models\Product\{Product, ProductReport, ProductReportSetting};
use App\Facades\Notification;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Database\Eloquent\Builder;

class ProductReportsController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of product reports.
     */
    public function index(Request $request): View
    {
        $query = ProductReport::with(['product', 'user', 'reviewedBy']);
        $this->applyFilters($query, $request);

        $reports = $query->get();
        $productReportSettings = ProductReportSetting::getInstance();

        return view('admin.reports.product-reports.index', compact('reports', 'productReportSettings'));
    }

    /**
     * Update the specified product report.
     */
    public function updateStatus(Request $request, ProductReport $report): JsonResponse
    {
        $validation = $this->validateRequestJson($request, [
            'status' => ['required', 'in:reviewed,resolved,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        try {
            $report->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'reviewed_by_id' => authAdmin()->id,
                'reviewed_at' => now(),
            ]);

            // Eager load the necessary relationships
            $product = Product::with('seller')->findOrFail($report->product_id);
            $report->load(['user']);

            // Notify Seller and reporter
            Notification::sendProductReportStatusNotification($product, $report, $request->status);

            // Process auto-actions after status update
            $this->processAutoActions($product);

            return $this->successJson('Report status updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while updating the report');
        }
    }

    /**
     * Bulk resolve reports
     */
    public function bulkResolve(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $count = 0;
                $reports = ProductReport::whereIn('id', $ids)->with(['product.seller', 'user'])->get();

                foreach ($reports as $report) {
                    // Skip if already resolved
                    if ($report->isResolved()) {
                        continue;
                    }

                    if ($report->canBeActioned()) {
                        $report->markAsResolved(authAdmin()->id());

                        if ($report->product) {
                            Notification::sendProductReportStatusNotification(
                                $report->product,
                                $report,
                                'resolved'
                            );
                            $this->processAutoActions($report->product);
                        }
                        $count++;
                    }
                }

                return $count;
            },
            ProductReport::class,
            ':count report(s) resolved successfully',
            'An error occurred while resolving reports'
        );
    }

    /**
     * Bulk cancel reports
     */
    public function bulkCancel(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $count = 0;
                $reports = ProductReport::whereIn('id', $ids)->with(['product.seller', 'user'])->get();

                foreach ($reports as $report) {
                    // Skip if already cancelled
                    if ($report->isCancelled()) {
                        continue;
                    }

                    if ($report->canBeActioned()) {
                        $report->markAsCancelled(authAdmin()->id());

                        if ($report->product) {
                            Notification::sendProductReportStatusNotification(
                                $report->product,
                                $report,
                                'cancelled'
                            );
                            $this->processAutoActions($report->product);
                        }
                        $count++;
                    }
                }

                return $count;
            },
            ProductReport::class,
            ':count report(s) cancelled successfully',
            'An error occurred while cancelling reports'
        );
    }

    /**
     * Bulk delete reports
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $count = 0;
                $reports = ProductReport::whereIn('id', $ids)->with('product')->get();

                foreach ($reports as $report) {
                    $product = $report->product;
                    $report->delete();

                    // Auto-unrestrict if no more reports
                    if ($product && $product->reportCounter() == 0 && $product->isRestricted()) {
                        $product->unrestrict();
                    }
                    $count++;
                }

                return $count;
            },
            ProductReport::class,
            ':count report(s) deleted successfully',
            'An error occurred while deleting reports'
        );
    }

    /**
     * Delete a report
     */
    public function destroy(ProductReport $report): JsonResponse
    {
        try {
            $product = $report->product;
            $wasRestricted = $product && $product->isRestricted();
            $remainingReports = $product ? $product->reportCounter() - 1 : 0;

            $report->delete();

            // Auto-unrestrict if no more reports
            if ($product && $remainingReports == 0 && $wasRestricted) {
                $product->unrestrict();
                return $this->successJson('Report deleted successfully. Product has been unrestricted as it has no more reports.');
            }

            return $this->successJson('Report deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while deleting the report');
        }
    }

    /**
     * Restrict a product (AJAX)
     */
    public function restrictProduct(Product $product): JsonResponse
    {
        try {
            if ($product->isRestricted()) {
                return $this->errorJson('Product is already restricted');
            }

            $product->restrict('Multiple reports received');

            // Mark all pending reports as resolved
            $product->reports()->pending()->update([
                'status' => ProductReportStatus::RESOLVED,
                'reviewed_by_id' => authAdmin()->id,
                'reviewed_at' => now(),
                'admin_notes' => 'Product restricted by admin',
            ]);

            $firstReport = $product->reports()->where('status', ProductReportStatus::RESOLVED)
                ->where('reviewed_by_id', authAdmin()->id)
                ->first();

            if ($firstReport) {
                $product->load('seller');
                Notification::sendProductReportStatusNotification($product, $firstReport, 'restricted');
            }

            return $this->successJson('Product has been restricted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while restricting the product');
        }
    }

    /**
     * Unrestrict a product (AJAX)
     */
    public function unrestrictProduct(Product $product): JsonResponse
    {
        try {
            if (!$product->isRestricted()) {
                return $this->errorJson('Product is not restricted');
            }

            $product->load('seller');

            // Get a representative report for notification
            $reportForNotification = $product->reports()
                ->where('status', ProductReportStatus::RESOLVED)
                ->latest('created_at')
                ->first();

            if (!$reportForNotification) {
                $reportForNotification = $product->reports()->latest('created_at')->first();
            }

            $product->unrestrict();

            if ($reportForNotification) {
                $reportForNotification->load('user');
                Notification::sendProductReportStatusNotification($product, $reportForNotification, 'un_restricted');
            }

            return $this->successJson('Product has been unrestricted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while unrestricting the product');
        }
    }

    /**
     * Delete a product (AJAX)
     */
    public function deleteProduct(Product $product): JsonResponse
    {
        try {
            // Get a representative report for notification
            $reportForNotification = $product->reports()->latest('created_at')->first();

            // Mark all reports as resolved before deleting
            $product->reports()->update([
                'status' => ProductReportStatus::RESOLVED,
                'reviewed_by_id' => authAdmin()->id,
                'reviewed_at' => now(),
                'admin_notes' => 'Product deleted by admin',
            ]);

            $product->softDelete();

            if ($reportForNotification) {
                Notification::sendProductReportStatusNotification($product, $reportForNotification, 'deleted');
            }

            return $this->successJson('Product has been deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while deleting the product');
        }
    }

    // ==================== Private Methods ====================
    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller')) {
            $query->where('user_id', $request->seller);
        }

        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }
    }

    /**
     * Process auto-actions for a product based on current settings.
     */
    private function processAutoActions(Product $product): void
    {
        $settings = ProductReportSetting::getInstance();
        $actions = $settings->applyProductActions($product);
    }

    /**
     * Update product report settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validation = $this->validateRequestJson($request, [
            'restrict_threshold' => 'required|integer|min:1|max:1000',
            'delete_threshold' => 'required|integer|min:1|max:1000|gt:restrict_threshold',
            'reporter_threshold' => 'required|integer|min:1|max:1000',
            'restrict_days' => 'required|integer|min:1|max:365',
            'reporter_days' => 'required|integer|min:1|max:365',
            'auto_restrict' => 'nullable|boolean',
            'auto_delete' => 'nullable|boolean',
            'restrict_reporter' => 'nullable|boolean',
        ], [
            'delete_threshold.gt' => translate('Deletion threshold must be higher than restriction threshold'),
            'restrict_days.min' => translate('Restriction period must be at least 1 day'),
            'restrict_days.max' => translate('Restriction period cannot exceed 365 days'),
            'reporter_days.min' => translate('Reporter restriction period must be at least 1 day'),
            'reporter_days.max' => translate('Reporter restriction period cannot exceed 365 days'),
        ]);

        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        try {
            $productReportSettings = ProductReportSetting::getInstance();
            $productReportSettings->updateSettings([
                'restrict_threshold' => $request->restrict_threshold,
                'delete_threshold' => $request->delete_threshold,
                'reporter_threshold' => $request->reporter_threshold,
                'restrict_days' => $request->restrict_days,
                'reporter_days' => $request->reporter_days,
                'auto_restrict' => $request->has('auto_restrict'),
                'auto_delete' => $request->has('auto_delete'),
                'restrict_reporter' => $request->has('restrict_reporter'),
            ]);

            return $this->successJson('Settings updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->errorJson($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while updating settings');
        }
    }
}
