<?php

namespace App\Http\Controllers\Admin\Products;

use App\Enums\BadgeAlias;
use App\Enums\Product\{ProductHistoryTitle, ProductStatus};
use App\Http\Controllers\Controller;
use App\Models\{User, Badge, Sale};
use App\Models\Product\{
    Product,
    ProductCategory,
    ProductComment,
    ProductDiscount,
    ProductHistory,
    ProductReview,
    ProductView
};
use App\Facades\Notification;
use App\Services\Statistics\StatisticsService;
use App\Traits\{HandlesValidation, HandlesSorting};
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Jenssegers\Date\Date;

/**
 * ProductController
 *
 * Handles admin operations for products including listing, status changes,
 * featuring, premium management, reviews, comments, and statistics.
 */
class ProductController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a listing of products with filters
     *
     * @return View
     */
    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getProductCounters();
        $query = Product::query()->where('status', '!=', ProductStatus::DRAFT->value);

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $products = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $products->map(fn($product) => $this->formatProductRow($product));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $productsCount = Product::count();
        $trashedCount = Product::onlyTrashed()->count();

        return view('admin.products.index', compact('counters', 'columns', 'filters', 'productsCount', 'trashedCount'));
    }

    /**
     * Display detailed product information
     *
     * @param int $id
     * @return View
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();
        $data = $this->getProductData($product, $id);
        $tab = $request->input('tab', 'details');

        // Map tabs to their partial views
        $tabsMap = [
            'details'    => 'admin.products.partials.details-content',
            'actions'    => 'admin.products.partials.actions-content',
            'history'    => 'admin.products.partials.history-content',
            'discount'   => 'admin.products.partials.discount-content',
            'reviews'    => 'admin.products.partials.reviews-content',
            'comments'   => 'admin.products.partials.comments-content',
            'statistics' => 'admin.products.partials.statistics-content',
        ];

        // Determine which partial to show
        $activePartial = $tabsMap[$tab] ?? 'admin.products.partials.details-content';

        // Add product to data so it's available in all partials
        $data['product'] = $product;
        $data['activeTab'] = $tab;
        $data['activePartial'] = $activePartial;

        if ($request->ajax()) {
            return view($activePartial, $data);
        }

        return view('admin.products.show', $data);
    }

    /**
     * Display product approval/rejection form
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function actions(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'actions');
    }

    /**
     * Process product approval or rejection (for pending/resubmitted products)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function actionsUpdate(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        // Check if product is pending review
        if (!$product->isPendingReview()) {
            return $this->errorJson('This product is not in pending status');
        }

        // Validate request
        $rules = [
            'action' => ['required', 'string', 'in:approve,needs_revision,reject'],
            'reason' => ['required_if:action,needs_revision', 'nullable', 'string', 'max:1000'],
        ];

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $action = $request->action;

            $status = match ($action) {
                'approve' => ProductStatus::APPROVED->value,
                'needs_revision' => ProductStatus::NEEDS_REVISION->value,
                'reject' => ProductStatus::REJECTED->value,
            };

            $title = match ($action) {
                'approve' => $product->isPending() ? ProductHistoryTitle::SUBMISSION_APPROVED : ProductHistoryTitle::RESUBMISSION_APPROVED,
                'needs_revision' => ProductHistoryTitle::REVISION_REQUIRED,
                'reject' => ProductHistoryTitle::REJECTION,
            };

            $success = match ($action) {
                'approve' => 'The product has been approved',
                'needs_revision' => 'The product has been sent for revision',
                'reject' => 'The product has been rejected',
            };

            // Create product history
            $history = ProductHistory::create([
                'seller_id' => $product->seller_id,
                'admin_id' => authAdmin()->id,
                'product_id' => $product->id,
                'title' => $title,
                'body' => $request->reason ?? null,
            ]);

            $product->status = $status;
            $product->save();

            // Send notification
            $notificationType = match ($action) {
                'approve' => 'approved',
                'needs_revision' => 'needs_revision',
                'reject' => 'rejected',
            };

            Notification::sendProductSubmissionStatusNotification(
                $product,
                $notificationType,
                $action !== 'approve' ? $history : null
            );

            return $this->successJson($success);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Update product status (administrative change without notification)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function actionsStatus(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $validStatuses = array_column(ProductStatus::cases(), 'value');

        $validator = $this->validateRequestJson($request, [
            'status' => ['required', 'string', 'in:' . implode(',', $validStatuses)],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            // Check if status is same as current
            if ($product->status->value === $request->status) {
                return $this->errorJson('The product already has this status');
            }

            $product->status = $request->status;
            $product->save();

            return $this->successJson('Status updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display product history
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function history(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'history');
    }

    /**
     * Delete a product history entry
     *
     * @param int $id
     * @param int $history_id
     * @return JsonResponse
     */
    public function historyDelete(int $id, int $history_id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $history = ProductHistory::where('id', $history_id)
            ->where('product_id', $product->id)->firstOrFail();

        $history->delete();

        return $this->successJson('History deleted Successfully');
    }

    /**
     * Display product discount form
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function discount(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'discount');
    }

    /**
     * Store or update product discount
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function discountStore(Request $request, int $id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)->firstOrFail();

            $validator = $this->validateRequestJson($request, [
                'regular_percentage' => ['required', 'integer', 'min:1', 'max:99'],
                'extended_percentage' => ['nullable', 'integer', 'min:1', 'max:99'],
                'starting_date' => ['required', 'date', 'after_or_equal:today'],
                'ending_date' => ['required', 'date', 'after:starting_date'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            if ($validator instanceof JsonResponse) {
                return $validator;
            }

            $validated = $validator->validated();

            // Calculate discounted prices
            $regularDiscountAmount = ($product->regular_price * $validated['regular_percentage']) / 100;
            $regularPrice = intval(ceil($product->regular_price - $regularDiscountAmount));

            $extendedPrice = null;
            $extendedPercentage = null;
            if (!empty($validated['extended_percentage']) && $product->extended_price) {
                $extendedPercentage = $validated['extended_percentage'];
                $extendedDiscountAmount = ($product->extended_price * $extendedPercentage) / 100;
                $extendedPrice = intval(ceil($product->extended_price - $extendedDiscountAmount));
            }

            // Update or create discount
            $discount = $product->discount ?? new ProductDiscount();
            $discount->product_id = $product->id;
            $discount->regular_percentage = $validated['regular_percentage'];
            $discount->regular_price = $regularPrice;
            $discount->extended_percentage = $extendedPercentage;
            $discount->extended_price = $extendedPrice;
            $discount->starting_at = $validated['starting_date'];
            $discount->ending_at = $validated['ending_date'];
            $discount->is_active = $request->boolean('is_active', false);
            $discount->save();

            // Update product discount status
            $product->is_on_discount = $discount->is_active;
            $product->last_discount_at = now();
            $product->save();

            return $this->successJson('Discount saved successfully', [
                'redirect' => route('admin.products.show', ['id' => $id, 'tab' => 'discount'])
            ]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Toggle product discount active/inactive status
     *
     * @param int $id
     * @return JsonResponse
     */
    public function discountStatus(int $id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)->firstOrFail();

            if (!$product->hasDiscount()) {
                return $this->errorJson('No discount found for this product');
            }

            $discount = $product->discount;
            $discount->is_active = !$discount->is_active;
            $discount->save();

            $product->is_on_discount = $discount->is_active;
            $product->save();

            $status = $discount->is_active ? 'activated' : 'deactivated';
            return $this->successJson("Discount {$status} successfully");
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Delete product discount
     *
     * @param int $id
     * @return JsonResponse
     */
    public function discountRemove(int $id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)->firstOrFail();

            if ($product->hasDiscount()) {
                $discount = $product->discount;
                $discount->delete();
            }

            $product->is_on_discount = false;
            $product->save();

            return $this->successJson('Discount deleted Successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display product reviews with filtering
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function reviews(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'reviews');
    }

    /**
     * Delete a product review
     *
     * @param int $id
     * @param int $review_id
     * @return JsonResponse
     */
    public function reviewsDelete(int $id, int $review_id): JsonResponse
    {
        $review = ProductReview::where('id', $review_id)
            ->where('product_id', $id)
            ->firstOrFail();

        $review->delete();
        return $this->successJson('Review deleted Successfully');
    }

    /**
     * Display product comments with filtering
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function comments(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'comments');
    }

    /**
     * Delete a product comment
     *
     * @param int $id
     * @param int $comment_id
     * @return JsonResponse
     */
    public function commentsDelete(int $id, int $comment_id): JsonResponse
    {
        $comment = ProductComment::where('id', $comment_id)
            ->where('product_id', $id)
            ->firstOrFail();

        $comment->delete();
        return $this->successJson('Comment deleted Successfully');
    }

    /**
     * Display product statistics with charts
     * Redirects to show page with all tabs loaded.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function statistics(int $id): RedirectResponse
    {
        return $this->redirectToShow($id, 'statistics');
    }


    /**
     * Export product statistics report
     *
     * @param int $id
     * @param string $format
     * @return mixed
     */
    public function exportStatistics(int $id, string $format = 'pdf')
    {
        $product = Product::where('id', $id)->firstOrFail();

        // Get period from request or use default
        $period = request()->input('period', 'this_month');
        [$startDate, $endDate] = $this->getStatisticsPeriods($period);

        // Initialize statistics service for Sales
        $salesStats = app(StatisticsService::class)
            ->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->dateRange($startDate, $endDate);

        // Get sales data with relationships
        $sales = Sale::where('product_id', $product->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->active()
            ->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate counters
        $counters = $salesStats->counters([
            'total_sales' => ['count', '*'],
            'total_sales_amount' => ['sum', 'price'],
            'total_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
        ]);

        // Add views counter from ProductView model
        $viewsStats = app(StatisticsService::class)
            ->forModel(ProductView::class)
            ->where('product_id', $product->id)
            ->dateRange($startDate, $endDate);

        $counters['total_views'] = $viewsStats->counters(['views' => ['count', '*']])['views'];

        // Add comments counter
        $commentsStats = app(StatisticsService::class)
            ->forModel(ProductComment::class)
            ->where('product_id', $product->id)
            ->dateRange($startDate, $endDate);

        $counters['total_comments'] = $commentsStats->counters(['comments' => ['count', '*']])['comments'];

        // Calculate additional metrics
        $counters['conversion_rate'] = $counters['total_views'] > 0
            ? round(($counters['total_sales'] / $counters['total_views']) * 100, 2)
            : 0;
        $counters['avg_sale_value'] = $counters['total_sales'] > 0
            ? round($counters['total_sales_amount'] / $counters['total_sales'], 2)
            : 0;

        // Get top purchasing countries using StatisticsService
        $topCountries = $salesStats->topItems('country', [
            'aggregations' => [
                'total_sales' => ['count', '*'],
                'total_revenue' => ['sum', 'price'],
                'total_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
            ],
            'orderBy' => 'total_sales',
            'limit' => 15,
        ]);

        // Filter out null countries
        $topCountries = $topCountries->filter(function ($item) {
            return !empty($item->country);
        });

        // Get referral data
        $referrals = $viewsStats->referrals([
            'field' => 'referrer',
            'limit' => 10,
        ]);

        $periodLabel = $this->getPeriodLabel($period);

        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('vendor.pdf.admin.product-statistics-report', [
            'product' => $product,
            'sales' => $sales,
            'counters' => $counters,
            'topCountries' => $topCountries,
            'referrals' => $referrals,
            'period' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->download("admin-product-{$product->id}-statistics-{$period}.pdf");
    }

    /**
     * Download product files
     *
     * @param int $id
     * @return mixed
     */
    public function download(int $id): mixed
    {
        $product = Product::where('id', $id)->firstOrFail();

        try {
            $response = $product->download();
            if (isset($response->type) && $response->type == "error") {
                throw new Exception($response->message);
            }
            return $response;
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Mark a product as featured
     *
     * @param int $id
     * @return JsonResponse
     */
    public function makeFeatured(int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $product->is_featured = true;
        $product->featured_at = now();
        $product->save();

        $this->assignBadgeToSeller($product->seller, BadgeAlias::FEATURED_PRODUCT);

        return $this->successJson('The product marked as featured successfully');
    }

    /**
     * Remove featured status from a product
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeFeatured(int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $product->is_featured = false;
        $product->featured_at = null;
        $product->save();

        return $this->successJson('The product marked as not featured successfully');
    }

    /**
     * Mark a product as premium
     *
     * @param int $id
     * @return JsonResponse
     */
    public function makePremium(int $id): JsonResponse
    {
        $product = Product::where('id', $id)->notFree()->firstOrFail();

        $product->is_premium = true;
        $product->premium_at = now();
        $product->save();

        $this->assignBadgeToSeller($product->seller, BadgeAlias::PREMIUMER);

        return $this->successJson('The product added to premium successfully');
    }

    /**
     * Remove premium status from a product
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removePremium(int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $product->is_premium = false;
        $product->premium_at = null;
        $product->save();

        return $this->successJson('The product removed from premium successfully');
    }

    /**
     * Recalculate product statistics from scratch.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function recalculateStatistics(int $id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)->firstOrFail();

            $salesData = Sale::where('product_id', $product->id)
                ->active()
                ->select([
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(price) as total_sales_amount'),
                    DB::raw('SUM(seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)) as total_earnings')
                ])
                ->first();

            $product->update([
                'total_sales' => (int) ($salesData->total_sales ?? 0),
                'total_sales_amount' => (float) ($salesData->total_sales_amount ?? 0),
                'total_earnings' => (float) ($salesData->total_earnings ?? 0),
            ]);

            return $this->successJson('Statistics data updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Assign a badge to a seller
     *
     * @param \App\Models\User $seller
     * @param BadgeAlias $badgeAlias
     * @return void
     */
    private function assignBadgeToSeller(User $seller, BadgeAlias $badgeAlias): void
    {
        $badge = Badge::where('alias', $badgeAlias)->first();
        if ($badge) {
            $seller->addBadge($badge);
        }
    }

    /**
     * Soft delete a product
     *
     * @param int $id
     * @return JsonResponse
     */
    public function softDelete(int $id): JsonResponse
    {
        $product = Product::where('id', $id)->firstOrFail();

        $product->softDelete(null, authAdmin()->id);
        return $this->successJson('Product deleted successfully');
    }

    /**
     * Display trashed (soft deleted) products with DataTables support.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function trash(Request $request): View|JsonResponse
    {
        $query = Product::onlyTrashed()->with(['seller', 'category', 'subcategory']);

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $products = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $products->map(fn($product) => $this->formatTrashedProductRow($product));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getTrashedDataTableColumns();
        $filters = $this->getDataTableFilters();
        $trashCount = Product::onlyTrashed()->count();

        return view('admin.products.trash', compact('columns', 'filters', 'trashCount'));
    }

    /**
     * Bulk restore soft deleted products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $products = Product::onlyTrashed()->whereIn('id', $ids)->get();
                $count = 0;

                foreach ($products as $product) {
                    $product->restoreDeleted();
                    $count++;
                }

                return "{$count} products has been restored successfully";
            }
        );
    }

    /**
     * Bulk permanently delete soft deleted products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkPermanentlyDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $products = Product::onlyTrashed()->whereIn('id', $ids)->get();
                $count = 0;

                foreach ($products as $product) {
                    $product->hardDelete();
                    $count++;
                }

                return "{$count} products has been permanently deleted successfully";
            }
        );
    }

    /**
     * Restore a soft deleted product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restoreDeleted();

        return $this->successJson('Product has been restored successfully');
    }

    /**
     * Permanently delete a product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->hardDelete();

        return $this->successJson('Product has been permanently deleted successfully');
    }

    /**
     * Bulk approve pending products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $products = Product::whereIn('id', $ids)
                    ->whereIn('status', [
                        ProductStatus::PENDING->value,
                        ProductStatus::RESUBMITTED->value,
                    ])
                    ->get();

                $count = 0;

                foreach ($products as $product) {
                    $title = $product->isPending()
                        ? ProductHistoryTitle::SUBMISSION_APPROVED
                        : ProductHistoryTitle::RESUBMISSION_APPROVED;

                    // Create product history
                    ProductHistory::create([
                        'seller_id' => $product->seller_id,
                        'admin_id' => authAdmin()->id,
                        'product_id' => $product->id,
                        'title' => $title,
                    ]);

                    $product->status = ProductStatus::APPROVED;
                    $product->save();

                    // Send notification
                    Notification::sendProductSubmissionStatusNotification($product, 'approved');

                    $count++;
                }

                return $count;
            },
            Product::class,
            ':count product(s) approved successfully'
        );
    }

    /**
     * Bulk delete products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    $product->softDelete(null, authAdmin()->id);
                }
                return $products->count();
            },
            Product::class,
            ':count product(s) deleted successfully'
        );
    }

    /**
     * Redirect to show page (all tabs are loaded there).
     *
     * @param int $id
     * @param string $tab
     * @return RedirectResponse
     */
    private function redirectToShow(int $id, string $tab = 'details'): RedirectResponse
    {
        return redirect()->route('admin.products.show', ['id' => $id, 'tab' => $tab]);
    }

    /**
     * Apply search and filter conditions to the DataTable query.
     */
    private function applyDataTableFilters($query): void
    {
        if ($search = request()->input('search.value')) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('slug', 'like', $searchTerm)
                    ->orWhere('id', 'like', $searchTerm);
            });
        }

        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '2': // Category
                        $query->where('category_id', $value);
                        break;
                    case '5': // Status
                        $query->where('status', $value);
                        break;
                    case '2': // Seller
                        // Filter removed
                        break;
                    case '6': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from']) && strtotime($value['from'])) {
                                $query->whereDate('created_at', '>=', $value['from']);
                            }
                            if (!empty($value['to']) && strtotime($value['to'])) {
                                $query->whereDate('created_at', '<=', $value['to']);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to the query for DataTables.
     */
    private function applyDataTableSorting($query): void
    {
        $order = request()->input('order.0');
        $sortColumns = [
            1 => 'name',
            2 => 'category_id',
            3 => 'regular_price',
            4 => 'total_sales',
            5 => 'status',
            6 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 6;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Format a single product row for the DataTables AJAX response.
     */
    private function formatProductRow(Product $product): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $product->id . '">',
            'details' => view('admin.products.draw.details', compact('product'))->render(),
            'sales' => view('admin.products.draw.sales', compact('product'))->render(),
            'category' => view('admin.products.draw.category', compact('product'))->render(),
            'price' => view('admin.products.draw.price', compact('product'))->render(),
            'status' => view('admin.products.draw.status', compact('product'))->render(),
            'created_at' => '<div class="text-center text-muted">' . dateFormat($product->created_at) . '</div>',
            'actions' => view('admin.products.draw.actions', compact('product'))->render(),
        ];
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        return [

            ['data' => 'bulk', 'name' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'class' => 'no-sort no-export'],
            ['data' => 'details', 'name' => 'name', 'title' => translate('Product Details'), 'orderable' => true, 'searchable' => true],
            ['data' => 'category', 'name' => 'category_id', 'title' => translate('Category'), 'orderable' => false, 'searchable' => false],
            ['data' => 'price', 'name' => 'regular_price', 'title' => translate('Price'), 'orderable' => false, 'searchable' => false],
            ['data' => 'sales', 'name' => 'total_sales', 'title' => translate('Total Sales'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'no-sort no-export text-end'],
        ];
    }

    /**
     * Get filters configuration for the Datatable header.
     */
    private function getDataTableFilters(): array
    {
        $categories = ProductCategory::has('products')->get()->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray();
        $statuses = collect(ProductStatus::options())->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray();

        return [
            [
                'type' => 'select',
                'column' => '2',
                'label' => translate('Category'),
                'options' => $categories
            ],
            [
                'type' => 'select',
                'column' => '5',
                'label' => translate('Status'),
                'options' => $statuses
            ],
            [
                'type' => 'daterange',
                'column' => '6',
                'label' => translate('Date Range')
            ]
        ];
    }

    /**
     * Get product statistics counters with percentage changes (rolling 7-day window).
     */
    private function getProductCounters(): array
    {
        // Current metrics
        $counters = [
            'approved' => Product::approved()->count(),
            'pending' => Product::whereIn('status', [ProductStatus::PENDING->value, ProductStatus::RESUBMITTED->value])->count(),
            'needs_revision' => Product::needsRevision()->count(),
            'rejected' => Product::rejected()->count(),
        ];

        // Metrics from exactly 7 days ago for comparison
        $sevenDaysAgo = now()->subDays(7);
        $previous = [
            'approved' => Product::approved()->where('created_at', '<', $sevenDaysAgo)->count(),
            'pending' => Product::whereIn('status', [ProductStatus::PENDING->value, ProductStatus::RESUBMITTED->value])->where('created_at', '<', $sevenDaysAgo)->count(),
            'needs_revision' => Product::needsRevision()->where('created_at', '<', $sevenDaysAgo)->count(),
            'rejected' => Product::rejected()->where('created_at', '<', $sevenDaysAgo)->count(),
        ];

        // Calculate growth percentages
        foreach (
            [
                'approved_percent' => [$counters['approved'], $previous['approved']],
                'pending_percent' => [$counters['pending'], $previous['pending']],
                'needs_revision_percent' => [$counters['needs_revision'], $previous['needs_revision']],
                'rejected_percent' => [$counters['rejected'], $previous['rejected']],
            ] as $key => [$current, $prev]
        ) {
            if ($prev > 0) {
                $counters[$key] = (int) round((($current - $prev) / $prev) * 100);
            } else {
                $counters[$key] = $current > 0 ? 100 : 0;
            }
        }

        return $counters;
    }

    /**
     * Prepare data for the show view
     *
     * This method gathers product details, history, reviews, comments and all
     * statistics/charts related data used in the product show page.
     *
     * @param int $id
     * @return array
     */
    private function getProductData(Product $product, int $id): array
    {
        // Statistics period and date range
        $period = request()->input('period', 'lifetime');
        [$startDate, $endDate] = $this->getStatisticsPeriods($period, $product);

        // Prepare the StatisticsService objects once and share them with helpers
        $salesStats = app(StatisticsService::class)
            ->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->dateRange($startDate, $endDate);

        $viewsStats = app(StatisticsService::class)
            ->forModel(ProductView::class)
            ->where('product_id', $product->id)
            ->dateRange($startDate, $endDate);

        $others = $this->getShowData($product, $id);
        $counters = $this->getShowCounters($salesStats, $viewsStats);
        $statistics = $this->getShowStatistics($salesStats, $viewsStats);

        // Overwrite cached product values with live counters to ensure UI consistency
        // (Header counters use $product attributes which can drift)
        $product->total_sales = $counters['counters']['total_sales'];
        $product->total_sales_amount = $counters['counters']['total_sales_amount'];
        $product->total_earnings = $counters['counters']['total_earnings'];

        $statistics['currentPeriod'] = $period;
        $statistics['period'] = $this->getPeriodLabel($period);

        return array_merge($others, $counters, $statistics);
    }

    /**
     * Prepare non-statistics resources for the show view: histories, reviews and comments
     *
     * @param Product $product
     * @param int $id
     * @return array
     */
    private function getShowData(Product $product, int $id): array
    {
        $productHistories = ProductHistory::where('product_id', $product->id)
            ->orderByDesc('id')->paginate(10);

        $reviews = ProductReview::where('product_id', $id)
            ->with('user')->orderByDesc('id')->paginate(10);

        $comments = ProductComment::where('product_id', $id)
            ->with('user')->orderByDesc('id')->paginate(10);

        return [
            'productHistories' => $productHistories,
            'reviews' => $reviews,
            'comments' => $comments,
        ];
    }

    /**
     * Prepare counters values for the show view using StatisticsService instances
     *
     * @param
     * @param mixed $salesStats
     * @param mixed $viewsStats
     * @return array
     */
    private function getShowCounters($salesStats, $viewsStats): array
    {
        $taxExpression = 'seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)';

        $counters = $salesStats->counters([
            'total_sales' => ['count', '*'],
            'total_sales_amount' => ['sum', 'price'],
            'total_earnings' => ['sum', DB::raw($taxExpression)],
        ]);

        $counters['total_views'] = $viewsStats->counters(['views' => ['count', '*']])['views'] ?? 0;

        // Default null sums to 0
        $counters['total_sales_amount'] = (float) ($counters['total_sales_amount'] ?? 0);
        $counters['total_earnings'] = (float) ($counters['total_earnings'] ?? 0);

        return ['counters' => $counters];
    }

    /**
     * Prepare charts, geo data and referrals for the show view
     *
     * @param mixed $salesStats
     * @param mixed $viewsStats
     * @return array
     */
    private function getShowStatistics($salesStats, $viewsStats): array
    {
        $charts['sales'] = $salesStats->chart('timeSeries', [
            'title' => 'Sales',
            'dateField' => 'created_at',
            'aggregation' => 'count',
        ]);

        $charts['views'] = $viewsStats->chart('timeSeries', [
            'title' => 'Views',
            'dateField' => 'created_at',
            'aggregation' => 'count',
        ]);

        $topPurchasingCountries = $salesStats->geoData('byCountry', [
            'aggregation' => 'sum',
            'field' => 'price',
            'limit' => 6,
            'orderBy' => 'total_price',
        ]);

        $geoCountries = $salesStats->geoData('byCountry', [
            'aggregation' => 'count',
            'field' => '*',
        ]);

        $referrals = $viewsStats->referrals([
            'field' => 'referrer',
            'limit' => 15,
        ]);

        // License Distribution
        $licenseDistribution = $salesStats->chart('pie', [
            'groupBy' => 'license_type',
            'title' => translate('License Distribution')
        ]);

        if (!empty($licenseDistribution['labels'])) {
            $licenseDistribution['labels'] = array_map(function ($type) {
                try {
                    return \App\Enums\LicenseType::from((int) $type)->label();
                } catch (\Exception $e) {
                    return $type;
                }
            }, $licenseDistribution['labels']);
        }

        return [
            'charts' => $charts,
            'topPurchasingCountries' => $topPurchasingCountries,
            'geoCountries' => $geoCountries,
            'referrals' => $referrals,
            'licenseDistribution' => $licenseDistribution,
            'topPurchasingCountriesFormatted' => $topPurchasingCountries->take(10),
        ];
    }

    /**
     * Get start and end dates for statistics based on period key
     *
     * @param string $period
     * @return array
     */
    private function getStatisticsPeriods(string $period, $entity = null): array
    {
        $now = Date::now();

        return match ($period) {
            'lifetime' => [$entity?->created_at ?? $now->copy()->subYears(10), $now->copy()],
            'last_28_days' => [$now->copy()->subDays(28), $now->copy()],
            'last_90_days' => [$now->copy()->subDays(90), $now->copy()],
            'last_6_months' => [$now->copy()->subMonths(6)->startOfMonth(), $now->copy()],
            'last_1_year' => [$now->copy()->subYear()->startOfMonth(), $now->copy()],
            default => [
                Date::parse($period)->startOfMonth(),
                Date::parse($period)->endOfMonth()
            ],
        };
    }

    /**
     * Get formatted period label
     *
     * @param string $period
     * @return string
     */
    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'last_28_days' => translate('Last 28 Days'),
            'last_90_days' => translate('Last 90 Days'),
            'last_6_months' => translate('Last 6 Months'),
            'last_1_year' => translate('Last 1 Year'),
            'lifetime' => translate('Lifetime'),
            default => preg_match('/^\d{4}-\d{2}$/', $period) ? \Carbon\Carbon::parse($period)->format('F Y') : translate('Last 28 Days'),
        };
    }

    /**
     * Format a single trashed product row for DataTables
     *
     * @param Product $product
     * @return array
     */
    private function formatTrashedProductRow(Product $product): array
    {
        $deletedByHtml = '';
        if ($product->deleted_by === superAdmin()?->id) {
            $deletedByHtml = '<span class="status-badge bg-success-subtle text-success">' . translate('Admin') . '</span>';
        } elseif ($product->deleted_by === authAdmin()?->id) {
            $deletedByHtml = '<span class="status-badge bg-orange-subtle text-orange">' . authAdmin()->full_name . '</span>';
        } else {
            $deletedByHtml = '<span class="status-badge bg-danger-subtle text-danger">' . translate('Seller') . '</span>';
        }

        $reason = $product->deletion_reason ? truncateText($product->deletion_reason, 30) : translate('N/A');
        $reasonTitle = $product->deletion_reason ?: '';

        return [
            'id' => '#' . $product->id,
            'details' => view('components.product', [
                'product' => $product,
                'showSubCategory' => true,
                'imageSize' => 'md'
            ])->render(),
            'seller' => view('components.user', [
                'user' => $product->seller,
                'avatarSize' => 'sm'
            ])->render(),
            'deleted_by' => '<div class="text-center">' . $deletedByHtml . '</div>',
            'deletion_reason' => '<div class="text-center"><span class="text-muted small" title="' . $reasonTitle . '">' . $reason . '</span></div>',
            'deleted_at' => '<div class="text-center text-muted">' . dateFormat($product->deleted_at ?? null) . '</div>',
            'actions' => view('admin.products.partials.trash-actions', ['product' => $product])->render(),
            'checkbox' => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $product->id . '">'
        ];
    }

    /**
     * Get DataTables column definitions for the Trash view
     *
     * @return array
     */
    private function getTrashedDataTableColumns(): array
    {
        return [
            ['data' => 'checkbox', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'width' => '20px'],
            ['data' => 'id', 'title' => translate('ID'), 'width' => '60px'],
            ['data' => 'details', 'title' => translate('Product Details')],
            ['data' => 'seller', 'title' => translate('Seller')],
            ['data' => 'deleted_by', 'title' => '<div class="text-center">' . translate('Deleted By') . '</div>'],
            ['data' => 'deletion_reason', 'title' => '<div class="text-center">' . translate('Deletion Reason') . '</div>'],
            ['data' => 'deleted_at', 'title' => '<div class="text-center">' . translate('Deleted Date') . '</div>'],
            ['data' => 'actions', 'title' => '<div class="text-end">' . translate('Actions') . '</div>', 'orderable' => false, 'searchable' => false],
        ];
    }
}
