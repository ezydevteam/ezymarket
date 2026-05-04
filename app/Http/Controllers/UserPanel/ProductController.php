<?php

namespace App\Http\Controllers\UserPanel;

use App\Http\Controllers\Controller;
use App\Enums\Product\ProductStatus;
use App\Models\{Sale, UploadedFile};
use App\Models\Product\{Product, ProductCategory, ProductView, ProductChangeLog, ProductDiscount, ProductHistory};
use App\Services\ProductSubmissionService;
use App\Services\Statistics\StatisticsService;
use App\Facades\Notification;
use App\Traits\HandlesValidation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Cviebrock\EloquentSluggable\Services\SlugService;

class ProductController extends Controller
{
    use HandlesValidation;

    public function __construct(private ProductSubmissionService $submissionService)
    {
    }

    // =========================================================================
    // PRODUCT LISTING
    // =========================================================================

    public function index(Request $request): View|JsonResponse
    {
        $query = Product::where('seller_id', authUser()->id)
            ->whereNot('status', ProductStatus::DRAFT->value);

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
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
        $hasRecords = Product::where('seller_id', authUser()->id)->exists();

        return theme_view('userpanel.products.index', compact('columns', 'filters', 'hasRecords'));
    }

    // =========================================================================
    // CREATE & SUBMIT
    // =========================================================================

    public function create(Request $request): View
    {
        $categories = ProductCategory::with(['subCategories'])->get();

        // Resume existing draft if ID provided
        $draft = null;
        if ($request->has('draft')) {
            $draft = Product::where('id', $request->draft)
                ->where('seller_id', authUser()->id)
                ->draft()->first();
        }

        // Load category data if draft has one or if specified in query
        $category = null;
        $uploadedFiles = collect();
        if ($draft && $draft->category_id) {
            $category = $draft->category->load(['subCategories']);
            $uploadedFiles = UploadedFile::where('seller_id', authUser()->id)
                ->where('category_id', $category->id)->notExpired()->latest()->get();
        } elseif ($request->has('category')) {
            $category = ProductCategory::where('slug', $request->category)
                ->with(['subCategories'])
                ->first();
            if ($category) {
                $uploadedFiles = UploadedFile::where('seller_id', authUser()->id)
                    ->where('category_id', $category->id)->notExpired()->latest()->get();
            }
        }

        // Draft limit tracking
        $draftCount = Product::where('seller_id', authUser()->id)->draft()->count();

        return theme_view('userpanel.products.create', compact(
            'categories',
            'category',
            'draft',
            'uploadedFiles',
            'draftCount'
        ));
    }

