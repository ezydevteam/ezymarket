<?php

declare(strict_types=1);

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use App\Models\Product\{ProductCategory, ProductSubCategory};
use App\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller for handling product categories and sub-categories on the theme frontend.
 */
class CategoryController extends Controller
{
    /**
     * Create a new CategoryController instance.
     *
     * @param CategoryService $categoryService Service to handle product logic
     */
    public function __construct(
        protected readonly CategoryService $categoryService
    ) {}

    /**
     * Display a listing of all product categories.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $categories = ProductCategory::query()
            ->with(['subCategories' => fn($query) =>
                $query->withCount(['products' => fn($q) => $q->approved()])
            ])
            ->withCount(['products' => fn($q) => $q->approved()])
            ->paginate(12);

        return theme_view('products.categories.index', compact('categories'));
    }

    /**
     * Display a specific category and its filtered products.
     *
     * @param Request $request
     * @param string $category_slug Slug of the category to display
     * @return View
     */
    public function category(Request $request, string $category_slug): View
    {
        // Use manual lookup based on slug to preserve existing logic if model binding is not yet configured in routes
        $category = ProductCategory::where('slug', $category_slug)
            ->with(['subCategories'])
            ->firstOrFail();

        $data = $this->categoryService->fetchCategoryProducts($category, null, $request);

        trackView($category, 'categories');

        return $this->renderView($category, null, $data);
    }

    /**
     * Display a specific sub-category and its filtered products.
     *
     * @param Request $request
     * @param string $category_slug Slug of the parent category
     * @param string $sub_category_slug Slug of the sub-category
     * @return View
     */
    public function subCategory(Request $request, string $category_slug, string $sub_category_slug): View
    {
        $category = ProductCategory::where('slug', $category_slug)->firstOrFail();

        $subCategory = ProductSubCategory::where('category_id', $category->id)
            ->where('slug', $sub_category_slug)
            ->with(['category'])
            ->firstOrFail();

        $data = $this->categoryService->fetchCategoryProducts($category, $subCategory, $request);

        trackView($subCategory, 'sub_categories');

        return $this->renderView($category, $subCategory, $data);
    }

    /**
     * Helper to render the unified category & sub-category view with smart data.
     *
     * @param ProductCategory $category Parent category
     * @param ProductSubCategory|null $subCategory Sub-category entity
     * @param array $data Service layer data (products, counts, filters)
     * @return View
     */
    protected function renderView(ProductCategory $category, ?ProductSubCategory $subCategory, array $data): View
    {
        $activeCategory = $subCategory ?? $category;

        return theme_view('products.categories.category', [
            'category'           => $category,
            'subCategory'        => $subCategory,
            'activeCategory'     => $activeCategory,
            'isSubCategory'      => $subCategory !== null,
            'products'           => $data['products'],
            'totalProductsCount' => $data['totalProductsCount'],
            'hasFilters'         => $data['hasFilters'],
            'sectionTitle'       => $activeCategory->section_title,
            'breadcrumbData'     => [
                'alias'  => $subCategory ? 'categories.sub-category' : 'categories.category',
                'params' => $subCategory ? [$category, $subCategory] : [$category]
            ]
        ]);
    }
}
