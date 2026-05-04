<?php

namespace App\Http\Controllers\Admin\Products\Categories;

use App\Http\Controllers\Controller;
use App\Enums\Product\ProductCategoryPreviewType;
use App\Models\Product\ProductCategory;
use App\Traits\{HandlesValidation, HandlesSorting};
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a listing of the product categories with DataTables support.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ProductCategory::withCount(['products', 'subCategories'])
            ->withSum('products', 'total_sales_amount');

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
                $categories = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $categories->map(fn($category) => $this->formatCategoryRow($category));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (\Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getCategoryDataTableColumns();
        $categoriesCount = ProductCategory::count();

        return view('admin.products.categories.main-categories.index', compact('columns', 'categoriesCount'));
    }

    /**
     * Show the form for creating a new product category.
     */
    public function create(): View
    {
        return view('admin.products.categories.main-categories.create');
    }

    /**
     * Store a newly created product category.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Validate category options if provided
        if ($request->has('category_options') && is_array($request->category_options)) {
            $optionsValidator = $this->validateAllRequest($request, $this->getCategoryOptionsValidationRules());
            if ($optionsValidator->fails()) {
                throw new \Exception($optionsValidator->errors()->first());
            }
        }

        // Validate file sizes
        if ($error = $this->validateFileSizes($request)) {
            return $this->errorJson($error);
        }

        $data = $this->prepareCategoryData($request);

        // Add category options if provided
        if ($request->has('category_options') && is_array($request->category_options)) {
            $data['options'] = $this->formatCategoryOptions($request->category_options);
        }

        ProductCategory::create($data);

        return $this->successJson('Category created successfully', ['redirect' => route('admin.products.categories.index')]);
    }

    /**
     * Show the form for editing the specified product category.
     */
    public function edit(ProductCategory $category): View
    {
        return view('admin.products.categories.main-categories.edit', compact('category'));
    }

    /**
     * Update the specified product category.
     */
    public function update(Request $request, ProductCategory $category): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules($category));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Validate category options
        $categoryOptions = $request->input('category_options', []);
        if (is_array($categoryOptions) && count($categoryOptions) > 0) {
            $optionsValidator = $this->validateAllRequest($request, $this->getCategoryOptionsValidationRules());
            if ($optionsValidator->fails()) {
                throw new \Exception($optionsValidator->errors()->first());
            }
        }

        // Validate file sizes
        if ($error = $this->validateFileSizes($request)) {
            return $this->errorJson($error);
        }

        $data = $this->prepareCategoryData($request);
        $data['options'] = $this->formatCategoryOptions(is_array($categoryOptions) ? $categoryOptions : []);

        $category->update($data);

        return $this->successJson('Category updated successfully', ['redirect' => route('admin.products.categories.index')]);
    }

    /**
     * Remove the specified product category.
     */
    public function destroy(ProductCategory $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return $this->errorJson('The selected category has products, it cannot be deleted');
        }

        if ($category->subCategories()->exists()) {
            return $this->errorJson('The selected category has subCategories, it cannot be deleted');
        }

        $category->delete();

        return $this->successJson('Category deleted successfully', ['redirect' => route('admin.products.categories.index')]);
    }

    /**
     * Bulk delete categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $categories = ProductCategory::whereIn('id', $ids)->get();
                $deletedCount = 0;
                $errors = [];

                foreach ($categories as $category) {
                    if ($category->products()->count() > 0) {
                        $errors[] = "Category '{$category->name}' has products and cannot be deleted";
                        continue;
                    }

                    if ($category->subCategories()->count() > 0) {
                        $errors[] = "Category '{$category->name}' has subcategories and cannot be deleted";
                        continue;
                    }

                    $category->delete();
                    $deletedCount++;
                }

                if ($deletedCount === 0 && !empty($errors)) {
                    throw new \Exception(implode(', ', $errors));
                }

                return [
                    'count' => $deletedCount,
                    'message' => $deletedCount > 0
                        ? translate(':count categories deleted successfully', ['count' => $deletedCount])
                        : translate('No categories were deleted'),
                ];
            },
            ProductCategory::class,
            ':count categories deleted successfully',
            'Error deleting categories'
        );
    }

    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, ProductCategory::class);
    }

    public function slug(Request $request): JsonResponse
    {
        $slug = null;
        if ($request->filled('content')) {
            $slug = SlugService::createSlug(ProductCategory::class, 'slug', $request->input('content'));
        }
        return response()->json(['slug' => $slug]);
    }

    /**
     * Get validation rules for product category.
     *
     * @param ProductCategory|null $category
     * @return array
     */
    private function getValidationRules(?ProductCategory $category = null): array
    {
        return [
            'name' => ['required', 'string', 'block_patterns', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('product_categories')->ignore($category?->id)],
            'title' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'description' => ['nullable', 'string', 'block_patterns', 'max:255'],
            'regular_buyer_fee' => ['nullable', 'numeric'],
            'extended_buyer_fee' => ['nullable', 'numeric'],
            'preview_type' => ['required', 'numeric', Rule::in(array_keys(ProductCategoryPreviewType::options()))],
            'preview_file_size' => ['nullable', 'numeric', 'min:1'],
            'gallery_images_count' => ['nullable', 'integer', 'min:0'],
            'main_file_types' => ['required', 'string', 'block_patterns', 'max:100'],
            'main_file_size' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    /**
     * Get validation rules for category options.
     *
     * @return array
     */
    private function getCategoryOptionsValidationRules(): array
    {
        return [
            'category_options' => ['required', 'array', 'min:1'],
            'category_options.*.type' => ['required', 'numeric', 'in:1,2'],
            'category_options.*.name' => ['required', 'string', 'block_patterns', 'max:255'],
            'category_options.*.options' => ['required', 'array', 'min:1'],
            'category_options.*.options.*' => ['required', 'string', 'block_patterns', 'max:255'],
        ];
    }

    /**
     * Validate file sizes against system max file size.
     *
     * @param Request $request
     * @return RedirectResponse|null
     */
    private function validateFileSizes(Request $request): ?RedirectResponse
    {
        $maxFileSize = @settings('product')->max_file_size;

        if ($request->filled('preview_file_size') && $request->preview_file_size > $maxFileSize) {
            return $this->errorBackWithInput('The preview file size cannot be greater than the max upload file size');
        }

        if ($request->filled('main_file_size') && $request->main_file_size > $maxFileSize) {
            return $this->errorBackWithInput('The main file size cannot be greater than the max upload file size');
        }

        return null;
    }

    /**
     * Prepare category data for storage.
     *
     * @param Request $request
     * @return array
     */
    private function prepareCategoryData(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'regular_buyer_fee' => $request->input('regular_buyer_fee'),
            'extended_buyer_fee' => $request->input('extended_buyer_fee'),
            'preview_type' => $request->input('preview_type'),
            'preview_file_size' => $request->filled('preview_file_size') ? ((float) $request->preview_file_size * 1048576) : (isset(settings('product')->max_file_size) ? settings('product')->max_file_size : 10485760),
            'gallery_images_count' => $request->input('gallery_images_count'),
            'main_file_types' => $request->input('main_file_types'),
            'main_file_size' => $request->filled('main_file_size') ? ((float) $request->main_file_size * 1048576) : (isset(settings('product')->max_file_size) ? settings('product')->max_file_size : 10485760),
        ];
    }

    /**
     * Format category options for storage in JSON.
     *
     * @param array $options
     * @return array
     */
    private function formatCategoryOptions(array $options): array
    {
        $formattedOptions = [];

        foreach ($options as $optionData) {
            if (!isset($optionData['name']) || !isset($optionData['type']) || !isset($optionData['options'])) {
                continue;
            }

            if (!is_array($optionData['options']) || count($optionData['options']) < 1) {
                continue;
            }

            $formattedOptions[] = [
                'id' => $optionData['id'] ?? (string) str()->uuid(),
                'type' => (int) $optionData['type'],
                'name' => $optionData['name'],
                'options' => array_values(array_filter($optionData['options'])),
                'is_required' => isset($optionData['required']) && $optionData['required'] == 1,
            ];
        }

        return $formattedOptions;
    }

    /**
     * Format a single category row for DataTables
     *
     * @param ProductCategory $category
     * @return array
     */
    private function formatCategoryRow(ProductCategory $category): array
    {
        return [
            'checkbox' => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $category->id . '">',
            'sort_handle' => '<span class="sortable-table-handle me-2 text-muted"><i class="bi bi-grip-vertical"></i></span>',
            'name' => '<a href="' . route('admin.products.categories.edit', $category->id) . '" class="text-dark fw-bold">' . e($category->name) . '</a>',
            'regular_fee' => '<div class="text-center">' . ($category->regular_buyer_fee > 0 ? getAmount($category->regular_buyer_fee) : 'N/A') . '</div>',
            'extended_fee' => '<div class="text-center">' . ($category->extended_buyer_fee > 0 ? getAmount($category->extended_buyer_fee) : 'N/A') . '</div>',
            'sub_categories_count' => '<div class="text-center">' . numberFormat($category->sub_categories_count ?? 0) . '</div>',
            'products_count' => '<div class="text-center">' . numberFormat($category->products_count ?? 0) . '</div>',
            'total_sales' => '<div class="text-center">' . getAmount($category->products_sum_total_sales_amount ?? 0) . '</div>',
            'created_at' => '<div class="text-center text-muted">' . dateFormat($category->created_at) . '</div>',
            'actions' => '<div class="text-end">' . view('admin.products.categories.main-categories.partials.actions', ['category' => $category])->render() . '</div>',
            'DT_RowAttr' => [
                'data-id' => $category->id
            ]
        ];
    }

    /**
     * Get column definitions for category DataTables
     *
     * @return array
     */
    private function getCategoryDataTableColumns(): array
    {
        return [
            ['data' => 'checkbox', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'width' => '20px', 'exportable' => false],
            ['data' => 'sort_handle', 'title' => '<i class="bi bi-arrows-move fs-6"></i>', 'orderable' => false, 'searchable' => false, 'class' => 'no-sort-icon', 'width' => '40px', 'centered' => true, 'exportable' => false],
            ['data' => 'name', 'title' => translate('Category Name')],
            ['data' => 'regular_fee', 'title' => translate('Regular Buyer Fee'), 'centered' => true],
            ['data' => 'extended_fee', 'title' => translate('Extended Buyer Fee'), 'centered' => true],
            ['data' => 'sub_categories_count', 'title' => translate('Total Subcategories'), 'centered' => true],
            ['data' => 'products_count', 'title' => translate('Total Products'), 'centered' => true],
            ['data' => 'total_sales', 'title' => translate('Total Sales'), 'centered' => true],
            ['data' => 'created_at', 'title' => translate('Created Date'), 'centered' => true],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'text-end'],
        ];
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
                    ->orWhere('id', 'like', $searchTerm);
            });
        }
    }

    /**
     * Apply sorting to the query for DataTables.
     */
    private function applyDataTableSorting($query): void
    {
        $order = request()->input('order.0');
        $sortColumns = [
            1 => 'sort_id',
            2 => 'name',
            5 => 'products_count',
            6 => 'products_sum_total_sales_amount',
            8 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 1;
        $sortColumn = $sortColumns[$columnIndex] ?? 'sort_id';
        $sortDir = $order['dir'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDir);
    }
}

