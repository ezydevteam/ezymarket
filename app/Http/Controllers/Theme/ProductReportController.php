<?php

namespace App\Http\Controllers\Theme;

use App\Enums\Product\ProductReportStatus;
use App\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Models\Product\{Product, ProductReport, ProductReportSetting};
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, DB};

class ProductReportController extends Controller
{
    use HandlesValidation;

    /**
     * Show the report form
     */
    public function create(Product $product)
    {
        if ($this->hasUserReportedProduct($product)) {
            return $this->errorBack('You have already reported this product');
        }

        return view('products.report', [
            'product' => $product,
            'reportReasons' => ProductReport::getReasonOptions(),
        ]);
    }

    /**
     * Store the report
     */
    public function store(Request $request, string $slug, Product $product): JsonResponse
    {
        $validation = $this->validateRequestJson($request, [
            'reason' => 'required|string|in:' . implode(',', array_keys(ProductReport::getReasonOptions())),
            'description' => 'required|string|max:1000',
            'screenshots.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'screenshots' => 'array|max:3',
        ], [
            'reason.required' => translate('Please select a reason for reporting'),
            'reason.in' => translate('Please select a valid reason'),
            'description.required' => translate('Description is required'),
            'description.max' => translate('Description cannot exceed 1000 characters'),
            'screenshots.*.image' => translate('Screenshots must be images'),
            'screenshots.*.mimes' => translate('Screenshots must be JPEG, JPG, or PNG format'),
            'screenshots.*.max' => translate('Each screenshot must be less than 2MB'),
            'screenshots.max' => translate('Maximum 3 screenshots are allowed'),
        ]);

        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        if ($this->hasUserReportedProduct($product)) {
            return $this->errorJson('You have already reported this product');
        }

        if ($product->seller_id === Auth::id()) {
            return $this->errorJson('You cannot report your own product');
        }

        try {
            $this->processReport($validation->validated(), $product);
            return $this->successJson('Your report has been submitted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while submitting your report');
        }
    }

    /**
     * Check if user can report the product
     */
    public function canReport(Product $product): JsonResponse
    {
        $canReport = !$this->hasUserReportedProduct($product) && $product->seller_id !== Auth::id();

        return response()->json([
            'can_report' => $canReport,
            'message' => $canReport ? null : translate('You cannot report this product'),
        ]);
    }

    /**
     * Check if user has already reported the product
     */
    private function hasUserReportedProduct(Product $product): bool
    {
        return ProductReport::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Process the report and handle auto-actions
     */
    private function processReport(array $data, Product $product): void
    {
        DB::transaction(function () use ($data, $product) {
            $report = ProductReport::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'reason' => $data['reason'],
                'description' => $data['description'],
                'screenshots' => $this->uploadScreenshots(),
                'status' => ProductReportStatus::PENDING,
            ]);

            $settings = ProductReportSetting::getInstance();
            $actions = $settings->applyProductActions($product);

            $this->sendNotifications($product, $report, $actions);
        });
    }

    /**
     * Upload screenshots and return paths
     */
    private function uploadScreenshots(): array
    {
        if (!request()->hasFile('screenshots')) {
            return [];
        }

        $paths = [];
        foreach (array_slice(request()->file('screenshots'), 0, 3) as $screenshot) {
            $paths[] = storageFileUpload($screenshot, 'products-report/', 'public');
        }

        return $paths;
    }

    /**
     * Send notifications based on actions taken
     */
    private function sendNotifications(Product $product, ProductReport $report, array $actions): void
    {
        if (in_array('deleted', $actions)) {
            Notification::sendProductReportStatusNotification($product, $report, 'deleted');
            return;
        }

        if (in_array('restricted', $actions)) {
            Notification::sendProductReportStatusNotification($product, $report, 'restricted');
        } else {
            Notification::sendProductReportStatusNotification($product, $report, 'un_restricted');
        }

        // Notify admins about high-priority reports (10+ reports)
        if ($product->reportCounter() >= 10) {
            Notification::sendAdminNotification(
                translate('Product [#:product_id] has received :count reports', [
                    'product_id' => $product->id,
                    'count' => $product->reportCounter(),
                ]),
                $product->thumbnail_url,
                route('admin.reports.product-reports.index')
            );
        }
    }

    /**
     * Get report statistics for a product
     */
    public function getReportStatistics(Product $product): JsonResponse
    {
        $stats = $product->getReportStats();

        return response()->json([
            'total_reports' => $stats['total'],
            'pending_reports' => $stats['pending'],
            'reviewed_reports' => $stats['reviewed'],
            'resolved_reports' => $stats['resolved'],
            'cancelled_reports' => $stats['cancelled'],
        ]);
    }

}
