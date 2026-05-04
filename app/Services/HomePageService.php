<?php

namespace App\Services;

use App\Cache\CacheManager;
use App\Classes\BuilderBlocks;
use App\Models\User;
use App\Models\Blog\{BlogArticle};
use App\Models\Product\{Product, ProductCategory};

/**
 * HomePageService
 *
 * Handles all data fetching and caching logic for the homepage sections.
 */
class HomePageService
{
    /**
     * Cache instance
     *
     * @var CacheManager
     */
    protected CacheManager $cache;

    /**
     * Create a new HomePageService instance
     */
    public function __construct()
    {
        $this->cache = CacheManager::scope('home_', 60);
    }

    /**
     * Get products by type with caching and pagination
     *
     * @param string $productType  (latest, trending, best_selling, sale, free, premium, featured)
     * @param array $options       Block options (pagination_style, products_number, etc.)
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getProductsByType(string $productType = 'latest', array $options = []): mixed
    {
        $paginationStyle = $options['pagination_style'] ?? 'none';
        $limit = max(1, (int)($options['products_number'] ?? 8));
        $pageName = $productType . '_page';
        $page = request()->get($pageName, 1);

        $cacheKey = "products_{$productType}_l{$limit}_s{$paginationStyle}_p{$page}";

        return $this->cache->remember($cacheKey, function () use ($productType, $limit, $paginationStyle, $pageName) {
            $query = Product::approved();

            $query = match ($productType) {
                'trending'      => $query->trending(),
                'best_selling'  => $query->bestSelling()->orderByDesc('total_sales'),
                'sale'          => $query->onDiscount(),
                'free'          => $query->free(),
                'premium'       => $query->premium(),
                'featured'      => $query->featured(),
                default         => $query->latest('id'),
            };

            if ($productType !== 'latest') {
                $query->inRandomOrder();
            }

            if (in_array($paginationStyle, ['numeric', 'load_more'])) {
                return $query->paginate($limit, ['*'], $pageName);
            }
            return $query->limit($limit)->get();
        }, $options['cache_expiry_time'] ?? 60);
    }

    /**
     * Get all categories with their products, respecting limit, pagination and product type
     *
     * @param array $options  Block options (pagination_style, products_number, product_type, etc.)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesWithProducts(array $options = []): mixed
    {
        $paginationStyle = $options['pagination_style'] ?? 'none';
        $limit = max(1, (int)($options['products_number'] ?? 8));
        $productType = $options['product_type'] ?? 'latest';

        $categories = ProductCategory::all();

        foreach ($categories as $category) {
            $query = $category->products()->approved();

            // Apply same product type scope as the main product list
            $query = match ($productType) {
                'trending'      => $query->trending(),
                'best_selling'  => $query->bestSelling()->orderByDesc('total_sales'),
                'sale'          => $query->onDiscount(),
                'free'          => $query->free(),
                'premium'       => $query->premium(),
                'featured'      => $query->featured(),
                default         => $query->latest('id'),
            };

            if ($productType !== 'latest') {
                $query->inRandomOrder();
            } else {
                $query->orderByDesc('id');
            }

            if (in_array($paginationStyle, ['numeric', 'load_more'])) {
                $pageName = "cat_{$category->slug}_page";
                $category->products = $query->paginate($limit, ['*'], $pageName);
            } else {
                $category->products = $query->limit($limit)->get();
            }
        }

        return $categories;
    }

    /**
     * Get products for each active tab (new, trending, featured, best_selling, free, premium, sale)
     *
     * @param array $activeTabs  ['new' => 'Label', 'trending' => 'Label', ...]
     * @param array $options     Block options (pagination_style, products_number, etc.)
     * @return array<string, mixed>  Keyed by tab name, value is paginator or collection
     */
    public function getProductsByTabs(array $activeTabs, array $options = []): array
    {
        $paginationStyle = $options['pagination_style'] ?? 'none';
        $limit = max(1, (int)($options['products_number'] ?? 8));
        $cacheExpiry = $options['cache_expiry_time'] ?? 60;
        $tabProducts = [];

        foreach ($activeTabs as $key => $label) {
            $pageName = "tab_{$key}_page";
            $page = request()->get($pageName, 1);
            $cacheKey = "tab_{$key}_l{$limit}_s{$paginationStyle}_p{$page}";

            $tabProducts[$key] = $this->cache->remember($cacheKey, function () use ($key, $limit, $paginationStyle, $pageName) {
                $query = Product::approved();

                $query = match ($key) {
                    'trending'     => $query->trending(),
                    'best_selling' => $query->bestSelling()->orderByDesc('total_sales'),
                    'sale'         => $query->onDiscount(),
                    'free'         => $query->free(),
                    'premium'      => $query->premium(),
                    'featured'     => $query->featured(),
                    default        => $query->latest('id'),
                };

                if ($key !== 'latest') {
                    $query->inRandomOrder();
                }

                if (in_array($paginationStyle, ['numeric', 'load_more'])) {
                    return $query->paginate($limit, ['*'], $pageName);
                }
                return $query->limit($limit)->get();
            }, $cacheExpiry);
        }

        return $tabProducts;
    }