    /**
     * Save product as draft.
     */
    public function saveDraft(Request $request): JsonResponse
    {
        try {
            $product = $this->submissionService->saveDraft($request, authUser());

            return $this->successJson('Draft saved successfully.', [
                'product_id' => $product->id,
            ]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Load uploaded files for a category (AJAX).
     */
    public function loadFiles($category_id): JsonResponse
    {
        $uploadedFiles = UploadedFile::where('seller_id', authUser()->id)
            ->where('category_id', hash_decode($category_id))
            ->notExpired()
            ->latest()
            ->get();

        $formattedFiles = [];
        foreach ($uploadedFiles as $file) {
            $formattedFiles[hash_encode($file->id)] = $file->toSelectOption();
        }

        return response()->json($formattedFiles);
    }

    /**
     * Delete an uploaded file.
     */
    public function deleteFile($category_id, $id): JsonResponse
    {
        $uploadedFile = UploadedFile::where('id', hash_decode($id))
            ->where('category_id', hash_decode($category_id))
            ->where('seller_id', authUser()->id)
            ->notExpired()
            ->first();

        if ($uploadedFile) {
            try {
                $uploadedFile->deleteFile();
                $uploadedFile->delete();
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage());
            }
        }

        return $this->successJson('File has been deleted successfully');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $product = $this->submissionService->storeProduct($request, authUser());

            $productSettings = settings('product');

            $message = @$productSettings->adding_require_review
                ? 'Your product has been submitted successfully, we will review it as soon as possible.'
                : 'Your product has been added successfully.';

            if (!@$productSettings->adding_require_review) {
                Notification::sendProductSubmittedApprovedNotification($product);
            }

            return $this->successJson($message, ['redirect' => route('user.product.index')]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // =========================================================================
    // EDIT & UPDATE
    // =========================================================================

    public function edit($id): View
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $category = $product->category;
        $categories = ProductCategory::all();

        $uploadedFiles = UploadedFile::where('seller_id', authUser()->id)
            ->where('category_id', $category->id)->notExpired()->latest()->get();

        return theme_view('userpanel.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'category' => $category,
            'uploadedFiles' => $uploadedFiles,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        try {
            $this->submissionService->updateProduct($request, $product, authUser());

            $productSettings = settings('product');

            if (@$productSettings->updating_require_review && $product->isApproved()) {
                $message = 'Your product update has been submitted for review.';
            } else {
                $message = 'Your product has been updated successfully.';
            }
            return $this->successJson($message, ['redirect' => route('user.product.edit', $product->id)]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // =========================================================================
    // DRAFTS MANAGEMENT
    // =========================================================================

    /**
     * List all drafts for the current seller.
     */

    public function drafts(): View
    {
        $drafts = Product::where('seller_id', authUser()->id)
            ->draft()
            ->with(['category'])
            ->get();

        return theme_view('userpanel.products.drafts', compact('drafts'));
    }

    /**
     * Publish a draft product.
     */
    public function publishDraft($id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)
                ->where('seller_id', authUser()->id)
                ->draft()
                ->firstOrFail();

            $this->submissionService->publishDraft($product);

            return $this->successJson('Product submitted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Delete a draft product.
     */
    public function deleteDraft($id): JsonResponse
    {
        try {
            $product = Product::where('id', $id)
                ->where('seller_id', authUser()->id)
                ->draft()
                ->firstOrFail();

            $product->forceDelete();

            return $this->successJson('Draft deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Get category data for dynamic category selection (AJAX).
     */
    public function getCategoryData($slug): JsonResponse
    {
        $category = ProductCategory::where('slug', $slug)
            ->with(['subCategories'])
            ->firstOrFail();

        $uploadedFiles = UploadedFile::where('seller_id', authUser()->id)
            ->where('category_id', $category->id)
            ->notExpired()
            ->latest()
            ->get();

        $formattedFiles = [];
        foreach ($uploadedFiles as $file) {
            $formattedFiles[hash_encode($file->id)] = $file->toSelectOption();
        }

        return response()->json([
            'category' => $category,
            'sub_categories' => $category->subCategories,
            'category_options' => $category->options ?? [],
            'uploaded_files' => $formattedFiles,
            'file_config' => [
                'upload' => [
                    'url' => route('user.product.upload', hash_encode($category->id)),
                    'max_files' => (int) @settings('product')->max_files - $uploadedFiles->count(),
                    'max_file_size' => (int) @settings('product')->max_file_size,
                    'allowed_types' => $category->getAllowedFileTypes(),
                ],
                'load_files_route' => route('user.product.files.load', hash_encode($category->id)),
                'buyer_fee' => [
                    'regular' => $category->regular_buyer_fee,
                    'extended' => $category->extended_buyer_fee,
                ],
            ],
        ]);
    }

    /**
     * Generate a unique slug for a product (AJAX).
     */
    public function slug(Request $request): JsonResponse
    {
        try {
            $slug = SlugService::createSlug(Product::class, 'slug', (string) $request->input('content'), ['unique' => false]);
            return response()->json(['slug' => $slug]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // =========================================================================
    // CHANGELOGS
    // =========================================================================

    public function changelogs($id): View
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $changelogs = ProductChangeLog::where('product_id', $product->id)
            ->latest()
            ->paginate(10);

        return theme_view('userpanel.products.changelogs', compact('product', 'changelogs'));
    }

    public function changelogsStore(Request $request, $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $validator = $this->validateRequestJson($request, [
            'version' => ['required', 'string', 'block_patterns', 'unique:product_change_logs,version,'.$product->id.',product_id', 'max:255'],
            'log' => ['required', 'string', 'block_patterns'],
        ], [
            'version.unique' => 'The version already exists.',
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $changelog = new ProductChangeLog();
        $changelog->product_id = $product->id;
        $changelog->version = $request->version;
        $changelog->log = $request->log;
        $changelog->save();

        return $this->successJson('Changelog created successfully');
    }

    public function changelogsDelete($id, $changelog_id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $changelog = ProductChangeLog::where('id', $changelog_id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $changelog->delete();

        return $this->successJson('Changelog deleted successfully');
    }

    // =========================================================================
    // HISTORY
    // =========================================================================

    public function history($id): View
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $productHistories = ProductHistory::where('product_id', $product->id)
            ->latest()
            ->paginate(10);

        return theme_view('userpanel.products.history', [
            'product' => $product,
            'productHistories' => $productHistories,
        ]);
    }

    // =========================================================================
    // DISCOUNT
    // =========================================================================

    public function discount($id): View
    {
        $product = Product::where('seller_id', authUser()->id)
            ->where('id', $id)
            ->active()
            ->firstOrFail();

        $discount = ProductDiscount::where('product_id', $product->id)
            ->latest()
            ->first();

        return theme_view('userpanel.products.discount', [
            'product' => $product,
            'discount' => $discount,
        ]);
    }

    public function discountCreate(Request $request, $id): JsonResponse
    {
        $product = Product::where('seller_id', authUser()->id)
            ->where('id', $id)
            ->active()
            ->firstOrFail();

        $productSettings = settings('product');

        // Check interval between discounts
        $latestDiscount = ProductDiscount::where('product_id', $product->id)
            ->latest()
            ->first();

        if ($latestDiscount) {
            $endDate = Carbon::parse($latestDiscount->ending_at);
            $waitDays = @$productSettings->discount_interval ?: 7;
            $waitingPeriodEnds = $endDate->addDays($waitDays);

            if (Carbon::now()->lt($waitingPeriodEnds)) {
                $remainingDays = Carbon::now()->diffInDays($waitingPeriodEnds, false);
                return $this->errorJson('You must wait :days day(s) before creating a new discount', parameters: ['days' => ceil($remainingDays)]);
            }
        }

        $maxPercentage = @$productSettings->discount_max_percentage ?: 90;

        $validator = $this->validateRequestJson($request, [
            'regular_percentage' => ['required', 'numeric', 'integer', 'min:1', 'max:' . $maxPercentage],
            'extended_percentage' => ['nullable', 'numeric', 'integer', 'min:1', 'max:' . $maxPercentage],
            'starting_at' => ['required', 'date', 'after_or_equal:today'],
            'ending_at' => ['required', 'date', 'after:starting_at'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Check max duration
        $startDate = Carbon::parse($request->starting_at);
        $endDate = Carbon::parse($request->ending_at);
        $duration = $startDate->diffInDays($endDate);
        $maxDays = @$productSettings->discount_max_days ?: 90;

        if ($duration > $maxDays) {
            return $this->errorJson('Discount duration cannot exceed :days day(s)', parameters: ['days' => $maxDays]);
        }

        // Calculate prices
        $regularPrice = (float) $product->regular_price;
        $regularDiscountedPrice = $regularPrice - ($regularPrice * ($request->regular_percentage / 100));

        $extendedDiscountedPrice = null;
        if ($product->extended_price && $request->extended_percentage) {
            $extendedPrice = (float) $product->extended_price;
            $extendedDiscountedPrice = $extendedPrice - ($extendedPrice * ($request->extended_percentage / 100));
        }

        try {
            $discount = new ProductDiscount();
            $discount->product_id = $product->id;
            $discount->regular_percentage = $request->regular_percentage;
            $discount->regular_price = $regularDiscountedPrice;
            $discount->extended_percentage = $request->extended_percentage ?? null;
            $discount->extended_price = $extendedDiscountedPrice;
            $discount->starting_at = $startDate;
            $discount->ending_at = $endDate;
            $discount->is_active = $startDate->isToday() || $startDate->isPast();
            $discount->save();

            // Set product status
            if ($discount->is_active) {
                $product->is_on_discount = true;
                $product->save();
            }

            return $this->successJson('Discount created successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function discountDelete(Request $request, $id): JsonResponse
    {
        $product = Product::where('seller_id', authUser()->id)
            ->where('id', $id)
            ->active()
            ->firstOrFail();

        $discount = ProductDiscount::where('product_id', $product->id)
            ->latest()
            ->firstOrFail();

        // Allow deletion if in grace period or not yet started
        $isActive = Carbon::now()->between($discount->starting_at, $discount->ending_at);
        $isInGracePeriod = $discount->created_at->diffInSeconds(now()) < 60;

        if ($isActive && !$isInGracePeriod) {
            return $this->errorJson('Cannot delete an active discount');
        }

        $discount->delete();
        return $this->successJson('Discount deleted successfully');
    }

    // =========================================================================
    // STATISTICS
    // =========================================================================

    public function statistics(Request $request, $id): View
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $currentPeriod = $request->get('period', 'last_28_days');
        $data = $this->getStatisticsData($product, $currentPeriod);

        return theme_view('userpanel.products.statistics', array_merge(['product' => $product], $data));
    }

    public function exportStatistics($id, $format = 'pdf')
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        try {
            $currentPeriod = request('period', 'last_28_days');
            $data = $this->getStatisticsData($product, $currentPeriod);

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('vendor.pdf.user.product-statistics-report', array_merge(['product' => $product], $data))
                ->setPaper('a4', 'portrait')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            return $pdf->download('product-' . $product->id . '-statistics-' . $currentPeriod . '.pdf');
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Recalculate product statistics from scratch based on active sales records.
     * This is used to fix drifted cached counters.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function recalculateStatistics(int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        try {
            $taxExpression = 'seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)';

            $stats = Sale::where('product_id', $product->id)
                ->active()
                ->selectRaw('COUNT(*) as total_sales')
                ->selectRaw('SUM(price) as total_sales_amount')
                ->selectRaw('SUM(' . $taxExpression . ') as total_earnings')
                ->first();

            $product->total_sales = $stats->total_sales ?? 0;
            $product->total_sales_amount = (float) ($stats->total_sales_amount ?? 0);
            $product->total_earnings = (float) ($stats->total_earnings ?? 0);
            $product->save();

            return $this->successJson('Statistics data updated successfully');
        } catch (Exception $e) {
            return $this->errorJson('Failed to synchronize statistics: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DOWNLOAD & DESTROY
    // =========================================================================

    public function download(Request $request, $id): RedirectResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        try {
            return $product->download();
        } catch (Exception $e) {
            return $this->errorBack();
        }
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', authUser()->id)
            ->active()
            ->firstOrFail();

        if ($product->total_sales > 0) {
            return $this->errorJson('Product with sales cannot be deleted.');
        }

        $product->delete();
        return $this->successJson('Product deleted successfully', [
            'redirect' => route('user.product.index')
        ]);
    }

    /**
     * Apply filter and search logic to the product query for DataTables.
     */
    private function applyDataTableFilters($query): void
    {
        if ($search = request()->input('search.value')) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('id', 'like', $searchTerm)
                    ->orWhere('status', 'like', $searchTerm);
            });
        }

        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                if ($column == '2') { // Status Column
                    $query->where('status', $value);
                } elseif ($column == '3') { // Date Range Column
                    if (is_array($value)) {
                        if (!empty($value['from']) && strtotime($value['from'])) {
                            $query->whereDate('created_at', '>=', $value['from']);
                        }
                        if (!empty($value['to']) && strtotime($value['to'])) {
                            $query->whereDate('created_at', '<=', $value['to']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Apply sorting to the product query for DataTables.
     */
    private function applyDataTableSorting($query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'name',
            1 => 'regular_price',
            2 => 'status',
            3 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 3;
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
            'details' => theme_view('userpanel.products.partials.row_details', compact('product'))->render(),
            'price' => theme_view('userpanel.products.partials.row_price', compact('product'))->render(),
            'status' => theme_view('userpanel.products.partials.row_status', compact('product'))->render(),
            'date' => dateFormat($product->created_at) . '<div class="text-muted small">' . timeAgo($product->created_at) . '</div>',
            'actions' => theme_view('userpanel.products.partials.row_actions', compact('product'))->render()
        ];
    }

    /**
     * Get columns for the DataTables
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'details', 'name' => 'name', 'title' => translate('Product Details'), 'orderable' => true, 'searchable' => true],
            ['data' => 'price', 'name' => 'regular_price', 'title' => translate('Price'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'date', 'name' => 'created_at', 'title' => translate('Published Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters for the DataTables
     */
    private function getDataTableFilters(): array
    {
        return [
            [
                'type' => 'select',
                'column' => 2,
                'label' => translate('Status'),
                'options' => array_map(fn($case) => ['value' => $case->value, 'label' => $case->label()], ProductStatus::cases())
            ],
            [
                'type' => 'daterange',
                'column' => 3,
                'label' => translate('Date Range')
            ]
        ];
    }

    /**
     * Consolidate statistics data gathering for a product and period.
     */
    private function getStatisticsData(Product $product, string $currentPeriod): array
    {
        $taxExpression = 'seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)';
        [$startDate, $endDate] = $this->getPeriodDates($currentPeriod);
        $stats = app(StatisticsService::class);

        // 1. Metric Counters
        $counters = $this->generateCounters($product, $startDate, $endDate);

        // Overwrite product attributes with live counters to ensure UI consistency
        // (Header counters use $product attributes which can drift)
        $product->total_sales = $counters['total_sales'] ?? 0;
        $product->total_sales_amount = $counters['total_sales_amount'] ?? 0;
        $product->total_earnings = $counters['total_earnings'] ?? 0;

        // 2. Charts (Sales and Views Trend)
        $salesChart = $stats->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->chart('timeSeries', [
                'dateField' => 'created_at',
                'aggregation' => 'sum',
                'aggregateField' => DB::raw($taxExpression),
                'title' => translate('Sales')
            ]);

        $viewsChart = $stats->forModel(ProductView::class)
            ->where('product_id', $product->id)
            ->dateRange($startDate, $endDate)
            ->chart('timeSeries', [
                'dateField' => 'created_at',
                'title' => translate('Views')
            ]);

        // 3. License Distribution (Pie Chart)
        $licenseDistribution = $stats->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->chart('pie', [
                'groupBy' => 'license_type',
                'title' => translate('License Distribution')
            ]);

        // Map raw license type values to labels
        if (!empty($licenseDistribution['labels'])) {
            $licenseDistribution['labels'] = array_map(function ($type) {
                try {
                    return \App\Enums\LicenseType::from((int) $type)->label();
                } catch (\Exception $e) {
                    return $type;
                }
            }, $licenseDistribution['labels']);
        }

        // 4. Geographical Data
        $salesByCountry = $stats->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->whereNotNull('country')
            ->dateRange($startDate, $endDate)
            ->topItems('country', [
                'aggregations' => [
                    'total_sales' => ['count', '*'],
                    'total_earnings' => ['sum', DB::raw($taxExpression)],
                ],
                'orderBy' => 'total_sales',
                'limit' => 50,
            ]);

        // 5. Referrals
        $referrals = $stats->forModel(ProductView::class)
            ->where('product_id', $product->id)
            ->whereNotNull('referrer')
            ->dateRange($startDate, $endDate)
            ->referrals(['limit' => 15]);

        // 6. Recent Sales (For PDF table)
        $sales = Sale::active()
            ->where('product_id', $product->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->with(['user:id,username'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Map and normalize fields for both Web and PDF views
        $salesByCountry = $salesByCountry->map(function ($item) {
            $item->total_count = $item->total_sales;
            $item->total_seller_earning = (float) ($item->total_earnings ?? 0);
            return $item;
        });

        return [
            'period' => $this->getPeriodLabel($currentPeriod),
            'currentPeriod' => $currentPeriod,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'counters' => $counters,
            'totalViews' => $counters['total_views'] ?? 0,
            'charts' => [
                'sales' => $salesChart,
                'views' => $viewsChart,
            ],
            'licenseDistribution' => $licenseDistribution,
            'geoCountries' => $salesByCountry,
            'salesByCountry' => $salesByCountry,
            'topPurchasingCountries' => $salesByCountry->take(10),
            'referrals' => $referrals,
            'sales' => $sales,
        ];
    }

    /**
     * Get start and end dates for a predefined or custom period.
     */
    private function getPeriodDates(string $period): array
    {
        switch ($period) {
            case 'last_28_days':
                $startDate = now()->subDays(28)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_90_days':
                $startDate = now()->subDays(90)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_6_months':
                $startDate = now()->subMonths(6)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_1_year':
                $startDate = now()->subYear()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'lifetime':
                $startDate = now()->subYears(10)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            default:
                if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                    $startDate = \Carbon\Carbon::parse($period)->startOfMonth();
                    $endDate = \Carbon\Carbon::parse($period)->endOfMonth();
                } else {
                    $startDate = now()->subDays(28)->startOfDay();
                    $endDate = now()->endOfDay();
                }
                break;
        }

        return [$startDate, $endDate];
    }

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

    private function generateCounters(Product $product, $startDate, $endDate): array
    {
        $stats = app(StatisticsService::class);
        $taxExpression = 'seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)';

        $salesCounters = $stats->forModel(Sale::class)
            ->where('product_id', $product->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'total_sales' => ['count', '*'],
                'total_sales_amount' => ['sum', 'price'],
                'total_earnings' => ['sum', DB::raw($taxExpression)],
            ]);

        // Default null sums to 0
        $salesCounters['total_sales_amount'] = (float) ($salesCounters['total_sales_amount'] ?? 0);
        $salesCounters['total_earnings'] = (float) ($salesCounters['total_earnings'] ?? 0);

        $viewsCounters = $stats->forModel(ProductView::class)
            ->where('product_id', $product->id)
            ->dateRange($startDate, $endDate)
            ->counters([
                'total_views' => ['count', '*'],
            ]);

        return array_merge($salesCounters, $viewsCounters);
    }
}
