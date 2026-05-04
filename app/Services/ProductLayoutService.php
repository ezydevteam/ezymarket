<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product\Product;
use App\Models\UserBadge;
use Illuminate\Support\Collection;

/**
 * Service for handling product page layouts, theme settings, and dynamic CSS generation.
 */
readonly class ProductLayoutService
{
    /**
     * Get all data required for the product page view.
     */
    public function getProductPageData(Product $product): array
    {
        $userBadges = UserBadge::where('user_id', $product->seller_id)
            ->with('badge')->get();

        $sellerProducts = Product::where('seller_id', $product->seller_id)
            ->whereNot('id', $product->id)
            ->approved()
            ->inRandomOrder()
            ->limit(6)->get();

        $relatedProducts = Product::query();

        if ($product->sub_category_id) {
            $relatedProducts->where('sub_category_id', $product->sub_category_id);
        } else {
            $relatedProducts->where('category_id', $product->category_id);
        }

        $relatedProducts = $relatedProducts->whereNot('id', $product->id)
            ->whereNot('seller_id', $product->seller_id)
            ->approved()
            ->inRandomOrder()
            ->limit(6)->get();

        // Theme layout settings
        $layoutData = $this->buildProductPageLayout();

        return [
            'product'               => $product,
            'userBadges'            => $userBadges,
            'sellerProducts'        => $sellerProducts,
            'relatedProducts'       => $relatedProducts,
            'regularExtraFeatures'  => $product->getRegularExtraFeatures(),
            'extendedExtraFeatures' => $product->getExtendedExtraFeatures(),
            'starBreakdown'         => $this->getStarBreakdown($product),
            'productPageData'       => $layoutData,
            'productPageCss'        => $layoutData['css'] ?? '',
        ];
    }

    /**
     * Build the product page layout configuration from theme settings.
     */
    public function buildProductPageLayout(): array
    {
        $themeSettingsFile = theme_resource_path('settings.json');
        $options = [];

        if (file_exists($themeSettingsFile)) {
            $themeSettings = json_decode(file_get_contents($themeSettingsFile), true);
            foreach ($themeSettings['single_product_page'] ?? [] as $setting) {
                if (isset($setting['key'])) {
                    $options[$setting['key']] = $setting['value'];
                }
            }
        }

        $data = [];
        $data['options'] = $options;
        $data['display_layout'] = $options['display_layout'] ?? 'fullwidth_title';

        // Container Width
        $containerWidth = $options['container_width'] ?? 'default';
        $data['container_width'] = $containerWidth;
        $data['container_class'] = match ($containerWidth) {
            'fluid' => 'container-fluid px-4',
            'boxed' => 'container container-boxed',
            default => 'container container-default',
        };

        // Sidebar Positioning
        $sidebarPosition = $options['sidebar_position'] ?? 'right_sidebar';
        $data['sidebar_position'] = $sidebarPosition;

        if ($sidebarPosition === 'no_sidebar') {
            $data['main_col_class'] = 'col-12';
            $data['sidebar_col_class'] = 'd-none';
        } elseif ($sidebarPosition === 'left_sidebar') {
            $data['main_col_class'] = 'col-lg-8 order-1 order-lg-2';
            $data['sidebar_col_class'] = 'col-lg-4 order-2 order-lg-1';
        } else {
            $data['main_col_class'] = 'col-lg-8';
            $data['sidebar_col_class'] = 'col-lg-4';
        }

        // Title and Breadcrumbs
        $data['show_breadcrumbs'] = $options['show_breadcrumbs'] ?? 1;
        $data['breadcrumb_show_title'] = $options['breadcrumb_show_title'] ?? 1;
        $data['breadcrumb_color'] = $options['breadcrumb_color'] ?? '';
        $data['breadcrumb_style'] = $options['breadcrumb_style'] ?? '';

        $data['breadcrumb_style_class'] = match ($data['breadcrumb_style']) {
            'rounded_pill' => 'bg-light rounded-pill px-3 py-2 d-inline-block',
            'full_bg'      => 'bg-light rounded-3 px-3 py-2 d-block',
            default        => '',
        };

        $data['title_size'] = $options['title_size'] ?? 'fs-2';
        $data['title_weight'] = $options['title_weight'] ?? 'fw-bolder';
        $data['title_color'] = $options['title_color'] ?? '';
        $data['title_transform'] = $options['title_transform'] ?? '';

        $data['preview_gallery_display'] = $options['preview_gallery_display'] ?? 'default';

        // Meta settings
        $data['meta_seller_name'] = $options['meta_seller_name'] ?? 1;
        $data['meta_avg_reviews'] = $options['meta_avg_reviews'] ?? 1;
        $data['meta_total_sales'] = $options['meta_total_sales'] ?? 1;
        $data['meta_free_downloads'] = $options['meta_free_downloads'] ?? 1;
        $data['meta_product_badge'] = $options['meta_product_badge'] ?? 1;
        $data['meta_recent_update'] = $options['meta_recent_update'] ?? 1;
        $data['meta_favorite_btn'] = $options['meta_favorite_btn'] ?? 1;
        $data['meta_share_btn'] = $options['meta_share_btn'] ?? 1;
        $data['meta_report_btn'] = $options['meta_report_btn'] ?? 1;

        $data['seller_more_products'] = $options['seller_more_products'] ?? 1;
        $data['related_products'] = $options['related_products'] ?? 1;

        // Tabs
        $tabStyle = $options['tab_style'] ?? 'bordered';
        $tabAlignment = $options['tab_alignment'] ?? 'start';
        $data['tab_text_color'] = $options['tab_text_color'] ?? '';
        $data['tab_active_color'] = $options['tab_active_color'] ?? '';
        $data['tab_hide_icon'] = (bool)($options['tab_hide_icon'] ?? false);
        $data['tab_hide_counter'] = (bool)($options['tab_hide_counter'] ?? false);
        $data['tab_area_style'] = $options['tab_area_style'] ?? '';
        $data['tab_area_bg'] = $options['tab_area_bg'] ?? '';

        // Tab area wrapper class
        $tabAreaWrapClass = '';
        if ($data['tab_area_style'] === 'rounded_pill') {
            $pillJustify = match ($tabAlignment) {
                'center' => 'justify-content-center',
                'fill'   => 'justify-content-between',
                default  => 'justify-content-start',
            };
            $tabAreaWrapClass = 'd-flex align-items-center ' . $pillJustify;
            $tabAreaWrapClass .= ($options['display_layout'] === 'fullwidth_title') ? ' p-0' : ' p-3 pb-0';
        } elseif ($data['tab_area_style'] === 'full_bg') {
            $tabAreaWrapClass = 'rounded-2 overflow-hidden p-2 pb-0';
            $tabAreaWrapClass .= ($options['display_layout'] === 'fullwidth_title') ? ' mb-3' : ' mx-4 mt-3';
        } else {
            $tabAreaWrapClass = 'p-0';
        }

        $data['tab_area_wrap_class'] = $tabAreaWrapClass;

        $navClass = 'nav-tabs';
        if ($tabStyle === 'rounded') {
            $navClass = 'nav-tabs-scroll nav-pills nav-pills-custom';
        } elseif ($tabStyle === 'pill') {
            $navClass = 'nav-tabs-scroll nav-pills';
        }

        if (($options['display_layout'] !== 'fullwidth_title') && ($tabStyle !== 'bordered')) {
            $navClass .= ' border-bottom rounded-0 pb-2';
        }

        $alignClass = '';
        if ($data['tab_area_style'] !== 'rounded_pill') {
            if ($tabAlignment === 'center') {
                $alignClass = 'justify-content-center';
            } elseif ($tabAlignment === 'fill') {
                $alignClass = 'nav-fill w-100';
            }
        }

        $data['tab_nav_class'] = trim($navClass . ' ' . $alignClass);

        // Custom CSS
        $data['css'] = $this->buildProductPageStyle($options);

        return $data;
    }

    /**
     * Build product page custom styles.
     */
    public function buildProductPageStyle(array $options): string
    {
        $bcrumbSeparator = $options['breadcrumb_separator'] ?? 'chevron';

        $map = [
            'slash'   => ['content' => '"/"',    'icon' => false],
            'dot'     => ['content' => '"\2022"', 'icon' => false],
            'chevron' => ['content' => '"\F285"', 'icon' => true],
        ];

        $config = $map[$bcrumbSeparator] ?? $map['chevron'];
        $content = $config['content'];
        $isIcon = $config['icon'];

        $extra = $isIcon
            ? 'font-family: bootstrap-icons !important; font-size: 0.6rem; vertical-align: middle;'
            : '';

        $css = ".product-page-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
            content: {$content} !important;
            {$extra}
        }";

        if (!empty($options['breadcrumb_color'])) {
            $bcColor = $options['breadcrumb_color'];
            $css .= " .product-page-breadcrumb.{$bcColor} .breadcrumb-item, .product-page-breadcrumb.{$bcColor} .breadcrumb-item a { color: inherit !important; }";
            $css .= " .product-page-breadcrumb.{$bcColor} .breadcrumb-item.active { color: inherit !important; opacity: 0.85; }";
            $css .= " .product-page-breadcrumb.{$bcColor} .breadcrumb-item+.breadcrumb-item::before { color: inherit !important; opacity: 0.6; }";
        }

        // Bootstrap CSS variable resolvers
        $resolveTextVar = function (string $color): string {
            if (str_ends_with($color, '-text-subtle') || str_ends_with($color, '-bg-subtle')) {
                $base = preg_replace('/-(text|bg)-subtle$/', '', $color);
                return "var(--bs-{$base}-text-emphasis)";
            }
            return "var(--bs-{$color})";
        };

        $resolveBgVar = function (string $color): string {
            if (str_ends_with($color, '-bg-subtle')) {
                $base = str_replace('-bg-subtle', '', $color);
                return "var(--bs-{$base}-bg-subtle)";
            }
            if (str_ends_with($color, '-text-subtle')) {
                $base = str_replace('-text-subtle', '', $color);
                return "var(--bs-{$base}-bg-subtle)";
            }
            return "var(--bs-{$color})";
        };

        $isSubtle = fn(string $c) => str_ends_with($c, '-bg-subtle') || str_ends_with($c, '-text-subtle');

        // Tab text color
        if (!empty($options['tab_text_color'])) {
            $tc = $options['tab_text_color'];
            $tcv = $resolveTextVar($tc);
            $css .= " #product-tab-container-for-js .nav-link:not(.active) { color: {$tcv} !important; }";
        }

        // Active tab color
        if (!empty($options['tab_active_color'])) {
            $ac = $options['tab_active_color'];
            $acText = $resolveTextVar($ac);
            $acBg = $resolveBgVar($ac);
            $subtle = $isSubtle($ac);
            $borderBottomColor = ($options['display_layout'] ?? '') === 'fullwidth_title' ? 'transparent' : $acText;

            $css .= " #product-tab-container-for-js.nav-tabs .nav-link.active { color: {$acText} !important; border-bottom-color: {$borderBottomColor} !important; }";
            $pillText = $subtle ? $acText : '#fff';
            $css .= " #product-tab-container-for-js.nav-pills .nav-link.active { background-color: {$acBg} !important; color: {$pillText} !important; }";
        }

        // Tab area style + background
        $tabAreaStyle = $options['tab_area_style'] ?? '';
        $tabAreaBg = $options['tab_area_bg'] ?? '';

        if ($tabAreaStyle === 'rounded_pill') {
            $css .= " .product-tab-area-wrapper #product-tab-container-for-js { display: inline-flex !important; width: auto !important; border-radius: 50rem !important; overflow: hidden; flex-wrap: nowrap; }";
            if (!empty($tabAreaBg)) {
                $tabBgv = $resolveBgVar($tabAreaBg);
                $css .= " .product-tab-area-wrapper #product-tab-container-for-js { background-color: {$tabBgv} !important; }";
            }
        } elseif ($tabAreaStyle === 'full_bg' && !empty($tabAreaBg)) {
            $tabBgv = $resolveBgVar($tabAreaBg);
            $css .= " .product-tab-area-wrapper { background-color: {$tabBgv} !important; }";
        }

        // Layout helpers
        $css .= " .min-vh-25 { min-height: 25vh; }
        .max-w-3xl { max-width: 48rem; }
        .max-w-4xl { max-width: 56rem; }
        .max-w-5xl { max-width: 64rem; }";

        // Hero Header
        if (($options['display_layout'] ?? '') === 'hero_header') {
            $css .= " .product-hero-header { overflow: hidden; }
            .hero-bg-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; object-fit: cover; }
            .product-hero-header video.hero-bg-layer { z-index: 0; }
            .product-hero-header .z-index-1 { z-index: 2; }
            .hero-audio-wrapper { max-width: 500px; }
            .hero-audio-player { filter: invert(1) brightness(2); height: 36px; }
            .product-hero-header .product-page-meta .product-page-breadcrumb { display: flex; justify-content: center; }
            .product-hero-header .product-page-meta .d-flex { justify-content: center; }
            .product-hero-header .product-page-meta .rating-number { color: #fff !important; }
            .product-hero-header .product-page-meta .text-gray-700 { color: #dddadaff !important; }
            .product-hero-header .product-page-meta .product-meta-favorite .btn,
            .product-hero-header .product-page-meta .product-meta-share .btn,
            .product-hero-header .product-page-meta .product-meta-report .btn { background: none !important; color: #fff !important; border: none !important; outline: none !important;}
            .hero-action-btns .d-flex { opacity: 0; max-width: 0; overflow: hidden; transition: opacity .3s ease, max-width .3s ease; display: inline-flex !important; flex-wrap: nowrap !important; gap: .5rem !important; white-space: nowrap; }
            .product-hero-header:hover .hero-action-btns .d-flex { opacity: 1; max-width: 500px; }
            .product-hero-header .product-floating-meta { position: absolute; top: 50%; transform: translateY(-50%); right: 0; opacity: 0; transition: opacity 0.3s ease; }
            .product-hero-header:hover .product-floating-meta { opacity: 1; }";
        }

        if (($options['display_layout'] ?? '') === 'minimalist') {
            $css .= " .product-page-meta { text-align: center; }
            .product-page-meta .d-flex { justify-content: center; }
            .product-page-meta .product-page-breadcrumb { display: flex; justify-content: center; }";
        }

        if (($options['display_layout'] ?? '') === 'gallery_focus') {
            $css .= "
            .gallery-focus-meta-wrap { position: relative; z-index: 10; margin-top: -80px; padding: 0 1.5rem; }
            .gallery-focus-meta-card { background: var(--bs-body-bg); border-radius: 8px; padding: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid var(--bs-border-color); }
            .gallery-focus-meta-card .product-page-meta { text-align: center; margin-bottom: 0 !important; }
            .gallery-focus-meta-card .product-page-meta .d-flex { justify-content: center; }
            .gallery-focus-meta-card .product-page-meta .product-page-breadcrumb { display: flex; justify-content: center; }
            @media (max-width: 767.98px) {
                .gallery-focus-meta-wrap { padding: 0 1rem; }
                .gallery-focus-meta-card { padding: 1rem; }
            }";
        }

        // Floating Meta
        if (($options['display_layout'] ?? '') !== 'minimalist') {
            $css .= " .product-floating-meta { position: absolute; top: 0; right: 0; padding: 1rem; display: flex; flex-direction: column; gap: .5rem; z-index: 2; }
            .product-floating-meta .floating-meta-item { width: 32px; height: 32px; background: rgba(255, 255, 255, 0.35); backdrop-filter: blur(6px); border: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3 ease; }
            .product-floating-meta .floating-meta-item i { font-size: 1.05rem; }
            .product-floating-meta .floating-meta-item:hover { background: rgba(255, 255, 255, 0.5); transform: scale(1.1) }
            .product-floating-meta .floating-meta-item .favorite-btn { border: none !important; background: none !important; outline: none !important; }
            .product-floating-meta .floating-meta-item:hover .bi-heart::before { content: '\\F415'; color: var(--bs-danger); }
            .product-floating-meta .floating-meta-item:hover .bi-reply::before { content: '\\F51F'; color: var(--bs-success); }
            .product-floating-meta .floating-meta-item:hover .bi-flag::before { content: '\\F3CB'; color: var(--bs-warning); }";
        }

        return minifyCss($css);
    }

    /**
     * Calculate the star breakdown (1-5) for a product's reviews.
     */
    private function getStarBreakdown(Product $product): array
    {
        $starBreakdown = $product->reviews()
            ->selectRaw('stars, count(*) as count')
            ->groupBy('stars')
            ->pluck('count', 'stars')
            ->toArray();

        for ($i = 1; $i <= 5; $i++) {
            $starBreakdown[$i] = $starBreakdown[$i] ?? 0;
        }

        return $starBreakdown;
    }
}
