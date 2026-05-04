<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product\{Product, ProductCategory, ProductSubCategory};
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Service class for handling Category and Sub-category related product logic.
 */
readonly class CategoryService
{
    /**
     * Create a new CategoryService instance.
     */
    public function __construct(
        private ProductQueryService $productQueryService
    ) {}

    /**
     * Fetch filtered and paginated products for a category or sub-category.
     *
     * @param ProductCategory $category
     * @param ProductSubCategory|null $subCategory
     * @param Request $request
     * @return array{products: LengthAwarePaginator, totalProductsCount: int, hasFilters: bool}
     */
    public function fetchCategoryProducts(ProductCategory $category, ?ProductSubCategory $subCategory, Request $request): array
    {
        $productsQuery = Product::query()
            ->where('category_id', $category->id)
            ->approved();

        if ($subCategory) {
            $productsQuery->where('sub_category_id', $subCategory->id);
        }

        // Apply custom category options filters
        $productsQuery = $this->applyCategoryOptionsFilters($productsQuery, $category, $request);

        // Apply global product filters (price, sorting, etc.)
        $productsQuery = $this->productQueryService->getResultByParams(
            $productsQuery,
            $request,
            true,
            $request->input('query')
        );

        $totalProductsCount = $productsQuery->count();

        // Get pagination settings from theme
        $perPage = (int) (themePageSettings()->products_per_page ?? 16);
        $products = $productsQuery->paginate($perPage);

        // Append all relevant query parameters to pagination links
        $this->appendQueryParams($products, $category, $request);

        return [
            'products' => $products,
            'totalProductsCount' => $totalProductsCount,
            'hasFilters' => $this->detectFilters($category, $request),
        ];
    }

    /**
     * Apply dynamic category options filters to the product query.
     */
    private function applyCategoryOptionsFilters($query, ProductCategory $category, Request $request)
    {
        $categoryOptions = $category->options ?? [];
        foreach ($categoryOptions as $categoryOption) {
            $slug = Str::slug($categoryOption['name'], '_');

            if ($request->filled($slug)) {
                $selectedOptions = $request->input($slug);
                $isMultiple = $categoryOption['type'] == ProductCategory::MULTIPLE_SELECT;

                if ($isMultiple && is_array($selectedOptions)) {
                    $query->where(function ($q) use ($selectedOptions) {
                        foreach ($selectedOptions as $option) {
                            $sanitizedOption = str_replace('-', ' ', $option);
                            $q->orWhere('options', 'like', '%' . $sanitizedOption . '%');
                        }
                    });
                } else {
                    $sanitizedOption = str_replace('-', ' ', (string) $selectedOptions);
                    $query->where('options', 'like', '%' . $sanitizedOption . '%');
                }
            }
        }

        return $query;
    }

    /**
     * Detect if any filters are currently applied.
     */
    private function detectFilters(ProductCategory $category, Request $request): bool
    {
        if ($this->productQueryService->hasFilters($request)) {
            return true;
        }

        $categoryOptions = $category->options ?? [];
        foreach ($categoryOptions as $option) {
            if ($request->filled(Str::slug($option['name'], '_'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Append all relevant search and filter parameters to the paginator.
     */
    private function appendQueryParams(LengthAwarePaginator $products, ProductCategory $category, Request $request): void
    {
        $params = [
            'query', 'min_price', 'max_price',
            'free', 'premium', 'on_sale',
            'best_selling', 'trending', 'featured',
            'stars', 'date', 'sort_by'
        ];

        $categoryOptions = $category->options ?? [];
        foreach ($categoryOptions as $option) {
            $params[] = Str::slug($option['name'], '_');
        }

        $products->appends($request->only($params));
    }
}
