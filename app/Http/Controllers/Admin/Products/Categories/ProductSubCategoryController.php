<?php

namespace App\Http\Controllers\Admin\Products\Categories;

use App\Http\Controllers\Controller;
use App\Models\Product\{ProductCategory, ProductSubCategory};
use App\Traits\{HandlesValidation, HandlesSorting};
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductSubCategoryController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a listing of the sub-categories with DataTables support.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ProductSubCategory::query();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $query->with('category')
            ->withCount('products')
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
                $subCategories = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $subCategories->map(fn($item) => $this->formatSubCategoryRow($item));

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

        $columns = $this->getSubCategoryDataTableColumns();
        $categories = ProductCategory::all();
        $subCategoriesCount = ProductSubCategory::count();

        return view('admin.products.categories.sub-categories.index', compact('columns', 'categories', 'subCategoriesCount'));
    }

     /**
     * Show edit form via AJAX.
     *
     * @param ProductSubCategory $subCategory
     * @return string
     */
    public function createModal(): string
    {
        $categories = ProductCategory::all();

        return view('admin.products.categories.sub-categories.modals.modal_create', compact('categories'))->render();
    }

    /**
     * Store sub-category via AJAX.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            ProductSubCategory::create($this->prepareSubCategoryData($request));

            return $this->successJson('Sub category created successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while creating sub category', [], 500);
        }
    }

    /**
     * Show edit form via AJAX.
     *
     * @param ProductSubCategory $subCategory
     * @return string
     */
    public function editModal(ProductSubCategory $subCategory): string
    {
        $categories = ProductCategory::all();

        return view('admin.products.categories.sub-categories.modals.modal_edit', compact('subCategory', 'categories'))->render();
    }

    /**
     * Update sub-category via AJAX.
     *
     * @param Request $request
     * @param ProductSubCategory $subCategory
     * @return JsonResponse
     */
    public function update(Request $request, ProductSubCategory $subCategory): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules($subCategory));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $subCategory->update($this->prepareSubCategoryData($request, true));

            return $this->successJson('Sub category updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while updating sub category', [], 500);
        }
    }

    /**
     * Handle sorting of sub-categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, ProductSubCategory::class);
    }

    /**
     * Generate slug for sub-category.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function slug(Request $request): JsonResponse
    {
        $slug = null;
        if ($request->filled('content')) {
            $slug = SlugService::createSlug(ProductSubCategory::class, 'slug', $request->input('content'));
        }
        return response()->json(['slug' => $slug]);
    }

    /**
     * Delete a sub-category.
     *
     * @param ProductSubCategory $subCategory
     * @return JsonResponse
     */
    public function destroy(ProductSubCategory $subCategory): JsonResponse
    {
        if ($subCategory->products->count() > 0) {
            return $this->errorJson('The selected sub category has products, it cannot be deleted');
        }

        $subCategory->delete();

        return $this->successJson('Sub category deleted successfully');
    }

    /**
     * Bulk delete sub-categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $subCategories = ProductSubCategory::whereIn('id', $ids)->get();
                $deletedCount = 0;
                $errors = [];

                foreach ($subCategories as $subCategory) {
                    if ($subCategory->products->count() > 0) {
                        $errors[] = "Sub category '{$subCategory->name}' has products and cannot be deleted";
                        continue;
                    }

                    $subCategory->delete();
                    $deletedCount++;
                }

                if ($deletedCount === 0 && !empty($errors)) {
                    throw new \Exception(implode(', ', $errors));
                }

                return [
                    'count' => $deletedCount,
                    'message' => $deletedCount > 0
                        ? translate(':count sub categor(ies) deleted successfully', ['count' => $deletedCount])
                        : translate('No sub categories were deleted'),
                ];
            },
            ProductSubCategory::class,
            ':count sub categor(ies) deleted successfully',
            'Error deleting sub categories'
        );
    }

    /**
     * Get validation rules for sub-category.
     *
     * @param ProductSubCategory|null $subCategory
     * @return array
     */
    private function getValidationRules(?ProductSubCategory $subCategory = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('product_sub_categories')->ignore($subCategory?->id)],
            'title' => ['nullable', 'string', 'max:70'],
            'description' => ['nullable', 'string', 'max:150'],
        ];

        // Only require category on create, not on update
        if (is_null($subCategory)) {
            $rules['category'] = ['required', 'integer', 'exists:product_categories,id'];
        }

        return $rules;
    }

    /**
     * Prepare sub-category data for storage.
     *
     * @param Request $request
     * @param bool $isUpdate
     * @return array
     */
    private function prepareSubCategoryData(Request $request, bool $isUpdate = false): array
    {
        $data = [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ];

        // Only set category_id on create, not on update
        if (!$isUpdate) {
            $data['category_id'] = $request->input('category');
        }

        return $data;
    }

    /**
     * Format a single sub-category row for DataTables
     *
     * @param ProductSubCategory $subCategory
     * @return array
     */
    private function formatSubCategoryRow(ProductSubCategory $subCategory): array
    {
        return [
            'checkbox' => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $subCategory->id . '">',
            'sort_handle' => '<span class="sortable-table-handle me-2 text-muted text-center"><i class="bi bi-grip-vertical"></i></span>',
            'name' => view('admin.products.categories.sub-categories.partials.details', ['subCategory' => $subCategory])->render(),
            'category' => '<a href="' . route('admin.products.categories.edit', $subCategory->category->id) . '" class="text-gray-800 hover-primary"><i class="bi bi-folder me-2"></i>' . e($subCategory->category->name) . '</a>',
            'products_count' => '<div class="text-center">' . numberFormat($subCategory->products_count ?? 0) . '</div>',
            'total_sales' => '<div class="text-center">' . getAmount($subCategory->products_sum_total_sales_amount ?? 0) . '</div>',
            'total_views' => '<div class="text-center">' . numberFormat($subCategory->total_views) . '</div>',
            'created_at' => '<div class="text-center text-muted">' . dateFormat($subCategory->created_at) . '</div>',
            'actions' => '<div class="text-end">' . view('admin.products.categories.sub-categories.partials.actions', ['subCategory' => $subCategory])->render() . '</div>',
            'DT_RowAttr' => [
                'data-id' => $subCategory->id
            ]
        ];
    }

    /**
     * Get column definitions for sub-category DataTables
     *
     * @return array
     */
    private function getSubCategoryDataTableColumns(): array
    {
        return [
            ['data' => 'checkbox', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'width' => '20px', 'exportable' => false],
            ['data' => 'sort_handle', 'title' => '<i class="bi bi-arrows-move fs-6"></i>', 'orderable' => false, 'searchable' => false, 'class' => 'no-sort-icon text-center', 'width' => '40px', 'exportable' => false],
            ['data' => 'name', 'title' => translate('Sub Category Name')],
            ['data' => 'category', 'title' => translate('Main Category')],
            ['data' => 'products_count', 'title' => translate('Total Products'), 'centered' => true],
            ['data' => 'total_sales', 'title' => translate('Total Sales'), 'centered' => true],
            ['data' => 'total_views', 'title' => translate('Total Views'), 'centered' => true],
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

        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '3': // Main Category
                        $query->where('category_id', $value);
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
            1 => 'sort_id',
            2 => 'name',
            3 => 'category_id',
            4 => 'products_count',
            5 => 'products_sum_total_sales_amount',
            7 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 1;
        $sortColumn = $sortColumns[$columnIndex] ?? 'sort_id';
        $sortDir = $order['dir'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDir);
    }
}
