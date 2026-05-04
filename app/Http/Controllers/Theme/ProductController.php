<?php

declare(strict_types=1);

namespace App\Http\Controllers\Theme;

use App\Enums\Product\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product\{Product, ProductComment, ProductReview};
use App\Services\{ProductLayoutService, ProductQueryService, ProductService};
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};

/**
 * Controller for handling product-related actions on the theme frontend.
 */
class ProductController extends Controller
{
    /**
     * Create a new ProductController instance.
     */
    public function __construct(
        protected readonly ProductQueryService $queryService,
        protected readonly ProductService $productService,
        protected readonly ProductLayoutService $layoutService
    ) {}

    /**
     * Display the product listing page.
     */
    public function index(Request $request): View
    {
        $productsQuery = Product::approved();
        $productsQuery = $this->queryService->getResultByParams($productsQuery, $request);

        $hasFilters = $this->queryService->hasFilters($request);
        $totalProductsCount = $productsQuery->count();
        $perPage = (int) (themePageSettings()->products_per_page ?? 16);

        $products = $productsQuery->paginate($perPage);
        $products->appends($request->except('query'));

        return theme_view('products.index', compact('products', 'totalProductsCount', 'hasFilters'));
    }

    /**
     * Display search results for products.
     */
    public function search(Request $request): View
    {
        $searchTerm = $request->input('query');
        $productsQuery = Product::approved();

        $productsQuery = $this->queryService->getResultByParams($productsQuery, $request, true, $searchTerm);

        $hasFilters = $this->queryService->hasFilters($request);
        $totalProductsCount = $productsQuery->count();
        $perPage = (int) (themePageSettings()->products_per_page ?? 16);

        $products = $productsQuery->paginate($perPage);
        $products->appends($request->all());

        $headerTitle = $searchTerm ?? translate('Search Results');
        $sectionTitle = translate('Search Results for:') . ' ' . ($searchTerm ?? '');

        return theme_view('search', compact(
            'products',
            'totalProductsCount',
            'searchTerm',
            'hasFilters',
            'headerTitle',
            'sectionTitle'
        ));
    }

    /**
     * Live search endpoint for real-time product suggestions.
     */
    public function liveSearch(Request $request): string
    {
        $query = Product::query()->approved();
        $searchTerm = $request->input('query');

        $query = $this->queryService->getResultByParams($query, $request, true, $searchTerm);

        $products = collect();
        if ($request->filled('query') && strlen((string) $request->input('query')) >= 2) {
            $products = $query->limit(10)->get();
        }

        return theme_view('partials.search.live-search', compact('products'))->render();
    }

    /**
     * Display a single product page.
     */
    public function show(string $slug, int $id, string $tab = 'details'): View|RedirectResponse
    {
        $product = Product::where('slug', $slug)
            ->where('id', $id)
            ->whereIn('status', [
                ProductStatus::APPROVED,
                ProductStatus::RESTRICTED,
            ])
            ->firstOrFail();

        if ($product->isRestricted()) {
            return view('vendor.errors.product-restricted', compact('product'));
        }

        $data = $this->layoutService->getProductPageData($product);
        $data['activeTab'] = $tab;

        // Populate additional tab data that normally comes from getProductPageData
        $data['changelogs'] = $product->changelogs()->latest()->paginate(10);
        $data['reviews'] = $product->reviews()->latest()->paginate(20);

        return theme_view('products.show', $data);
    }

    /**
     * Display a single product preview (live demo iframe).
     */
    public function preview(string $id): View
    {
        $product = Product::where('id', decrypt($id))
            ->whereNotNull('demo_link')
            ->approved()
            ->firstOrFail();

        return theme_view('products.preview', compact('product'));
    }

    /**
     * Serve AJAX-loaded tab content for the product page.
     */
    public function getAjaxTabContent(Request $request, string $slug, int $id, string $tab): View|RedirectResponse
    {
        $product = Product::where('id', $id)->approved()->firstOrFail();

        if (!$request->ajax()) {
            $cleanUrl = "/products/{$slug}/{$id}";
            if ($tab !== 'details') {
                $cleanUrl .= "/{$tab}";
            }
            return redirect($cleanUrl, 301);
        }

        return match ($tab) {
            'details'    => $this->renderDetailsTab($product),
            'changelogs' => $this->renderChangelogsTab($product),
            'reviews'    => $this->renderReviewsTab($request, $product),
            'comments'   => $this->renderCommentsTab($product),
            'support'    => $this->renderSupportTab($product),
            default      => abort(404),
        };
    }