    /**
     * Get block definition merged with active layout JSON options
     *
     * @param string $alias
     * @return object|null
     */
    protected function getConfiguredBlock(string $alias): ?object
    {
        $expectedId = "home_" . $alias;
        $blockDef = BuilderBlocks::find($expectedId);

        $block = $blockDef
            ? (object) $blockDef
            : (object)['id' => $expectedId, 'alias' => $alias, 'options' => []];

        $homeLayout = settings('theme_home');
        if (is_string($homeLayout)) {
            $homeLayout = json_decode($homeLayout);
        }

        if (is_array($homeLayout)) {
            foreach ($homeLayout as $row) {
                if (isset($row->columns) && is_array($row->columns)) {
                    foreach ($row->columns as $col) {
                        if (isset($col->blocks) && is_array($col->blocks)) {
                            foreach ($col->blocks as $layoutBlock) {
                                if (($layoutBlock->id ?? '') === $expectedId) {
                                    $blockOptions = (array) ($block->options ?? []);
                                    $layoutOptions = (array) ($layoutBlock->options ?? []);
                                    $block->options = array_merge($blockOptions, $layoutOptions);

                                    return $block;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $block;
    }
    /**
     * Get all homepage block data
     *
     * @return array
     */
    public function getAllBlocks(): array
    {
        return [
            // Categories Block
            'categoriesBlock' => $this->getCategoriesBlock(),
            'homeCategories' => $this->getHomeCategories(),

            // Featured Seller
            'featuredSellerBlock' => $this->getFeaturedSellerBlock(),
            'featuredSeller' => $this->getFeaturedSeller(),

            // Other Blocks
            'faqsBlock' => $this->getFaqsBlock(),
            'faqs' => $this->getFaqs(),

            'testimonialsBlock' => $this->getTestimonialsBlock(),
            'testimonials' => $this->getTestimonials(),

            'blogArticlesBlock' => $this->getBlogArticlesBlock(),
            'blogArticles' => $this->getBlogArticles(),
        ];
    }

    /**
     * Get categories block configuration
     *
     * @return object|null
     */
    public function getCategoriesBlock(): ?object
    {
        return $this->getConfiguredBlock('categories');
    }

    /**
     * Get home categories with caching
     */
    public function getHomeCategories()
    {
        $categoriesBlock = $this->getCategoriesBlock();

        if (!$categoriesBlock) {
            return null;
        }

        return $this->cache->remember('categories_cache', function () use ($categoriesBlock) {
            $content = $categoriesBlock->options['content'] ?? [];
            return count($content) > 0 ? collect($content)->map(fn($item) => (object)$item) : collect([]);
        }, $categoriesBlock->options['cache_expiry_time'] ?? 60);
    }

    /**
     * Get featured seller block configuration
     *
     * @return object|null
     */
    public function getFeaturedSellerBlock(): ?object
    {
        return $this->getConfiguredBlock('featured_seller');
    }

    /**
     * Get featured seller with caching
     *
     * @return \App\Models\User|null
     */
    public function getFeaturedSeller(): ?User
    {
        $block = $this->getFeaturedSellerBlock();

        if (!$block) {
            return null;
        }

        return $this->cache->remember('featured_seller_cache', function () use ($block) {
            return User::seller()
                ->featuredSeller()
                ->active()
                ->with(['products' => function ($query) use ($block) {
                    $query->inRandomOrder()
                        ->limit($block->options['featured_products_number'] ?? 3);
                }])
                ->first();
        }, $block->options['cache_expiry_time'] ?? 60);
    }


    /**
     * Get FAQs block configuration
     *
     * @return object|null
     */
    public function getFaqsBlock(): ?object
    {
        return $this->getConfiguredBlock('faqs');
    }

    /**
     * Get FAQs with caching
     */
    public function getFaqs()
    {
        $block = $this->getFaqsBlock();

        if (!$block) {
            return null;
        }

        return $this->cache->remember('faqs_cache', function () use ($block) {
            $content = $block->options['content'] ?? [];
            return count($content) > 0 ? collect($content)->map(fn($item) => (object)$item) : collect([]);
        }, $block->options['cache_expiry_time'] ?? 60);
    }

    /**
     * Get testimonials block configuration
     *
     * @return object|null
     */
    public function getTestimonialsBlock(): ?object
    {
        return $this->getConfiguredBlock('testimonials');
    }

    /**
     * Get testimonials with caching
     */
    public function getTestimonials()
    {
        $block = $this->getTestimonialsBlock();

        if (!$block) {
            return null;
        }

        return $this->cache->remember('testimonials_cache', function () use ($block) {
            $content = $block->options['content'] ?? [];
            return count($content) > 0 ? collect($content)->map(fn($item) => (object)$item) : collect([]);
        }, $block->options['cache_expiry_time'] ?? 60);
    }

    /**
     * Get blog articles block configuration
     *
     * @return object|null
     */
    public function getBlogArticlesBlock(): ?object
    {
        if (!@settings('actions') || !@settings('actions')->blog) {
            return null;
        }

        return $this->getConfiguredBlock('blog_articles');
    }

    /**
     * Get blog articles with caching
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getBlogArticles()
    {
        $block = $this->getBlogArticlesBlock();

        if (!$block) {
            return null;
        }

        return $this->cache->remember('blog_articles_cache', function () use ($block) {
            $limit = max(1, (int)($block->options['blog_number'] ?? 4));
            return BlogArticle::limit($limit)
                ->orderbyDesc('id')
                ->get();
        }, $block->options['cache_expiry_time'] ?? 60);
    }
}