    /**
     * Display a single product comment thread.
     */
    public function comment(string $slug, int $id, int $comment_id): View
    {
        $product = Product::where('slug', $slug)->where('id', $id)->approved()->firstOrFail();
        $data = $this->layoutService->getProductPageData($product);

        $comment = ProductComment::where('id', $comment_id)
            ->where('product_id', $id)
            ->with('user')
            ->firstOrFail();

        return theme_view('products.comment', ['comment' => $comment] + $data);
    }

    /**
     * Display a single product review.
     */
    public function review(string $slug, int $id, int $review_id): View
    {
        $product = Product::where('slug', $slug)->where('id', $id)->approved()->firstOrFail();
        $data = $this->layoutService->getProductPageData($product);

        $review = ProductReview::where('id', $review_id)
            ->where('product_id', $id)
            ->with('user')
            ->firstOrFail();

        return theme_view('products.review', ['review' => $review] + $data);
    }

    /**
     * Store a new product review.
     */
    public function reviewsStore(Request $request, string $slug, int $id): RedirectResponse
    {
        $product = Product::where('slug', $slug)->where('id', $id)->approved()->firstOrFail();
        $user = authUser();
        abort_unless($user, 403);

        if ($user->hasReviewedProduct($product->id)) {
            return $this->errorBack('You have already reviewed this product.');
        }

        if (!$user->hasPurchasedProduct($product->id)) {
            return back();
        }

        $request->validate([
            'review_stars' => ['required', 'integer', 'min:1', 'max:5'],
            'subject'      => ['required', 'string', 'block_patterns', 'max:100'],
            'review'       => ['nullable', 'string', 'block_patterns', 'max:1200'],
        ]);

        $this->productService->storeReview($product, $user, $request);

        return $this->successBack('Your review has been successfully published');
    }

    /**
     * Store a seller reply to a product review.
     */
    public function reviewsReply(Request $request, string $slug, int $id, int $review_id): RedirectResponse
    {
        $product = Product::where('slug', $slug)->where('id', $id)->approved()->firstOrFail();
        $review = ProductReview::where('id', $review_id)->where('product_id', $product->id)->firstOrFail();

        $user = authUser();
        abort_unless($user, 403);

        if (!$review->body || $user->id !== $product->seller_id) {
            return back();
        }

        $request->validate([
            'reply' => ['required', 'string', 'block_patterns', 'max:1200'],
        ]);

        $this->productService->storeReviewReply($product, $review, $user, $request);

        return $this->successBack('Your reply has been successfully published');
    }

    /**
     * Initiate the "Buy Now" checkout flow.
     */
    public function buyNow(Request $request, string $slug, int $id): RedirectResponse|JsonResponse
    {
        $product = Product::where('slug', $slug)->where('id', $id)->approved()->firstOrFail();
        $user = authUser();

        $rules = [
            'license_type' => ['required', 'integer', 'min:1', 'max:2'],
        ];

        if (@settings('product')?->support_status && $product->isSupported()) {
            $rules['support'] = ['required', 'integer', 'exists:support_packages,id'];
        }

        $request->validate($rules);

        // Custom validation for product-specific support
        if (isset($rules['support'])) {
            $freePackage = freeSupportPackage();
            $paidPackage = $product->supportPackage;
            $requestedId = (int) $request->support;

            $isValid = false;
            if ($freePackage && $requestedId === $freePackage->id) {
                $isValid = true;
            } elseif ($paidPackage && $requestedId === $paidPackage->id) {
                $isValid = true;
            }

            if (!$isValid) {
                return $this->errorBack('The selected support package is not available for this product.');
            }
        }

        $transaction = $this->productService->handleBuyNow($product, $user, $request);

        return redirect()->route('checkout.index', hash_encode($transaction->id));
    }

    /**
     * Download a free product's main file.
     */
    public function freeDownload(Request $request, string $id): mixed
    {
        $product = Product::where('id', hash_decode($id))->free()->approved()->firstOrFail();

        try {
            return $this->productService->processDownload($product);
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Redirect to a free product's external download link.
     */
    public function freeExternalDownload(string $id): RedirectResponse
    {
        $product = Product::where('id', hash_decode($id))->free()->approved()->firstOrFail();
        $product->increment('free_downloads');
        return redirect($product->main_file['path'] ?? '');
    }

    /**
     * Display the free license page.
     */
    public function freeLicense(string $id): View
    {
        $product = Product::where('id', decrypt($id))->free()->approved()->firstOrFail();
        return theme_view('products.license', [
            'product' => $product,
            'licenseType' => 'free'
        ]);
    }

    /**
     * Download a premium product's main file.
     */
    public function premiumDownload(Request $request, string $id): mixed
    {
        $product = Product::where('id', hash_decode($id))->premium()->approved()->firstOrFail();
        $user = authUser();
        abort_unless($user && $user->isPremiumMember() && $product->seller_id !== $user->id, 403);

        if ($user->premium->isExpired()) {
            return $this->errorRedirect('user.settings.premium', [], 'Your premium membership is expired');
        }

        if ($user->premium->isDailyLimitReached()) {
            return $this->errorRedirect('premium.index', [], 'You have exceeded your daily download limit.');
        }

        try {
            return $this->productService->processDownload($product, $user);
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Redirect to a premium product's external download link.
     */
    public function premiumExternalDownload(string $id): RedirectResponse
    {
        $product = Product::where('id', hash_decode($id))->premium()->approved()->firstOrFail();
        $user = authUser();
        abort_unless($user && $user->isPremiumMember() && $product->seller_id !== $user->id, 403);

        if ($user->premium->isExpired()) {
            return $this->errorRedirect('user.settings.premium', [], 'Your premium membership is expired');
        }

        if ($user->premium->isDailyLimitReached()) {
            return $this->errorRedirect('premium.index', [], 'You have exceeded your daily download limit.');
        }

        $user->premium->increment('total_downloads');
        return redirect($product->main_file['path'] ?? '');
    }

    /**
     * Display the premium license page.
     */
    public function premiumLicense(string $id): View
    {
        $product = Product::where('id', decrypt($id))->premium()->approved()->firstOrFail();
        $user = authUser();
        abort_unless($user && $user->isPremiumMember() && $product->seller_id !== $user->id, 403);

        return theme_view('products.license', [
            'product' => $product,
            'licenseType' => 'premium'
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS (VIEW RENDERING)
    // =========================================================================

    private function renderDetailsTab(Product $product): View
    {
        $layoutData = $this->layoutService->buildProductPageLayout();
        $data = (object) ($layoutData['options'] ?? []);
        return theme_view('products.ajax-tabs.details', compact('product', 'data'));
    }

    private function renderReviewsTab(Request $request, Product $product): View
    {
        abort_unless(@settings('product')?->reviews_status, 404);

        $reviewsQuery = $product->reviews();
        $reviewSortBy = $request->input('review_sort_by', 'newest');

        match ($reviewSortBy) {
            'highest_rating' => $reviewsQuery->orderByDesc('stars')->latest(),
            'lowest_rating'  => $reviewsQuery->orderBy('stars')->latest(),
            default          => $reviewsQuery->latest(),
        };

        $reviews = $reviewsQuery->paginate(20)->appends(['review_sort_by' => $reviewSortBy]);
        $starBreakdown = $this->layoutService->getProductPageData($product)['starBreakdown'];

        return theme_view('products.ajax-tabs.reviews', compact('product', 'reviews', 'starBreakdown'));
    }

    private function renderChangelogsTab(Product $product): View
    {
        abort_unless(@settings('product')?->changelogs_status && $product->hasChangelogs(), 404);
        $changelogs = $product->changelogs()->latest()->paginate(20);
        return theme_view('products.ajax-tabs.changelogs', compact('product', 'changelogs'));
    }

    private function renderCommentsTab(Product $product): View
    {
        abort_unless(@settings('product')?->comments_status, 404);
        return theme_view('products.ajax-tabs.comments', compact('product'));
    }

    private function renderSupportTab(Product $product): View
    {
        abort_unless(@settings('product')?->support_status && $product->isSupported(), 404);
        return theme_view('products.ajax-tabs.support', compact('product'));
    }
}
