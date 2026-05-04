<?php

namespace App\Http\Controllers\Theme;

use App\Classes\BuilderBlocks;
use App\Classes\GoogleFonts;
use App\Http\Controllers\Controller;
use App\Models\Blog\BlogCategory;
use App\Models\Premium\PremiumPlan;
use App\Services\HomePageService;
use Illuminate\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param HomePageService $homePageService
     */
    public function __construct(
        protected HomePageService $homePageService
    ) {}

    /**
     * Display the homepage
     *
     * @return View
     */
    public function index(): View
    {
        // Check if maintenance mode is enabled
        $maintenance = settings('maintenance');
        if (!authAdmin() && $maintenance && $maintenance->status) {
            return view('vendor.maintenance');
        }

        // Get home builder elements
        $homeBlocks = BuilderBlocks::byType('home');

        // Pre-calculate view paths for all home blocks (fallback support)
        foreach ($homeBlocks as &$block) {
            $cleanId = BuilderBlocks::getViewName($block['id'], 'home_');
            $block['view'] = 'blocks.home.' . str($cleanId)->replace('_', '-');
        }
        unset($block);

        // Get home layout configuration
        $homeLayout = settings('theme_home');
        if (is_string($homeLayout)) {
            $homeLayout = json_decode($homeLayout);
        }

        // Build sections with pre-computed CSS data
        $homeSections = $this->buildHomeSections($homeLayout, $homeBlocks);

        // Collect fonts
        $homeFontsLink = $this->getGoogleFontsLink($homeLayout);

        // Get all homepage block data from service
        $blockData = $this->homePageService->getAllBlocks();

        // Merge all data and return the view
        return theme_view('home', array_merge(
            [
                'homeBlocks' => $homeBlocks,
                'homeLayout' => $homeLayout,
                'homeSections' => $homeSections,
                'homeFontsLink' => $homeFontsLink,
            ],
            $blockData
        ));
    }

    /**
     * Generate Google Fonts link based on collected fonts
     */
    protected function getGoogleFontsLink(array $layout): ?string
    {
        // Collect fonts from sections
        $fonts = [];
        if (is_array($layout)) {
            foreach ($layout as $section) {
                // Ensure section is handled as an array to access options safely or check property
                $options = is_object($section) ? ($section->options ?? []) : ($section['options'] ?? []);
                $options = (array) $options;

                if (!empty($options['font_family'])) {
                    $fonts[] = $options['font_family'];
                }
            }
        }

        return GoogleFonts::getLink($fonts);
    }

    /**
     * Build home sections with pre-computed CSS data
     *
     * @param mixed $homeLayout
     * @param array $homeBlocks
     * @return array
     */
    protected function buildHomeSections(mixed $homeLayout, array $homeBlocks): array
    {
        if (empty($homeLayout) || !is_array($homeLayout)) {
            return [];
        }

        $sections = [];

        foreach ($homeLayout as $index => $row) {
            // Ensure options is an array for buildCustomStyles
            $options = isset($row->options) ? (array) $row->options : [];

            $isFullWidth = $options['is_full_width'] ?? false;
            $sectionId = 'home-section-' . $index;

            // Generate styles using the HeaderComposer-like logic (returns string)
            $customStyles = $this->buildCustomStyles($options);
            $containerData = $this->buildContainerData($sectionId, $options);

            // Build full CSS string in Controller
            $sectionCss = "#{$sectionId} { {$customStyles} }";
            if (!empty($containerData['css'])) {
                $containerStyles = implode(' ', $containerData['css']);
                $sectionCss .= " #{$containerData['id']} { {$containerStyles} }";
            }

            // Process columns and accumulate block CSS
            $blockCss = '';
            $columns = [];
            foreach ($row->columns as $col) {
                $rawBlocks = $col->blocks ?? [];

                $columns[] = [
                    'width' => $col->width ?? 12,
                    'blocks' => $this->prepareBlocks($rawBlocks, $homeBlocks, $blockCss, $containerData['class'])
                ];
            }

            // Append block-level CSS to section CSS
            $sectionCss .= $blockCss;

            $sections[] = [
                'row' => $row,
                'index' => $index,
                'isFullWidth' => $isFullWidth,
                'sectionId' => $sectionId,
                'containerClass' => $containerData['class'],
                'containerId' => $containerData['id'],
                'columns' => $columns,
                'css' => $sectionCss,
            ];
        }

        return $sections;
    }

    /**
     * Prepare blocks for rendering by resolving paths and validating existence
     *
     * @param mixed $blocks
     * @param array $definedBlocks
     * @return array
     */
    protected function prepareBlocks(mixed $blocks, array $definedBlocks, string &$blockCss = '', string $containerClass = ''): array
    {
        if (empty($blocks) || (!is_array($blocks) && !is_object($blocks))) {
            return [];
        }

        $prepared = [];
        $definedCollection = collect($definedBlocks);

        foreach ($blocks as $block) {
            $blockId = $block->id ?? null;
            if (!$blockId) continue;

            $def = $definedCollection->firstWhere('id', $blockId);

            $options = (array)($block->options ?? []);
            $isActive = $options['is_active'] ?? 1;

            if ($def && $isActive) {
                $cleanId = BuilderBlocks::getViewName($blockId, 'home_');
                $viewKey = str($cleanId)->replace('_', '-');

                // Generate unique block ID for scoped CSS
                $uniqueBlockId = 'hb-' . str($cleanId)->replace('_', '-') . '-' . count($prepared);
                $options['uniqueId'] = $uniqueBlockId;
                $options['containerClass'] = $containerClass;

                // Process block: prepare data + build CSS (HeaderComposer pattern)
                $this->processBlock($cleanId, $uniqueBlockId, $options, $blockCss);

                // Prepare block title area data (pre-computed for the blade partial)
                $options['blockTitle'] = $this->prepareBlockTitle($uniqueBlockId, $options);

                // Append block title CSS
                if (!empty($options['blockTitle']['_titleCss'])) {
                    $blockCss .= $options['blockTitle']['_titleCss'];
                }

                // Calculate Wrapper Classes
                $wrapperClass = 'home-block-wrapper';
                $visibility = $options['visibility'] ?? 'all';

                if ($visibility === 'desktop') {
                    $wrapperClass .= ' d-none d-lg-block';
                } elseif ($visibility === 'mobile') {
                    $wrapperClass .= ' d-block d-lg-none';
                }

                if (!empty($options['custom_class'])) {
                    $wrapperClass .= ' ' . $options['custom_class'];
                }

                $prepared[] = [
                    'id' => $blockId,
                    'view' => 'blocks.home.' . $viewKey,
                    'data' => $options,
                    'wrapper_class' => $wrapperClass
                ];
            }
        }
        return $prepared;
    }

    /**
     * Generate custom CSS styles from options (Adapted from HeaderComposer)
     */
    protected function buildCustomStyles(array $options): string
    {
        $styles = [];

        // Text & Background Colors
        if (!empty($options['text_color'])) {
            $styles[] = "color: {$options['text_color']}";
        }
        if (!empty($options['bg_color'])) {
            $styles[] = "background-color: {$options['bg_color']}";
        }

        // Common Units
        $mUnit = $options['margin_unit'] ?? 'px';
        $pUnit = $options['padding_unit'] ?? 'px';
        $marginBottomValue = $options['margin_bottom'] ?? ($mUnit === 'rem' ? 0 : 0); //1.5rem or 24px

        // Margins
        $this->addStyleUnit($styles, 'margin-top', $options['margin_top'] ?? null, $mUnit);
        $this->addStyleUnit($styles, 'margin-right', $options['margin_right'] ?? null, $mUnit);
        $this->addStyleUnit($styles, 'margin-bottom', $marginBottomValue, $mUnit);
        $this->addStyleUnit($styles, 'margin-left', $options['margin_left'] ?? null, $mUnit);

        // Paddings
        $this->addStyleUnit($styles, 'padding-top', $options['padding_top'] ?? null, $pUnit);
        $this->addStyleUnit($styles, 'padding-right', $options['padding_right'] ?? null, $pUnit);
        $this->addStyleUnit($styles, 'padding-bottom', $options['padding_bottom'] ?? null, $pUnit);
        $this->addStyleUnit($styles, 'padding-left', $options['padding_left'] ?? null, $pUnit);

        // Borders
        $borderStyle = $options['border_style'] ?? null;

        if ($borderStyle && $borderStyle !== 'none') {
            $styles[] = "border-style: {$borderStyle}";
            $styles[] = "border-color: " . ($options['border_color'] ?? '#dee2e6');

            $defaultWidth = $options['border_width'] ?? '0';
            $styles[] = "border-top-width: " . ($options['border_top_width'] ?? $defaultWidth) . "px";
            $styles[] = "border-right-width: " . ($options['border_right_width'] ?? $defaultWidth) . "px";
            $styles[] = "border-bottom-width: " . ($options['border_bottom_width'] ?? $defaultWidth) . "px";
            $styles[] = "border-left-width: " . ($options['border_left_width'] ?? $defaultWidth) . "px";
        } elseif ($borderStyle === 'none') {
            $styles[] = "border: none !important";
        }

        // Border Radius
        $this->addStyleUnit($styles, 'border-radius', $options['border_radius'] ?? null);

        // Background Image
        if (!empty($options['bg_image'])) {
            $bg = $options['bg_image'];
            if (str_starts_with($bg, 'http') || str_starts_with($bg, '//')) {
                $bgUrl = $bg;
            } elseif (file_exists(public_path($bg))) {
                $bgUrl = asset($bg);
            } else {
                $bgUrl = theme_asset('images/hero-default.jpg');
            }
            $styles[] = "background-image: url('" . $bgUrl . "')";
            $styles[] = "background-repeat: " . ($options['bg_repeat'] ?? 'no-repeat');
            $styles[] = "background-size: " . ($options['bg_size'] ?? 'cover');
            $styles[] = "background-position: " . ($options['bg_position'] ?? 'center center');
        }

        // Font Family
        if (!empty($options['font_family'])) {
            $font = stripslashes($options['font_family']);
            $styles[] = "font-family: {$font}";
        }

        // Box Shadow
        if (!empty($options['box_shadow_toggle']) && $options['box_shadow_toggle'] === 'on') {
            $x = $options['box_shadow_x'] ?? 0;
            $y = $options['box_shadow_y'] ?? 0;
            $blur = $options['box_shadow_blur'] ?? 0;
            $spread = $options['box_shadow_spread'] ?? 0;
            $color = $options['box_shadow_color'] ?? 'rgba(0,0,0,0.1)';
            $styles[] = "box-shadow: {$x}px {$y}px {$blur}px {$spread}px {$color} !important";
        }

        // Min Height
        $this->addStyleUnit($styles, 'min-height', $options['min_height'] ?? null);

        return implode('; ', $styles);
    }

    /**
     * Build container data (class, id, css) for a section
     *
     * @param string $sectionId
     * @param array $options
     * @return array
     */
    protected function buildContainerData(string $sectionId, array $options): array
    {
        $containerId = $sectionId . '-container';
        $containerCss = [];

        $widthOption = $options['container_width'] ?? 'default';

        if ($widthOption === 'boxed') {
            // Boxed - constrained 1080px container
            $containerClass = 'container container-boxed';
        } elseif ($widthOption === 'full_width') {
            $containerClass = 'container-fluid';
        } else {
            // Default: same as header/footer (standard Bootstrap container)
            $containerClass = 'container container-default';
        }

        return [
            'class' => $containerClass,
            'id' => $containerId,
            'css' => $containerCss,
        ];
    }

    /**
     * Helper to add style with unit (From HeaderComposer)
     */
    protected function addStyleUnit(array &$styles, string $property, $value, string $unit = 'px'): void
    {
        if ($value !== null && $value !== '') {
            $styles[] = "{$property}: {$value}{$unit} !important";
        }
    }

    // =========================================================================
    // BLOCK PROCESSING (HeaderComposer Pattern)
    // =========================================================================

    /**
     * Block handler registry - maps block IDs to their data/styles methods
     */
    protected function getBlockHandlers(): array
    {
        return [
            'hero'              => ['data' => 'prepareHeroData', 'styles' => 'buildHeroStyle'],
            'countdown'         => ['data' => 'prepareCountdownData', 'styles' => 'buildCountdownStyle'],
            'divider'           => ['data' => 'prepareDividerData', 'styles' => 'buildDividerStyle'],
            'newsletter'        => ['data' => 'prepareNewsletterData', 'styles' => 'buildNewsletterStyle'],
            'login_form'        => ['data' => 'prepareLoginFormData', 'styles' => 'buildLoginFormStyle'],
            'offer_banner'      => ['data' => 'prepareOfferBannerData', 'styles' => 'buildOfferBannerStyle'],
            'slider'            => ['data' => 'prepareSliderData', 'styles' => 'buildSliderStyle'],
            'categories'        => ['data' => 'prepareCategoriesData'],
            'tabs'              => ['data' => 'prepareTabsData', 'styles' => 'buildTabsStyle'],
            'product_tabs'      => ['data' => 'prepareProductTabsData', 'styles' => 'buildProductStyle'],
            'html'              => ['data' => 'prepareHtmlBlockData'],
            'rich_text'         => ['data' => 'prepareRichTextData'],
            'image'             => ['data' => 'prepareImageData'],
            'button'            => ['data' => 'prepareButtonData'],
            'social_icons'      => ['data' => 'prepareSocialIconsData', 'styles' => 'buildSocialIconsStyle'],
            'FaQs'              => ['data' => 'prepareFaqsData', 'styles' => 'buildFaqsStyle'],
            'widget'            => ['data' => 'prepareWidgetData'],
            'testimonials'      => ['data' => 'prepareTestimonialsData'],
            'advertisement'     => ['data' => 'prepareAdvertisementData'],
            'premium_plans'     => ['data' => 'preparePremiumPlansData', 'styles' => 'buildPremiumPlansStyle'],
            'products'          => ['data' => 'prepareProductBlockData', 'styles' => 'buildProductStyle'],
            'blog_articles'     => ['data' => 'prepareBlogArticlesData'],
            'blog_categories'   => ['data' => 'prepareBlogCategoriesData'],
            'featured_seller'   => ['data' => 'prepareFeaturedSellerData'],
        ];
    }

    /**
     * Process a block: call its handler to prepare data and build CSS
     */
    protected function processBlock(string $cleanId, string $uniqueBlockId, array &$options, string &$blockCss): void
    {
        $handlers = $this->getBlockHandlers();

        if (!isset($handlers[$cleanId])) {
            return;
        }

        $config = $handlers[$cleanId];

        // Prepare data
        if (!empty($config['data'])) {
            $method = $config['data'];
            if (method_exists($this, $method)) {
                $options = array_merge($options, $this->{$method}($uniqueBlockId, $options));
            }
        }

        // Build CSS
        if (!empty($config['styles'])) {
            $method = $config['styles'];
            if (method_exists($this, $method)) {
                $blockCss .= $this->{$method}($uniqueBlockId, $options);
            }
        }
    }

    /**
     * Prepare block title area data (pre-computed for the blade partial)
     */
    protected function prepareBlockTitle(string $uniqueBlockId, array $options): array
    {
        $titleStyle     = $options['title_style'] ?? 'none';
        $title          = $options['title'] ?? '';
        $subtitle       = $options['subtitle'] ?? '';
        $showSubtitle   = ($options['show_subtitle'] ?? '1') == '1';
        $showBorder     = !empty($options['show_bottom_border']);
        $titleColor     = $options['title_color'] ?? '';
        $fontSize       = $options['title_font_size'] ?? 'fs-4';
        $fontWeight     = $options['title_font_weight'] ?? 'fw-medium';
        $transform      = $options['title_transform'] ?? '';
        $alignment      = $options['title_alignment'] ?? '';
        $blockAlignment = $options['block_alignment'] ?? 'left';
        $titleIcon      = $options['title_icon'] ?? '';
        $showViewMore   = !empty($options['show_view_more']);
        $vmStyle        = $options['view_more_style'] ?? 'text';
        $vmText         = $options['view_more_text'] ?? '';
        $vmUrl          = $options['view_more_url'] ?? '';
        $vmIcon         = $options['view_more_icon'] ?? '';

        // Category Menu options
        $showCategoryDropdown = !empty($options['show_category_dropdown']);
        $catStyle       = $options['category_dropdown_style'] ?? 'text';
        $catIcon        = $options['category_dropdown_icon'] ?? '';
        $catLabel       = $options['category_dropdown_label'] ?? '';

        // Should we render the block title at all?
        $show = $titleStyle !== 'none' && !empty($title);

        // Icon class: stored as 'bi-star', rendered as 'bi bi-star'
        $iconClass = $titleIcon ? "bi {$titleIcon}" : '';

        // Title heading classes
        $titleClasses = trim("{$fontSize} fw-semibold mb-0 {$transform}");

        // Flex alignment classes
        $alignClass = match ($alignment) {
            'center' => 'justify-content-center text-center',
            'end'    => 'justify-content-between flex-row-reverse',
            default  => 'justify-content-between',
        };

        // Content center class
        $contentCenterClass = $alignment === 'center' ? 'text-center' : '';

        // Container classes
        $containerClass = "section-title-area section-title-{$titleStyle} mb-4"
            . ($showBorder ? ' section-title-has-border' : '');

        // View More button classes
        $vmClasses = match ($vmStyle) {
            'pill'   => 'btn btn-sm btn-primary rounded-pill px-3',
            'rounded' => 'btn btn-sm btn-primary rounded-1',
            'icon_only' => 'text-primary p-0',
            default  => 'text-primary',
        };

        // Category dropdown classes
        $catClasses = match ($catStyle) {
            'pill'   => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
            'rounded' => 'btn btn-sm btn-outline-secondary rounded-1',
            default  => '',
        };

        // Map alignment to Bootstrap classes
        $blockAlignmentClass = match ($blockAlignment) {
            'center' => 'justify-content-center text-center',
            'right' => 'justify-content-end text-end',
            default => 'justify-content-start text-start',
        };

        // Build scoped CSS for block title (instead of inline styles)
        $titleCss = $this->buildBlockTitleStyle($uniqueBlockId, $titleColor);

        return [
            'show'              => $show,
            'style'             => $titleStyle,
            'title'             => $title,
            'subtitle'          => $subtitle,
            'showSubtitle'      => $showSubtitle,
            'showBorder'        => $showBorder,
            'fontSize'          => $fontSize,
            'fontWeight'        => $fontWeight,
            'transform'         => $transform,
            'iconClass'         => $iconClass,
            'titleClasses'      => $titleClasses,
            'alignClass'        => $alignClass,
            'contentCenterClass' => $contentCenterClass,
            'containerClass'    => $containerClass,
            'showCategoryDropdown' => $showCategoryDropdown,
            'categoryDropdownStyle' => $catStyle,
            'categoryDropdownClasses' => $catClasses,
            'categoryDropdownIcon' => $catIcon,
            'categoryDropdownLabel' => $catLabel,
            'showViewMore'      => $showViewMore,
            'viewMoreStyle'     => $vmStyle,
            'viewMoreIcon'      => $vmIcon,
            'viewMoreClasses'   => $vmClasses,
            'viewMoreText'      => $vmText,
            'viewMoreUrl'       => $vmUrl,
            'blockAlignmentClass' => $blockAlignmentClass,
            '_titleCss'         => $titleCss,
        ];
    }

    /**
     * Build block title scoped CSS
     */
    protected function buildBlockTitleStyle(string $uniqueId, string $titleColor): string
    {
        if (empty($titleColor)) {
            return '';
        }

        $css = '';
        $css .= "#{$uniqueId} .section-title-area h2{color:{$titleColor}}";
        $css .= "#{$uniqueId} .section-title-subtitle{color:{$titleColor};opacity:.85}";
        $css .= "#{$uniqueId} .sta-accent-line,#{$uniqueId} .sta-underline,#{$uniqueId} .sta-bottom-border{background:{$titleColor}}";
        $css .= "#{$uniqueId} .sta-badge,#{$uniqueId} .sta-parallelogram,#{$uniqueId} .sta-square{background:{$titleColor}}";

        return minifyCss($css);
    }

    // =========================================================================
    // BLOCK DATA PREPARATION METHODS
    // =========================================================================

    /**
     * Prepare Hero block data
     */
    protected function prepareHeroData(string $uniqueId, array $options): array
    {
        $designStyle = $options['hero_design'] ?? 'classic';
        $textAlign   = $options['text_align'] ?? 'center';
        $textWidth   = $options['text_width'] ?? 60;
        $titleDesign = $options['title_design'] ?? 'default';

        $type = $options['type'] ?? 'video';
        $imageUrl = $options['image'] ?? ($options['content']['image'] ?? '');
        $image = '';
        if (!empty($imageUrl)) {
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $image = $imageUrl;
            } elseif (file_exists(public_path($imageUrl))) {
                $image = asset($imageUrl);
            } else {
                $image = theme_asset('images/hero-default.jpg');
            }
        } else {
            $image = theme_asset('images/hero-default.jpg');
        }
        $videoUrl = !empty($options['video_url'])
            ? (filter_var($options['video_url'], FILTER_VALIDATE_URL) ? $options['video_url'] : theme_asset($options['video_url']))
            : '';

        // Extract YouTube video ID if applicable
        $youtubeId = '';
        if ($videoUrl && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/', $videoUrl, $m)) {
            $youtubeId = $m[1];
        }

        $overlayColor   = $options['overlay_color'] ?? '#000000';
        $overlayOpacity = $options['overlay_opacity'] ?? '0.5';
        $searchEnable   = !empty($options['search_enable']);
        $searchPlaceholder = $options['search_placeholder'] ?? '';
        $titleColor     = $options['title_color'] ?? '';
        $subtitleColor  = $options['subtitle_color'] ?? '';
        $showBottomFade = ($options['show_bottom_fade'] ?? '1') == '1';

        // Buttons
        $btn1Text  = $options['btn1_text'] ?? '';
        $btn1Url   = $options['btn1_url'] ?? '#';
        $btn1Class = $options['btn1_class'] ?? 'primary';
        $btn2Text  = $options['btn2_text'] ?? '';
        $btn2Url   = $options['btn2_url'] ?? '#';
        $btn2Class = $options['btn2_class'] ?? 'outline-light';

        // Alignment mapping
        $detailsAlign = match ($textAlign) {
            'left'  => 'text-start align-items-start',
            'right' => 'text-end align-items-end',
            default => 'text-center align-items-center',
        };
        $colAlign = match ($textAlign) {
            'left'  => 'justify-content-start',
            'right' => 'justify-content-end',
            default => 'justify-content-center',
        };

        // Design-specific tweaks
        $containerClass  = $options['containerClass'] ?: 'container container-default';
        $sectionMinHeight = 'min-height: 600px;';
        $boxStyles = '';

        if ($designStyle === 'creative') {
            $boxStyles = 'background: rgba(255, 255, 255, 0.3); padding: 3rem; border-radius: 1rem; color: #212529; box-shadow: 0 10px 30px rgba(0,0,0,0.1); backdrop-filter: blur(10px);';
            if ($textAlign === 'center') $boxStyles .= ' margin: 0 auto;';
        }

        if ($designStyle === 'modern') {
            $sectionMinHeight = 'min-height: 700px;';
            if ($textAlign === 'center') $textAlign = 'left';
            $detailsAlign = 'text-start align-items-start';
            $colAlign = 'justify-content-start';
        }

        if ($designStyle === 'minimal') {
            $sectionMinHeight = 'min-height: 500px;';
        }

        // Title classes & styles
        $titleClass = 'fw-bold mb-2 display-4';
        $titleStyle = 'text-shadow: 0 2px 4px rgba(0,0,0,0.3);';
        if ($titleColor) {
            $titleStyle .= " color: {$titleColor} !important;";
        }

        if ($designStyle === 'creative') {
            $titleClass = 'fw-bold mb-2 display-5' . ($titleColor ? '' : ' text-dark');
            $titleStyle = $titleColor ? "color: {$titleColor} !important;" : '';
        } elseif ($titleDesign === 'highlight') {
            $titleStyle .= ' background: linear-gradient(120deg, var(--bs-primary) 0%, var(--bs-primary) 100%); background-repeat: no-repeat; background-size: 100% 0.3em; background-position: 0 88%;';
        } elseif ($titleDesign === 'underline') {
            $titleClass .= ' text-decoration-underline text-decoration-thickness-4';
        } elseif ($titleDesign === 'outline') {
            $titleStyle .= ' -webkit-text-stroke: 1px #fff; color: transparent;';
        } elseif ($titleDesign === 'display') {
            $titleClass = 'fw-black mb-4 display-1';
        }

        // Subtitle style
        $subtitleClass = ($designStyle === 'creative') ? ($subtitleColor ? '' : 'text-muted') : ($subtitleColor ? '' : 'text-white');
        $subtitleStyle = 'opacity: 0.9;';
        if ($subtitleColor) {
            $subtitleStyle .= " color: {$subtitleColor} !important;";
        } elseif ($designStyle === 'modern') {
            $subtitleStyle .= ' color: white !important;';
        }

        return [
            'containerClass' => $containerClass,
            'title'            => $options['title'] ?? '',
            'subtitle'         => $options['subtitle'] ?? '',
            'designStyle'      => $designStyle,
            'textAlign'        => $textAlign,
            'textWidth'        => $textWidth,
            'bgType'           => $type,
            'heroImage'        => $image,
            'heroVideoUrl'     => $videoUrl,
            'heroYoutubeId'    => $youtubeId,
            'overlayColor'     => $overlayColor,
            'overlayOpacity'   => $overlayOpacity,
            'searchEnable'     => $searchEnable,
            'searchPlaceholder' => $searchPlaceholder,
            'btn1Text'         => $btn1Text,
            'btn1Url'          => $btn1Url,
            'btn1Class'        => $btn1Class,
            'btn2Text'         => $btn2Text,
            'btn2Url'          => $btn2Url,
            'btn2Class'        => $btn2Class,
            'detailsAlign'     => $detailsAlign,
            'colAlign'         => $colAlign,
            'titleClass'       => $titleClass,
            'subtitleClass'    => $subtitleClass,
            'showBottomFade'   => $showBottomFade,
            // CSS-only values (used by buildHeroStyle, not in blade)
            '_sectionMinHeight' => $sectionMinHeight,
            '_boxStyles'        => $boxStyles,
            '_titleStyle'       => $titleStyle,
            '_subtitleStyle'    => $subtitleStyle,
        ];
    }

    /**
     * Build Hero block CSS
     */
    protected function buildHeroStyle(string $uniqueId, array $options): string
    {
        $css = '';
        $minHeight   = $options['_sectionMinHeight'] ?? 'min-height: 600px;';
        $image       = $options['heroImage'] ?? '';
        $overlay     = $options['overlayColor'] ?? '#000000';
        $opacity     = $options['overlayOpacity'] ?? '0.5';
        $textWidth   = $options['textWidth'] ?? 60;
        $boxStyles   = $options['_boxStyles'] ?? '';
        $titleStyle  = $options['_titleStyle'] ?? '';
        $subStyle    = $options['_subtitleStyle'] ?? '';
        $design      = $options['designStyle'] ?? 'classic';

        // Main wrapper
        $css .= "#{$uniqueId}{{$minHeight};display:flex;align-items:center}";

        // Background image (shared for classic/minimal/creative + modern mobile)
        $bgCss = "background-image:url('{$image}');background-size:cover;background-position:center";
        $css .= "#{$uniqueId} .hero-bg{{$bgCss}}";

        // Modern: desktop image on right half, dark panel on left
        if ($design === 'modern') {
            $css .= "#{$uniqueId} .hero-bg-desktop{{$bgCss}}";
            $css .= "#{$uniqueId} .hero-overlay-panel{background-color:{$overlay}}";
        }

        // Overlay
        $css .= "#{$uniqueId} .hero-overlay{background-color:{$overlay};opacity:{$opacity}}";

        // Gradient overlays
        if ($design === 'gradient') {
            $css .= "#{$uniqueId} .hero-gradient-1{background:linear-gradient(135deg,{$overlay} 0%,rgba(0,0,0,0) 100%);opacity:0.85}";
            $css .= "#{$uniqueId} .hero-gradient-2{background:linear-gradient(to right,{$overlay} 10%,transparent 80%);opacity:{$opacity}}";
        }

        // Content column max-width
        $css .= "#{$uniqueId} .hero-content-col{max-width:{$textWidth}%}";

        // Box styles (creative design)
        if (!empty($boxStyles)) {
            $css .= "#{$uniqueId} .hero-box{{$boxStyles}}";
        }

        // Title
        if (!empty($titleStyle)) {
            $css .= "#{$uniqueId} .hero-title{{$titleStyle}}";
        }

        // Subtitle
        if (!empty($subStyle)) {
            $css .= "#{$uniqueId} .hero-subtitle{{$subStyle}}";
        }

        // Search wrapper
        $css .= "#{$uniqueId} .hero-search{max-width:600px}";

        // Bottom fade (always output CSS; blade controls visibility via showBottomFade toggle)
        $css .= "#{$uniqueId} .hero-bottom-fade{height:60px;background:linear-gradient(to top,rgba(255,255,255,1),rgba(255,255,255,0))}";

        return minifyCss($css);
    }

    /**
     * Prepare Countdown block data
     */
    protected function prepareCountdownData(string $uniqueId, array $options): array
    {
        $content = (array)($options['content'] ?? []);
        $bgImage = $content['bg_image'] ?? $options['bg_image'] ?? null;
        $bgUrl = null;

        $useBgImage = filter_var($options['use_bg_image'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($useBgImage && !empty($bgImage)) {
            if (filter_var($bgImage, FILTER_VALIDATE_URL)) {
                $bgUrl = $bgImage;
            } elseif (file_exists(public_path($bgImage))) {
                $bgUrl = asset($bgImage);
            }
        }

        return [
            'containerClass'  => $options['containerClass'] ?? 'container container-default',
            'blockAlign'      => $options['block_alignment'] ?? 'center',
            'countdownDate'   => $options['countdown_date'] ?? null,
            'countdownStyle'  => $options['block_style'] ?? 'default',
            'countdownBgUrl' => $bgUrl,
            'useBgImage'      => $useBgImage,
            'bgColor'         => $options['bg_color'] ?? '#0d6efd',
            'textColor'       => $options['text_color'] ?? '#ffffff',
            'showDays'        => filter_var($options['show_days'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showHours'       => filter_var($options['show_hours'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showMinutes'     => filter_var($options['show_minutes'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showSeconds'     => filter_var($options['show_seconds'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'btnText'         => $options['btn_text'] ?? '',
            'btnUrl'          => $options['btn_url'] ?? '',
            'btnStyle'        => $options['btn_style'] ?? 'btn-primary',
            'btnIcon'         => $options['btn_icon'] ?? '',
        ];
    }

    /**
     * Build Countdown block CSS
     */
    protected function buildCountdownStyle(string $uniqueId, array $options): string
    {
        $style = $options['block_style'] ?? 'default';
        $bgUrl = $options['countdownBgUrl'] ?? null;
        $useBgImage = filter_var($options['use_bg_image'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $bgColor = $options['bg_color'] ?? 'transparent';
        $textColor = $options['text_color'] ?? '#ffffff';

        // Base background logic
        $bgCss = ($useBgImage && $bgUrl)
            ? "background-image:url('{$bgUrl}');background-size:cover;background-position:center"
            : "background-color:{$bgColor}";

        // Main Block Style
        $css = "#{$uniqueId} .countdown-block{{$bgCss};color:{$textColor};min-height:200px}";

        // Ensure Text Color applies to all relevant children
        $css .= "#{$uniqueId} .countdown-description{color:{$textColor}}";
        $css .= "#{$uniqueId} .countdown-number{color:{$textColor}}";
        $css .= "#{$uniqueId} .countdown-label{color:{$textColor}}";

        // Specific Styles
        if ($style === 'circle') {
            $css .= "#{$uniqueId} .countdown-item{width:100px;height:100px;border:3px solid {$textColor};border-radius:50%;display:flex;flex-direction:column;justify-content:center;align-items:center;margin:0 10px}";

            // Remove transparency override if we want to respect the user's choices
            $css .= "#{$uniqueId} .countdown-block{padding:4rem 0}";
        } elseif ($style === 'digital') {
            $css .= "#{$uniqueId} .countdown-item{background:rgba(0,0,0,0.15);color:{$textColor};padding:15px 25px;border-radius:5px;font-family:'Courier New', monospace !important;box-shadow:0 0 5px rgba(0,0,0,0.4)}";
            $css .= "#{$uniqueId} .countdown-label{color:{$textColor};opacity:0.8}";
            $css .= "#{$uniqueId} .countdown-block{padding:4rem 0}";
        } elseif ($style === 'minimal') {
            $css .= "#{$uniqueId} .countdown-item{text-align:center;padding:0 2rem}";
            $css .= "#{$uniqueId} .countdown-number{font-weight:300;line-height:1;color:{$textColor}}";
            $css .= "#{$uniqueId} .countdown-block{padding:4rem 0}";
            // Separators
            $css .= "#{$uniqueId} .display-4{color:{$textColor}}";
        } else {
            // Default Style
            // The structure has .display-4 for numbers and small for labels.
            $css .= "#{$uniqueId} .display-4{color:{$textColor}}";
            $css .= "#{$uniqueId} .small{color:{$textColor}}";
        }

        return minifyCss($css);
    }

    /**
     * Prepare Divider block data
     */
    protected function prepareDividerData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId'        => $uniqueId,
            'containerClass'  => $options['container_class'] ?? 'container container-custom',
            'blockAlign'      => $options['block_alignment'] ?? 'center',
            'dividerHeight'   => $options['divider_height'] ?? '1',
            'dividerColor'    => $options['divider_color'] ?? 'rgba(0,0,0,0.1)',
            'dividerWidth'    => $options['divider_width'] ?? '100%',
            'dividerSpacing' => $options['divider_spacing'] ?? '20',
        ];
    }

    /**
     * Build Divider block CSS
     */
    protected function buildDividerStyle(string $uniqueId, array $options): string
    {
        $thickness = $options['dividerHeight'] ?? '1';
        $length    = $options['dividerWidth'] ?? '100%';
        $color     = $options['dividerColor'] ?? 'rgba(0,0,0,0.1)';
        $spacing   = $options['dividerSpacing'] ?? '20';
        $style     = $options['dividerStyle'] ?? 'horizontal';

        // Ensure units
        if (is_numeric($thickness)) $thickness .= 'px';
        if (is_numeric($spacing)) $spacing .= 'px';
        // default to % if just number for length, unless 0
        if (is_numeric($length) && $length != '0') $length .= '%';

        if ($style === 'vertical') {
            return "#{$uniqueId} .divider-line{width:{$thickness};height:{$length};background-color:{$color};margin-left:{$spacing};margin-right:{$spacing}}";
        }

        return "#{$uniqueId} .divider-line{height:{$thickness};width:{$length};background-color:{$color};margin-top:{$spacing};margin-bottom:{$spacing}}";
    }

    /**
     * Prepare Newsletter block data
     */
    protected function prepareNewsletterData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'newsletterTitle'    => $options['title'] ?? translate('Subscribe to Our Newsletter'),
            'newsletterSubtitle' => $options['subtitle'] ?? ($options['description'] ?? translate('Stay tuned for the latest updates')),
            'nlStyle'            => $options['block_style'] ?? 'generic',
            'nlPlaceholder'      => $options['nl_placeholder'] ?? null,
            'nlButtonText'       => $options['nl_button_text'] ?? null,
            'nlButtonDisplay'    => $options['nl_button_display'] ?? 'text_only',
            'nlButtonIcon'       => $options['nl_button_icon'] ?? '',
            'nlButtonStyle'      => $options['nl_button_style'] ?? 'primary',
            'nlShowName'         => !empty($options['nl_show_name']),
            'nlHideTopIcon'      => !empty($options['nl_hide_top_icon']),
            'blockAlignment'     => $options['block_alignment'] ?? 'start',
            'containerClass'     => $options['containerClass'] ?? 'container container-default',
        ];
    }

    /**
     * Build Newsletter block CSS
     */
    protected function buildNewsletterStyle(string $uniqueId, array $options): string
    {
        $css = '';
        $bg = $options['nl_bg_color'] ?? '#f8f9fa';
        $titleColor = $options['nl_title_color'] ?? '#222222';
        $titleTransform = $options['nl_title_transform'] ?? 'default';

        if (!empty($options['nl_bg_color'])) {
            $css .= "#{$uniqueId} .newsletter-section{background-color:{$bg}; padding: 2.5rem 0; border-radius: 0.75rem;}";
        }

        if (!empty($options['nl_title_color'])) {
            $css .= "#{$uniqueId} .newsletter-title{color:{$titleColor} !important}";
            $css .= "#{$uniqueId} .newsletter-subtitle{color:{$titleColor} !important; opacity: 0.85;}";
            $css .= "#{$uniqueId} .newsletter-icon{color:{$titleColor} !important}";
        }

        if ($titleTransform !== 'default') {
            $css .= "#{$uniqueId} .newsletter-title{text-transform:{$titleTransform}}";
        }

        $css .= "#{$uniqueId} .newsletter-wrapper{max-width:600px; width:100%;}";

        return minifyCss($css);
    }

    /**
     * Prepare Offer Banner block data
     */
    protected function prepareOfferBannerData(string $uniqueId, array $options): array
    {
        $align = $options['block_alignment'] ?? 'start';
        $image = '';
        if (!empty($options['image'])) {
            $imgPath = ltrim($options['image'], '/\\');
            if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                $image = $imgPath;
            } elseif (file_exists(public_path($imgPath))) {
                $image = asset($imgPath);
            } else {
                $image = theme_asset('images/hero-default.jpg');
            }
        }

        return [
            'containerClass'     => $options['containerClass'] ?? 'container container-default',
            'bannerTitle'       => $options['title'] ?? '',
            'bannerSubtitle' => $options['subtitle'] ?? '',
            'bannerBtnText'     => $options['btn_text'] ?? '',
            'bannerBtnUrl'      => $options['btn_url'] ?? '#',
            'bannerRegularPrice' => $options['regular_price'] ?? '',
            'bannerOfferPrice' => $options['offer_price'] ?? '',
            'bannerBtnStyle'    => $options['btn_style'] ?? 'primary',
            'bannerBtnIcon'     => $options['btn_icon'] ?? '',
            'bannerShowMegaphone' => !empty($options['show_megaphone']),
            'bannerImage'       => $image,
            'bannerAlign'       => $align,
            'bannerIsReverse'   => $align === 'end',
            'bannerIsCenter'    => $align === 'center',
            'bannerTextAlign'   => ($align === 'center') ? 'text-center' : 'text-start',
            'bannerStyle'       => $options['block_style'] ?? 'modern',
            'bannerBgStyle'     => $options['bg_style'] ?? '',
        ];
    }

    /**
     * Build Offer Banner block CSS
     */
    protected function buildOfferBannerStyle(string $uniqueId, array $options): string
    {
        $style = $options['block_style'] ?? 'modern';
        $css = "#{$uniqueId} .banner-img{min-height:300px;max-height:400px;object-fit:cover}";

        if ($style === 'glass') {
            $css .= "#{$uniqueId} .glass-card{background-image:url('{$options['image']}');background-size:cover;background-position:center;padding:4rem 2rem}";
            $css .= "#{$uniqueId} .glass-content{background:rgba(255,255,255,0.75);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.4);box-shadow:0 8px 32px 0 rgba(0,0,0,0.1);border-radius:16px;padding:3rem}";
        } elseif ($style === 'creative') {
            $css .= "#{$uniqueId} .creative-card{border-radius:24px;overflow:hidden}";
            $css .= "#{$uniqueId} .banner-img{min-height:100%;height:100%;border-radius:0}";
            $css .= "#{$uniqueId} .creative-content{padding:2rem}";
        } elseif ($style === 'minimal') {
            $css .= "#{$uniqueId} .minimal-card{background:transparent;border:1px solid rgba(0,0,0,0.08);border-radius:12px}";
            $css .= "#{$uniqueId} .banner-img{height:250px;min-height:250px;border-radius:12px 12px 0 0}";
            $css .= "#{$uniqueId} .minimal-content{padding:2rem}";
        }

        return minifyCss($css);
    }

    /**
     * Prepare Slider block data
     */
    protected function prepareSliderData(string $uniqueId, array $options): array
    {
        $slides = $options['content'] ?? [];
        if (!is_array($slides)) $slides = [];

        // Process slides: resolve image URLs
        $processedSlides = [];
        foreach ($slides as $slide) {
            $slide = (array)$slide;
            if (!empty($slide['image'])) {
                $imgPath = ltrim($slide['image'], '/\\');
                if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                    $slide['imageUrl'] = $imgPath;
                } elseif (file_exists(public_path($imgPath))) {
                    $slide['imageUrl'] = asset($imgPath);
                } else {
                    $slide['imageUrl'] = theme_asset('images/hero-default.jpg');
                }
            } else {
                $slide['imageUrl'] = theme_asset('images/hero-default.jpg');
            }
            $processedSlides[] = $slide;
        }

        return [
            'slides'           => $processedSlides,
            'sliderAutoplay'   => !empty($options['autoplay']),
            'sliderId'         => $uniqueId,
            'blockStyle'       => $options['block_style'] ?? 'default',
            'sliderDelay'      => (int)($options['autoplay_delay'] ?? 3000),
            'sliderLoop'       => $options['loop'] ?? true,
            'sliderPause'      => $options['pause_on_hover'] ?? false,
            'sliderPagination' => $options['show_pagination'] ?? true,
            'sliderNavigation' => $options['show_navigation'] ?? true,
            'containerClass'   => $options['container_class'] ?? 'container container-default',
            'blockAlignment'   => $options['block_alignment'] ?? 'center',
        ];
    }

    /**
     * Build Slider block CSS
     */
    protected function buildSliderStyle(string $uniqueId, array $options): string
    {
        $h = $options['height'] ?? '400';
        $style = $options['block_style'] ?? 'default';
        $accentColor = $options['accent_color'] ?? 'var(--primary_color)';

        $css = "#{$uniqueId} .swiper{height:{$h}px; border-radius: 1rem;}#{$uniqueId} .swiper-wrapper{align-items:stretch;}#{$uniqueId} .swiper-slide{height:auto; display:flex; flex-direction:column; justify-content:center; border-radius: 0.75rem; overflow: hidden; transform: translateZ(0);}#{$uniqueId} .slide-img{object-fit:cover; width:100%; height:100%; border-radius: inherit;}#{$uniqueId} a.d-block{border-radius: inherit; overflow: hidden;}";

        switch ($style) {
            case 'modern':
                $css .= "#{$uniqueId} .modern-card { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 1rem; border: 1px solid rgba(255,255,255,0.3); padding: 2rem; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }";
                break;
            case 'creative':
                $css .= "#{$uniqueId} .creative-split { display: flex; height: {$h}px; overflow: hidden; border-radius: 1rem; }";
                $css .= "#{$uniqueId} .creative-split-content { flex: 1; display: flex; align-items: center; padding: 3rem; color: #fff; }";
                $css .= "#{$uniqueId} .creative-bg-blur { position: absolute; top: 0; left: 0; width: 100%; height: 100%; filter: blur(30px); transform: scale(1.2); z-index: 0; background-position: center; background-size: cover; }";
                $css .= "#{$uniqueId} .creative-bg-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; }";
                $css .= "#{$uniqueId} .creative-content-inner { position: relative; width: 100%; z-index: 2; }";
                $css .= "#{$uniqueId} .creative-split-image { flex: 1; height: 100%; }";
                $css .= "@media (max-width: 768px) { #{$uniqueId} .creative-split { flex-direction: column-reverse; height: auto; } #{$uniqueId} .creative-split-image { height: 250px; } }";
                $css .= "#{$uniqueId} .creative-split-content h5 { color: #fff; margin:0; font-size: 1.5rem; line-height: 1.4; font-weight: 600; }";
                break;
            case 'centered':
                $css .= "#{$uniqueId} .centered-bubble { background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); color: #fff; padding: 1rem 2rem; border-radius: 50rem; display: inline-block; font-weight: 500; letter-spacing: 0.5px; }";
                break;
            case 'default':
            default:
                $css .= "#{$uniqueId} .default-gradient { background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%); }";
                break;
        }

        return minifyCss($css);
    }

    /**
     * Build Tabs CSS
     */
    protected function buildTabsStyle(string $uniqueId, array $options): string
    {
        $style = $options['block_style'] ?? 'pills';
        $accentColor = $options['accent_color'] ?? 'var(--primary_color)';
        $blockAlignment = $options['block_alignment'] ?? 'start';

        $css = "";

        switch ($style) {
            case 'underline':
                $css .= "#{$uniqueId} .nav-tabs { border-bottom: 2px solid var(--bs-gray-200); }";
                $css .= "#{$uniqueId} .nav-link { border: none; border-bottom: 2px solid transparent; border-radius: 0; color: var(--bs-gray-600); padding: 1rem 1.5rem; }";
                $css .= "#{$uniqueId} .nav-link:hover { color: {$accentColor}; }";
                $css .= "#{$uniqueId} .nav-link.active { color: {$accentColor}; border-bottom-color: {$accentColor}; background: transparent; }";
                break;

            case 'boxed':
                if ($blockAlignment === 'center') {
                    $blockAlignment = 'margin:0 auto;';
                } elseif ($blockAlignment === 'end') {
                    $blockAlignment = 'margin-left:auto;';
                } else {
                    $blockAlignment = 'margin-right:auto;';
                }
                $css .= "#{$uniqueId} .nav { max-width:fit-content; width:100%; {$blockAlignment} }";
                $css .= "#{$uniqueId} .nav-pills { background: var(--bs-gray-100); padding: 0.375rem; border-radius: 0.75rem; display: inline-flex; }";
                $css .= "#{$uniqueId} .nav-link { border-radius: 0.5rem; color: var(--bs-gray-600); padding: 0.375rem 1.25rem; font-weight: 500; }";
                $css .= "#{$uniqueId} .nav-link.active { background: #fff; color: {$accentColor}; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }";
                break;

            case 'vertical':
                $css .= "#{$uniqueId} .nav-pills { flex-direction: column; gap: 0.5rem; }";
                $css .= "#{$uniqueId} .nav-link { text-align: {$blockAlignment}; padding: 0.75rem; border-radius: 0.5rem; color: var(--bs-gray-600); background: var(--bs-gray-100); }";
                $css .= "#{$uniqueId} .nav-link.active { background: {$accentColor}; color: #fff; }";
                $css .= "#{$uniqueId} .nav-pills .nav-link:not(.active):hover { background: var(--bs-gray-200); color: var(--bs-gray-800); }";
                break;

            case 'pills':
            default:
                $css .= "#{$uniqueId} .nav-pills .nav-item { margin-right: 10px; }";
                $css .= "#{$uniqueId} .nav-pills .nav-item:last-child { margin-right: 0; }";
                $css .= "#{$uniqueId} .nav-pills .nav-link { border-radius: 50rem; padding: 0.5rem 1.5rem; color: var(--bs-gray-600); background: var(--bs-gray-100);}";
                $css .= "#{$uniqueId} .nav-pills .nav-link.active { background: {$accentColor}; color: #fff; }";
                $css .= "#{$uniqueId} .nav-pills .nav-link:not(.active):hover { background: var(--bs-gray-200); color: var(--bs-gray-800); }";
                break;
        }

        return minifyCss($css);
    }

    /**
     * Prepare Tabs block data
     */
    protected function prepareTabsData(string $uniqueId, array $options): array
    {
        $blockStyle = $options['block_style'] ?? 'pills';
        $blockAlignment = $options['block_alignment'] ?? 'center';
        $containerClass = $options['containerClass'] ?? 'container container-custom';
        $tabBgStyle = $options['tab_bg_style'] ?? 'white';
        $tabContentShadow = $options['tab_content_shadow'] ?? false;
        $tabs = $options['content'] ?? [];
        if (!is_array($tabs)) $tabs = [];

        $processedTabs = [];
        foreach ($tabs as $tab) {
            $tab = (array)$tab;
            $tab['htmlContent'] = $tab['html'] ?? ($tab['content']['html'] ?? '');
            $processedTabs[] = $tab;
        }

        $rowClass = $blockStyle === 'vertical' ? 'flex-column flex-md-row' : '';
        $colClassTab = $blockStyle === 'vertical' ? 'col-md-3 mb-3 mb-md-0' : 'col-12';
        $colClassContent = $blockStyle === 'vertical' ? 'col-md-9' : 'col-12';
        $tabClass = $blockStyle === 'underline' ? 'nav-tabs' : 'nav-pills';
        $tabAlignmentClass = $blockStyle === 'vertical'
            ? 'flex-column'
            : 'mb-3 justify-content-' . ($blockAlignment ?? 'center');

        return [
            'tabItems'       => $processedTabs,
            'tabsId'         => $uniqueId,
            'blockStyle'     => $blockStyle,
            'blockAlignment' => $blockAlignment,
            'containerClass' => $containerClass,
            'rowClass'       => $rowClass,
            'colClassTab'    => $colClassTab,
            'colClassContent' => $colClassContent,
            'tabClass'       => $tabClass,
            'tabAlignmentClass' => $tabAlignmentClass,
            'tabBgStyle'     => $tabBgStyle,
            'tabContentShadow' => $tabContentShadow,
        ];
    }

    /**
     * Prepare Product Tabs block data
     */
    protected function prepareProductTabsData(string $uniqueId, array $options): array
    {
        // Reuse all product card options (grid, styles, action buttons, etc.)
        $baseData = $this->prepareProductBlockData($uniqueId, $options);

        // Remove category-related keys (tabs block doesn't use category filters)
        unset($baseData['categories'], $baseData['activeCategorySlug']);

        // Build active tabs from options
        $tabs = [
            'latest'       => 'Latest',
            'trending'     => 'Trending',
            'featured'     => 'Featured',
            'best_selling' => 'Best Selling',
            'sale'         => 'Discounted',
            'free'         => 'Free',
            'premium'      => 'Premium',
        ];

        $activeTabs = [];

        foreach ($tabs as $key => $label) {
            if (!empty($options["show_{$key}"])) {
                $activeTabs[$key] = translate($label);
            }
        }

        // Delegate product loading to service
        $tabProducts = $this->homePageService->getProductsByTabs($activeTabs, $options);

        // Tab navigation style & alignment
        $tabStyle = $options['tab_nav_style'] ?? 'pills';
        $tabAlignment = $options['tab_nav_alignment'] ?? 'end';

        return array_merge($baseData, [
            'productTabsActiveTabs' => $activeTabs,
            'productTabsProducts'   => $tabProducts,
            'productTabsId'         => $uniqueId,
            'tabNavStyle'           => $tabStyle,
            'tabNavAlignment'       => $tabAlignment,
        ]);
    }

    /**
     * Prepare HTML block data
     */
    protected function prepareHtmlBlockData(string $uniqueId, array $options): array
    {
        return [
            'htmlContent' => $options['custom_html'] ?? '',
            'uniqueId'        => $uniqueId,
            'containerClass'  => $options['containerClass'] ?? 'container container-custom',
        ];
    }

    /**
     * Prepare Rich Text block data
     */
    protected function prepareRichTextData(string $uniqueId, array $options): array
    {
        return [
            'richTextContent' => $options['rich_text'] ?? '',
            'uniqueId'        => $uniqueId,
            'containerClass'  => $options['containerClass'] ?? 'container container-custom',
        ];
    }

    /**
     * Prepare Image block data
     */
    protected function prepareImageData(string $uniqueId, array $options): array
    {
        $content  = (array)($options['content'] ?? []);
        $imageUrl = $content['image'] ?? $options['image'] ?? null;

        $blockImageUrl = null;
        if (!empty($imageUrl)) {
            $blockImageUrl = asset($imageUrl);
        }

        return [
            'blockImageUrl'  => $blockImageUrl,
            'blockImageLink' => $options['link'] ?? null,
            'imageTitle'     => $options['title'] ?? null,
            'imageSubtitle'  => $options['subtitle'] ?? null,
            'containerClass' => $options['containerClass'] ?? 'container container-custom',
            'blockAlign'     => $options['block_alignment'] ?? 'center',
            'uniqueId'       => $uniqueId,
            'imageSize'      => $options['image_size'] ?? 'w-100',
            'imageCorner'    => $options['image_corner'] ?? 'rounded-0',
            'blockStyle'     => $options['block_style'] ?? 'default',
        ];
    }

    /**
     * Prepare Button block data
     */
    /**
     * Prepare Button block data
     */
    protected function prepareButtonData(string $uniqueId, array $options): array
    {
        // Support both new singular options and legacy array content (fallback)
        $buttons = $options['content'] ?? [];
        if (!is_array($buttons)) $buttons = [];

        // If new options are present, use them as the primary button
        $primaryBtn = [];
        if (!empty($options['btn_text'])) {
            $primaryBtn = [
                'uniqueId'    => $uniqueId,
                'label'       => $options['btn_text'],
                'link'        => $options['btn_link'] ?? '#',
                'target'      => $options['btn_target'] ?? '_self',
                'btnStyle'    => $options['btn_style'] ?? 'btn-primary',
                'btnSize'     => $options['btn_size'] ?? '',
                'btnShape'    => $options['btn_shape'] ?? '',
                'btnIcon'     => $options['btn_icon'] ?? '',
                'aosAnim'     => $options['aos_animation'] ?? '',
                'aosDelay'    => $options['aos_delay'] ?? '0',
            ];
        }

        return [
            'buttonData'    => $primaryBtn,
            'blockAlignment' => $options['block_alignment'] ?? 'center',
            'containerClass' => $options['containerClass'] ?? 'container container-default',
        ];
    }

    /**
     * Prepare Social Icons block data
     */
    protected function prepareCategoriesData(string $uniqueId, array $options): array
    {
        $items = $options['content'] ?? [];
        if (!is_array($items)) $items = [];

        $appUrl = rtrim(config('app.url'), '/');

        $categories = [];
        foreach ($items as $item) {
            $item = (array)$item;
            $image = $item['image'] ?? '';
            // Strip full URL to relative path (builder stores full URLs)
            if (!empty($image) && str_starts_with($image, $appUrl)) {
                $image = ltrim(substr($image, strlen($appUrl)), '/');
            }
            // Also handle http/https prefix for any domain
            if (!empty($image) && preg_match('#^https?://.+?/(.+)$#', $image, $m)) {
                $image = $m[1];
            }

            $categories[] = (object)[
                'title' => $item['title'] ?? '',
                'link'  => $item['link'] ?? '#',
                'image' => !empty($image) ? $image : 'images/placeholders/category.png',
            ];
        }

        $categories = collect($categories);
        $blockStyle = $options['block_style'] ?? 'swiper';
        $blockAlignment = $options['block_alignment'] ?? 'left';
        $containerClass  = $options['containerClass'] ?? 'container container-default';

        return [
            'homeCategories'  => $categories,
            'blockStyle'      => $blockStyle,
            'blockAlignment'  => $blockAlignment,
            'containerClass'  => $containerClass,
        ];
    }

    protected function prepareSocialIconsData(string $uniqueId, array $options): array
    {
        $icons = $options['content'] ?? [];
        if (!is_array($icons)) $icons = [];

        // Map platform names to Bootstrap Icons
        $iconMap = [
            'Facebook'  => 'bi bi-facebook',
            'Twitter'   => 'bi bi-twitter-x',
            'Instagram' => 'bi bi-instagram',
            'LinkedIn'  => 'bi bi-linkedin',
            'YouTube'   => 'bi bi-youtube',
            'Pinterest' => 'bi bi-pinterest',
            'TikTok'    => 'bi bi-tiktok',
            'WhatsApp'  => 'bi bi-whatsapp',
            'Telegram'  => 'bi bi-telegram',
            'Snapchat'  => 'bi bi-snapchat',
            'Reddit'    => 'bi bi-reddit',
            'Discord'   => 'bi bi-discord',
            'Github'    => 'bi bi-github',
            'Behance'   => 'bi bi-behance',
            'Dribbble'  => 'bi bi-dribbble',
            'Website'   => 'bi bi-globe',
            'Email'     => 'bi bi-envelope-fill',
            'Phone'     => 'bi bi-telephone-fill',
        ];

        $processedIcons = [];
        foreach ($icons as $icon) {
            $icon = (array)$icon;
            $name = $icon['name'] ?? 'Website';

            $processedIcons[] = [
                'name'      => $name,
                'link'      => $icon['link'] ?? '#',
                'iconClass' => $iconMap[$name] ?? 'bi bi-link-45deg',
                'brandClass' => 'brand-' . Str::slug($name),
            ];
        }

        return [
            'socialIcons' => $processedIcons,
            'uniqueId'       => $uniqueId,
            'blockAlignment' => $options['block_alignment'] ?? 'center',
            'containerClass' => $options['containerClass'] ?? 'container container-default',
            'showName'       => in_array($options['block_style'] ?? '', ['inline_name', 'bottom_name']),
        ];
    }

    /**
     * Build Social Icons CSS
     */
    protected function buildSocialIconsStyle(string $uniqueId, array $options): string
    {
        $style = $options['block_style'] ?? 'default';
        $accentColor = $options['accent_color'] ?? 'var(--primary_color)';
        $multicolor = !empty($options['multicolor_icons']);

        $brandColors = [
            'Facebook'  => '#1877F2',
            'Twitter'   => '#1DA1F2',
            'Instagram' => '#E4405F',
            'LinkedIn'  => '#0A66C2',
            'YouTube'   => '#FF0000',
            'Pinterest' => '#BD081C',
            'TikTok'    => '#000000',
            'WhatsApp'  => '#25D366',
            'Telegram'  => '#0088CC',
            'Snapchat'  => '#FFFC00',
            'Reddit'    => '#FF4500',
            'Discord'   => '#5865F2',
            'Github'    => '#181717',
            'Behance'   => '#1769FF',
            'Dribbble'  => '#EA4C89',
            'Website'   => '#444444',
            'Email'     => '#EA4335',
            'Phone'     => '#34A853',
        ];

        $css = "#{$uniqueId} .social-icon-link { transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; width: 48px; height: 48px; }";

        switch ($style) {
            case 'inline_name':
                $css .= "#{$uniqueId} .social-icon-link { width: auto; padding: 0 20px; border-radius: 50px; background: var(--bs-light); color: var(--bs-dark); }";
                $css .= "#{$uniqueId} .social-icon-name { margin-left: 10px; font-weight: 500; font-size: 0.95rem; }";
                $css .= "#{$uniqueId} .social-icon-link:hover { background: {$accentColor}; color: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }";
                break;

            case 'bottom_name':
                $css .= "#{$uniqueId} .social-icon-link { flex-direction: column; width: 90px; height: auto; padding: 15px 5px; border-radius: 12px; background: var(--bs-light); color: var(--bs-dark); }";
                $css .= "#{$uniqueId} .social-icon-name { margin-top: 8px; font-weight: 500; font-size: 0.85rem; text-align: center; line-height: 1.2; }";
                $css .= "#{$uniqueId} .social-icon-link:hover { background: {$accentColor}; color: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }";
                break;

            case 'square':
                $css .= "#{$uniqueId} .social-icon-link { border-radius: 8px; background: var(--bs-light); color: var(--bs-dark); }";
                $css .= "#{$uniqueId} .social-icon-link:hover { background: {$accentColor}; color: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }";
                break;

            case 'outline':
                $css .= "#{$uniqueId} .social-icon-link { border-radius: 50%; border: 2px solid var(--bs-gray-300); background: transparent; color: var(--bs-secondary); }";
                $css .= "#{$uniqueId} .social-icon-link:hover { border-color: {$accentColor}; background: {$accentColor}; color: #fff; transform: translateY(-3px); }";
                break;

            case 'glass':
                $css .= "#{$uniqueId} .social-icon-link { border-radius: 50%; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); color: var(--bs-dark); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }";
                $css .= "#{$uniqueId} .social-icon-link:hover { background: rgba(255,255,255,0.9); transform: scale(1.02); color: {$accentColor}; }";
                break;

            case 'minimal':
                $css .= "#{$uniqueId} .social-icon-link { width: auto; height: auto; padding: 0 10px; background: transparent; color: var(--bs-secondary); }";
                $css .= "#{$uniqueId} .social-icon-link:hover { color: {$accentColor}; transform: translateY(-3px); }";
                break;

            case 'default':
            default:
                $css .= "#{$uniqueId} .social-icon-link { border-radius: 50%; background: var(--bs-light); color: var(--bs-dark); }";
                $css .= "#{$uniqueId} .social-icon-link:hover { background: {$accentColor}; color: #fff; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }";
                break;
        }

        if ($multicolor) {
            foreach ($brandColors as $name => $color) {
                $slug = Str::slug($name);
                $selector = "#{$uniqueId} .brand-{$slug}";

                if ($style === 'default' || $style === 'square' || $style === 'glass' || $style === 'inline_name' || $style === 'bottom_name') {
                    // Background color for filled styles
                    $css .= "{$selector} { background: {$color} !important; color: #fff !important; }";
                    $css .= "{$selector}:hover { filter: brightness(1.1); box-shadow: 0 5px 15px " . hex2rgba($color, 0.3) . " !important; }";
                } else {
                    // Text/Border color for outline/minimal
                    $css .= "{$selector} { color: {$color} !important; }";
                    if ($style === 'outline') $css .= "{$selector} { border-color: {$color} !important; }";

                    $css .= "{$selector}:hover { background: {$color} !important; color: #fff !important; }";
                }
            }
        }

        return minifyCss($css);
    }

    /**
     * Prepare FAQs block data
     */
    protected function prepareFaqsData(string $uniqueId, array $options): array
    {
        $items = $options['content'] ?? [];
        if (!is_array($items)) $items = [];

        $faqs = collect($items)->map(fn($item) => (object)(array)$item);

        $colClass = $options['block_style'] ?? '12';
        $blockAlignment = $options['block_alignment'] ?? 'left';
        $faqIcon = $options['faq_icon'] ?? 'plus_minus';
        $faqIconPosition = $options['faq_icon_position'] ?? 'left';
        $faqBtnStyle = $options['faq_btn_style'] ?? 'icon_only';
        $faqItemStyle = $options['faq_item_style'] ?? 'default';
        $collapseFirst = $options['faq_collapse_first'] ?? false;

        // Item style classes
        $itemClass = match ($faqItemStyle) {
            'rounded' => 'rounded-3',
            'pill'    => 'rounded-pill',
            default   => '',
        };

        // Button icon style class
        $btnClass = match ($faqBtnStyle) {
            'bg_rounded' => 'faq-btn-bg-rounded',
            'circle'       => 'faq-btn-circle',
            default      => '',
        };

        return [
            'faqs'          => $faqs,
            'faqIcon'       => $faqIcon,
            'colClass'      => $colClass,
            'blockAlignment' => $blockAlignment,
            'itemClass'     => $itemClass,
            'btnClass'      => $btnClass,
            'iconPosition'  => $faqIconPosition,
            'collapseFirst' => $collapseFirst,
        ];
    }

    /**
     * Prepare Testimonials block data
     */
    protected function prepareTestimonialsData(string $uniqueId, array $options): array
    {
        $items = $options['content'] ?? [];
        if (!is_array($items)) $items = [];

        $appUrl = rtrim(config('app.url'), '/');

        $testimonials = [];
        foreach ($items as $item) {
            $item = (array)$item;
            $image = $item['image'] ?? '';
            if (!empty($image) && str_starts_with($image, $appUrl)) {
                $image = ltrim(substr($image, strlen($appUrl)), '/');
            }
            if (!empty($image) && preg_match('#^https?://.+?/(.+)$#', $image, $m)) {
                $image = $m[1];
            }

            $tImage = $item['testimonial_image'] ?? '';
            if (!empty($tImage) && str_starts_with($tImage, $appUrl)) {
                $tImage = ltrim(substr($tImage, strlen($appUrl)), '/');
            }
            if (!empty($tImage) && preg_match('#^https?://.+?/(.+)$#', $tImage, $m)) {
                $tImage = $m[1];
            }

            $testimonials[] = (object)[
                'name'              => $item['name'] ?? '',
                'designation'       => $item['designation'] ?? '',
                'comment'           => $item['comment'] ?? '',
                'rating'            => $item['rating'] ?? 5,
                'image'             => !empty($image) ? $image : 'images/placeholders/user.png',
                'show_image'        => !empty($item['show_image']),
                'testimonial_image' => !empty($tImage) ? $tImage : '',
            ];
        }

        $testimonials = collect($testimonials);
        $blockAlignment = $options['block_alignment'] ?? 'left';

        return [
            'testimonials'      => $testimonials,
            'blockAlignment'    => $blockAlignment,
            'blockStyle'        => $options['block_style'] ?? 'swiper',
            'disableBg'         => !empty($options['testimonial_disable_bg']),
            'disableAutoplay'   => !empty($options['testimonial_disable_autoplay']),
        ];
    }

    /**
     * Build FAQs scoped CSS
     */
    protected function buildFaqsStyle(string $uniqueId, array $options): string
    {
        $css = '';

        // Item background
        $itemBg = $options['faq_item_bg'] ?? null;
        if ($itemBg) {
            $css .= "#{$uniqueId} .accordion-item{background-color:{$itemBg} !important}";
            $css .= "#{$uniqueId} .accordion-button{background-color:{$itemBg} !important}";
        }

        // Item text color
        $itemColor = $options['faq_item_color'] ?? null;
        if ($itemColor) {
            $css .= "#{$uniqueId} .accordion-item,#{$uniqueId} .accordion-button,#{$uniqueId} .accordion-button .accordion-button-icon,#{$uniqueId} .accordion-body{color:{$itemColor} !important}";
            $css .= "#{$uniqueId} .accordion-body {opacity: .90}";
        }

        // Title font size
        $titleSize = $options['faq_title_size'] ?? null;
        if ($titleSize) {
            $css .= "#{$uniqueId} .accordion-button{font-size:{$titleSize}px !important}";
        }

        // Title text transform
        $titleTransform = $options['faq_title_transform'] ?? 'default';
        if ($titleTransform === 'uppercase') {
            $css .= "#{$uniqueId} .accordion-button{text-transform:uppercase !important}";
        } elseif ($titleTransform === 'lowercase') {
            $css .= "#{$uniqueId} .accordion-button{text-transform:lowercase !important}";
        } elseif ($titleTransform === 'capitalize') {
            $css .= "#{$uniqueId} .accordion-button{text-transform:capitalize !important}";
        }

        // Item border-radius
        $itemStyle = $options['faq_item_style'] ?? 'default';
        if ($itemStyle === 'rounded') {
            $css .= "#{$uniqueId} .accordion-item{border-radius:.5rem !important;overflow:hidden}";
        } elseif ($itemStyle === 'pill') {
            $css .= "#{$uniqueId} .accordion-item{border-radius:50rem !important;overflow:hidden}";
            $css .= "#{$uniqueId} .accordion-button:not(.collapsed){padding-left:1.5rem !important;padding-right:1.5rem !important}";
        } else {
            $css .= "#{$uniqueId} .accordion-item{background-color:transparent !important;}";
            $css .= "#{$uniqueId} .accordion-button{background-color:transparent !important;padding:0 !important}";
        }

        // Toggle button style
        $btnStyle = $options['faq_btn_style'] ?? 'icon_only';
        if ($btnStyle === 'bg_rounded') {
            $css .= "#{$uniqueId} .accordion-button-icon{background:rgba(0,0,0,.06);border-radius:.5rem;width:36px;height:36px;display:flex;align-items:center;justify-content:center}";
        } elseif ($btnStyle === 'circle') {
            $css .= "#{$uniqueId} .accordion-button-icon{background:rgba(0,0,0,.06);border-radius:50rem;width:36px;height:36px;display:flex;align-items:center;justify-content:center}";
        } else {
            $css .= "#{$uniqueId} .accordion-button-icon{background:transparent;border-radius:0;width:auto;height:auto;display:flex;align-items:center;justify-content:center}";
        }

        // Chevron icon rotation
        $faqIcon = $options['faq_icon'] ?? 'plus_minus';
        if ($faqIcon === 'chevron') {
            $css .= "#{$uniqueId} .accordion-button-icon i{display:block !important;transition:transform .3s ease}";
            $css .= "#{$uniqueId} .accordion-button:not(.collapsed) .accordion-button-icon i{transform:rotate(180deg);display:block !important}";
        }

        // Icon position
        $iconPos = $options['faq_icon_position'] ?? 'left';
        if ($iconPos === 'right') {
            $css .= "#{$uniqueId} .accordion-button .accordion-button-icon{order:1;margin-right:0;margin-left:auto}";
        }

        return minifyCss($css);
    }

    /**
     * Prepare Widget block data
     */
    protected function prepareWidgetData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'containerClass' => $options['container_class'] ?? 'container container-default',
        ];
    }

    /**
     * Prepare Advertisement block data
     */
    protected function prepareAdvertisementData(string $uniqueId, array $options): array
    {
        return [
            'adAlias' => $options['ad_alias'] ?? '',
            'uniqueId' => $uniqueId,
            'containerClass' => $options['container_class'] ?? 'container container-default',
        ];
    }

    /**
     * Prepare Premium Plans block data
     */
    protected function preparePremiumPlansData(string $uniqueId, array $options): array
    {
        $weeklyPlans   = PremiumPlan::weekly()->active()->get();
        $monthlyPlans  = PremiumPlan::monthly()->active()->get();
        $yearlyPlans   = PremiumPlan::yearly()->active()->get();
        $lifetimePlans = PremiumPlan::lifetime()->active()->get();

        $allTabs = [
            ['premiumPlans' => $weeklyPlans,   'id' => 'week',     'label' => translate('Weekly')],
            ['premiumPlans' => $monthlyPlans,  'id' => 'month',    'label' => translate('Monthly')],
            ['premiumPlans' => $yearlyPlans,   'id' => 'year',     'label' => translate('Yearly')],
            ['premiumPlans' => $lifetimePlans, 'id' => 'lifetime', 'label' => translate('Lifetime')],
        ];

        $availableTabs = collect($allTabs)->filter(fn($tab) => $tab['premiumPlans']->count() > 0)->values();

        return [
            'premiumTabs'       => $availableTabs,
            'premiumShowSwitcher' => $availableTabs->count() > 1,
            'premiumPlansId'    => $uniqueId,
            'blockStyle'        => $options['block_style'] ?? 'default',
            'containerClass'    => $options['container_class'] ?? 'container container-default',
            'buttonText'        => $options['button_text'] ?? translate('Start Now'),
            'buttonPosition'    => $options['button_position'] ?? 'before_features',
        ];
    }

    /**
     * Build Premium Plans CSS styles based on block_style option
     */
    protected function buildPremiumPlansStyle(string $uniqueId, array $options): string
    {
        $style = $options['block_style'] ?? 'default';
        $accentColor = $options['accent_color'] ?? 'var(--primary_color)';
        $align = $options['block_alignment'] ?? 'center';
        $featureMargin = $options['button_position'] == 'before_features' ? 'margin-top: 24px;' : 'margin-bottom: 16px;';

        // --- BASE STYLES ---
        $css = "
        #{$uniqueId} .plan {
            position: relative;
            background: #fff;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: {$align};
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1;
        }
        #{$uniqueId} .plan-pro {
            position: absolute;
            top: 20px;
            right: -35px;
            background: {$accentColor};
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 40px;
            transform: rotate(45deg);
            z-index: 2;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #{$uniqueId} .plan-title { font-size: 22px; font-weight: 600; margin-bottom: 12px; color: var(--bs-dark); }
        #{$uniqueId} .plan-price { font-size: 32px; font-weight: 800; color: #000; margin-bottom: 12px; }
        #{$uniqueId} .plan-text { font-size: 15px; color: var(--bs-secondary); margin-bottom: 25px; line-height: 1.6; }
        #{$uniqueId} .plan-features { text-align: left; flex-grow: 1; }
        #{$uniqueId} .plan-feat { display: flex; align-items: flex-start; margin-bottom: 12px; font-size: 15px; color: var(--bs-gray-800); }
        #{$uniqueId} .plan-feat-icon { flex-shrink: 0; width: 24px; height: 24px; background: rgba(0,0,0,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; color: var(--bs-dark); font-size: 11px; }
        #{$uniqueId} .plan form { margin-top: auto; }
        #{$uniqueId} .nav-pills { max-width: fit-content; width: 100%; }
        #{$uniqueId} .nav-pills .nav-link { padding: 5px 12px; color: var(--bs-gray-700); }
        #{$uniqueId} .nav-pills .nav-link.active { background: {$accentColor}; color: #fff; }
        ";

        switch ($style) {
            case 'glass':
                $css .= "#{$uniqueId} .plan { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 40px rgba(0,0,0,0.06); }";
                $css .= "#{$uniqueId} .plan:hover { transform: translateY(-10px); background: rgba(255, 255, 255, 0.6); box-shadow: 0 15px 50px rgba(0,0,0,0.1); border-color: rgba(255,255,255,1); }";
                /*$css .= "#{$uniqueId} .plan-feat-icon { background: rgba(255,255,255,0.5); }";*/
                $css .= "#{$uniqueId} .plan.featured-plan { background: {$accentColor}; background: linear-gradient(180deg, rgba(119, 91, 245, 0.15) 0%, transparent 100%); border: none; }";
                break;

            case 'bordered':
                $css .= "#{$uniqueId} .plan { background: transparent; border: 1px solid var(--bs-gray-200); box-shadow: none; }";
                $css .= "#{$uniqueId} .plan.featured-plan { border-color: {$accentColor}; }";
                $css .= "#{$uniqueId} .plan:hover { border-color: {$accentColor}; transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.08); background: #fff; }";
                $css .= "#{$uniqueId} .plan-feat-icon { background: var(--bs-light); }";
                break;

            case 'vibrant':
                $css .= "#{$uniqueId} .plan:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }";
                $css .= "#{$uniqueId} .plan.featured-plan { background: {$accentColor}; background: linear-gradient(135deg, {$accentColor} 0%, var(--bs-primary, #000) 100%); border: none; transform: scale(1.03); }";
                $css .= "#{$uniqueId} .plan.featured-plan:hover { transform: scale(1.03) translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0, 0.2); }";
                $css .= "#{$uniqueId} .plan.featured-plan .plan-title, #{$uniqueId} .plan.featured-plan .plan-price { color: #fff; }";
                $css .= "#{$uniqueId} .plan.featured-plan .plan-text, #{$uniqueId} .plan.featured-plan .plan-feat { color: rgba(255,255,255,0.9); }";
                $css .= "#{$uniqueId} .plan.featured-plan .plan-feat-icon { background: rgba(255,255,255,0.2); color: #fff; }";
                $css .= "#{$uniqueId} .plan.featured-plan .plan-pro { background: var(--bs-gray-200); color: #000; }";
                $css .= "#{$uniqueId} .plan.featured-plan .btn-primary { background: #fff !important; color: {$accentColor} !important; border-color: #fff !important; }";
                $css .= "#{$uniqueId} .plan.featured-plan .btn-primary:hover { background: var(--bs-light) !important; color: var(--bs-dark) !important; border-color: var(--bs-light) !important; }";
                break;

            case 'default':
            default:
                $css .= "#{$uniqueId} .plan:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }";
                break;
        }

        return minifyCss($css);
    }

    /**
     * Prepare Login Form block data
     */
    protected function prepareLoginFormData(string $uniqueId, array $options): array
    {
        return [
            'blockAlign'  => $options['block_alignment'] ?? 'center',
            'blockStyle'  => $options['block_style'] ?? 'default',
            'formWidth'   => $options['form_width'] ?? 'col-12',
            'formShadow'  => $options['form_shadow'] ?? 'shadow-sm',
            'bgColor'     => $options['bg_color'] ?? '#ffffff',
            'textColor'   => $options['text_color'] ?? '#333333',
            'btnStyle'    => $options['lf_btn_style'] ?? 'primary',
            'btnIcon'     => $options['lf_btn_icon'] ?? '',
        ];
    }

    /**
     * Build Login Form scoped CSS
     */
    protected function buildLoginFormStyle(string $uniqueId, array $options): string
    {
        $css = '';
        $bgColor = $options['bg_color'] ?? '#ffffff';
        $textColor = $options['text_color'] ?? '#333333';

        // Apply styles to the form container
        $css .= "#{$uniqueId} .login-form-container { background-color: {$bgColor}; color: {$textColor} }";
        $css .= "#{$uniqueId} .login-form-container .form-control { border-color: rgba(0,0,0,0.1) }";

        return minifyCss($css);
    }

    /**
     * Prepare data options for generic Product Blocks
     */
    protected function prepareProductBlockData(string $uniqueId, array $options): array
    {
        $productType = $options['product_type'] ?? 'latest';
        $products = $this->homePageService->getProductsByType($productType, $options);
        $categories = $this->homePageService->getCategoriesWithProducts($options);

        // Detect active category from URL pagination params (e.g. cat_themes_page=2)
        $activeCategorySlug = null;
        foreach ($categories as $cat) {
            $pageName = "cat_{$cat->slug}_page";
            if (request()->has($pageName) && request()->get($pageName) > 1) {
                $activeCategorySlug = $cat->slug;
                break;
            }
        }

        $isList = ($options['block_style'] ?? 'grid') === 'list';
        $isMixed = ($options['block_style'] ?? 'grid') === 'mixed';
        $gridClass = $isMixed
            ? ''
            : ($isList ? 'row-cols-1 row-cols-xl-2' : 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4');

        return [
            'uniqueId'       => $uniqueId,
            'containerClass' => $options['container_class'] ?? 'container container-default',
            'blockAlignment' => $options['block_alignment'] ?? 'start',
            'blockStyle'     => $options['block_style'] ?? 'grid',
            'gridClass'      => $gridClass,
            'product_type'   => $productType,
            'products'       => $products,
            'categories'     => $categories,
            'activeCategorySlug' => $activeCategorySlug,
            'show_category'  => !empty($options['show_category']),
            'seller_avatar'  => !empty($options['seller_avatar']),
            'seller_name'    => !empty($options['seller_name']),
            'total_sales'    => !empty($options['total_sales']),
            'total_reviews'  => !empty($options['total_reviews']),
            'post_date' => !empty($options['post_date']),
            'cart_btn' => !empty($options['cart_btn']),
            'live_preview_btn' => !empty($options['live_preview_btn']),
            'action_button_style' => $options['action_button_style'] ?? 'default',
            'preview_button_style' => $options['preview_button_style'] ?? 'primary',
            'cart_button_style' => $options['cart_button_style'] ?? 'outline-primary',
            'favorite_btn' => !empty($options['favorite_btn']),
            'product_badge' => !empty($options['product_badge']),
            'total_downloads' => !empty($options['total_downloads']),
            'download_btn' => !empty($options['download_btn']),
            'pause_on_hover' => !empty($options['pause_on_hover']),
            'show_navigation' => $options['show_navigation'] ?? true,
            'show_pagination' => $options['show_pagination'] ?? true,
            'pagination_style' => $options['pagination_style'] ?? 'none',
            'product_meta_style' => $options['product_meta_style'] ?? 'default',
            'products_number' => max(1, (int)($options['products_number'] ?? 8)),
            'products_title_length' => max(1, (int)($options['products_title_length'] ?? 45)),
            'pagi_btn_style' => $options['pagi_btn_style'] ?? 'outline-primary',
            'pagi_btn_icon' => $options['pagi_btn_icon'] ?? '',
        ];
    }

    /**
     * Build Products style CSS
     *
     * @param string $uniqueBlockId
     * @param array $options
     * @return string
     */
    protected function buildProductStyle(string $uniqueBlockId, array $options): string
    {
        $style = $options['block_style'] ?? 'grid';
        $bodyStyle = $options['product_body_style'] ?? 'shadow';
        $productSelector = "#{$uniqueBlockId} .product";
        $css = '';

        if ($style === 'background') {
            $css .= "
                {$productSelector} {
                    background: rgba(255, 255, 255, 0.4);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.5);
                    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
                    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
                }
                #{$uniqueBlockId} .products-wrapper {
                   background: var(--bs-gray-100);
                   padding: 1rem;
                   border-radius: 12px;
                }
            ";
        } elseif ($style === 'list') {
            $css .= "
                @media (min-width: 768px) {
                    {$productSelector} {
                        display: flex;
                        flex-direction: row;
                        align-items: stretch;
                        height: 100%;
                    }
                    {$productSelector} .product-header {
                        width: 40%;
                        flex-shrink: 0;
                        margin-bottom: 0;
                    }
                    {$productSelector} .product-img-holder {
                        height: 100%;
                        border-radius: 12px 0 0 12px;
                    }
                    {$productSelector} .product-img-holder img,
                    {$productSelector} .product-video,
                    {$productSelector} .product-video video,
                    {$productSelector} .product-audio {
                        height: 160px !important;
                        object-fit: cover;
                        border-radius: 12px 0 0 12px;
                    }
                    {$productSelector} .product-body {
                        width: 60%;
                        height: 160px !important;
                        padding-left: 1.25rem;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        border-radius: 0 12px 12px 0;
                    }
                }
            ";
        } elseif ($style === 'overlay') {
            $css .= "
                {$productSelector} {
                    position: relative;
                    overflow: hidden;
                    border: none;
                }
                {$productSelector} .product-header {
                    margin-bottom: 0;
                    border-radius: 12px;
                    overflow: hidden;
                }
                {$productSelector} .product-body {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    padding: 1.5rem;
                    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 50%, transparent 100%);
                    color: white;
                    transform: translateY(10px);
                    transition: transform 0.3s ease;
                    border-radius: 0 0 12px 12px;
                    z-index: 2;
                }
                {$productSelector} .product-audio-wave  {
                    align-items: start;
                }
                {$productSelector}:hover .product-body {
                    transform: translateY(0);
                }
                {$productSelector} .product-title,
                {$productSelector} .product-price-number {
                    color: #fff !important;
                }
                {$productSelector} .product-meta a,
                {$productSelector} .product-price-through {
                    color: rgba(255,255,255,0.8) !important;
                }
            ";
        } elseif ($style === 'mixed') {
            $css .= "
                /* Force perfectly clean CSS grid, dropping bootstrap flex hacks */
                #{$uniqueBlockId} .row.g-4 {
                    display: grid !important;
                    grid-template-columns: 1fr;
                    gap: var(--bs-gutter-y, 1.5rem) var(--bs-gutter-x, 1.5rem);
                    margin: 0 !important;
                }
                #{$uniqueBlockId} .row.g-4 > .col {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    flex: none !important;
                }

                @media (min-width: 576px) {
                    #{$uniqueBlockId} .row.g-4 { grid-template-columns: repeat(2, 1fr); }
                }

                @media (min-width: 768px) and (max-width: 991px) {
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 3),
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 2) {
                        grid-column: span 1;
                        grid-row: span 1;
                    }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1),
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) {
                        grid-column: span 2;
                        grid-row: span 1;
                    }
                }

                @media (min-width: 992px) {
                    #{$uniqueBlockId} .row.g-4 { grid-template-columns: repeat(4, 1fr); }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 3),
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 2) {
                        grid-column: span 1;
                        grid-row: span 2;
                    }

                    /* 3rd and 4th items: Span 2 cols, 1 row (Wide List Cards) */
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1),
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) {
                        grid-column: span 2;
                        grid-row: span 1;
                    }
                }

                /* Transform the 3rd and 4th items to horizontal list cards on Tablet+ */
                @media (min-width: 768px) {
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product {
                        display: flex !important;
                        flex-direction: row !important;
                        align-items: center !important;
                        height: 100% !important;
                        text-align: left !important;
                        padding: 0 !important;
                    }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product .product-header,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product .product-header {
                        width: 45% !important;
                        flex: 0 0 45% !important;
                        margin-bottom: 0 !important;
                        position: relative !important;
                        align-self: stretch !important;
                        min-height: 180px !important;
                        border-radius: var(--bs-border-radius) 0 0 var(--bs-border-radius) !important;
                        overflow: hidden !important;
                    }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product .product-img-holder,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product .product-img-holder {
                        height: 100% !important;
                        width: 100% !important;
                        position: absolute !important;
                        top: 0; left: 0; right: 0; bottom: 0;
                        margin-bottom: 0 !important;
                        border-radius: 0.75rem 0 0 0.75rem !important;
                    }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product .product-img-holder img,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product .product-img-holder img {
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        object-fit: cover !important;
                        border-radius: 0.75rem 0 0 0.75rem !important;
                    }
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product .product-video video,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product .product-video video,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n - 1) .product .product-audio,
                    #{$uniqueBlockId} .row.g-4 > .col:nth-child(4n) .product .product-audio {
                        min-height: 180px !important;
                        border-radius: 0.75rem 0 0 0.75rem !important;
                        align-self: stretch !important;
                    }
                    #{$uniqueBlockId} .col:nth-child(4n - 1) .product .product-body,
                    #{$uniqueBlockId} .col:nth-child(4n) .product .product-body {
                        width: 55% !important;
                        flex: 0 0 55% !important;
                        display: flex !important;
                        flex-direction: column !important;
                        justify-content: center !important;
                        align-self: stretch !important;
                        padding: 1rem 1rem 1rem 1.5rem !important;
                        border-radius: 0 0.75rem 0.75rem 0 !important;
                    }
                }
            ";
        } elseif ($style === 'split') {
            $css .= "
                {$productSelector} {
                    background: transparent;
                    border: none;
                    box-shadow: none;
                }
                {$productSelector} .product-header {
                    margin-bottom: 0;
                }
                {$productSelector} .product-img-holder,
                {$productSelector} .product-video video,
                {$productSelector} .product-audio {
                    border-radius: 12px !important;
                }
                {$productSelector} .product-body {
                    background: #fff;
                    padding: 1rem;
                    border-radius: 12px;
                    width: 90%;
                    margin: 0 auto;
                    margin-top: -3rem;
                    position: relative;
                    z-index: 2;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
            ";
        }

        if ($bodyStyle === 'outline') {
            $css .= "
                {$productSelector} {
                    background: transparent;
                    border: {($style === 'split') ? 'none' : 1px solid var(--bs-border-color)};
                    box-shadow: none;
                    transition: all 0.3s ease;
                }
                {$productSelector}:hover {
                    border-color: var(--primary_color);
                }
                {if ($style === 'split')}
                {$productSelector} .product-body {
                    border: 1px solid var(--bs-border-color);
                }
                {$productSelector}:hover .product-body{
                    border-color: var(--primary_color);
                }
                {endif}
            ";
        } elseif ($bodyStyle === 'shadow') {
            $css .= "
                {$productSelector} .product-body {
                    background: #fff;
                    border: none;
                    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
                    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
                }
                {$productSelector}:hover{
                    box-shadow: 0 15px 35px rgba(31, 38, 135, 0.1);
                    transform: translateY(-5px);
                }
            ";
        } elseif ($bodyStyle === 'bg_light') {
            $css .= "
                {$productSelector} .product-body{
                    background: var(--bs-gray-100);
                    border: none;
                    box-shadow: none;
                }
            ";
        } elseif ($bodyStyle === 'bg_green') {
            $css .= "
                {$productSelector} .product-body{
                    background: var(--bs-success-bg-subtle);
                    border: none;
                    box-shadow: none;
                }
            ";
        } elseif ($bodyStyle === 'bg_purple') {
            $css .= "
                {$productSelector} .product-body {
                    background: #e6e6faff;
                    border: none;
                    box-shadow: none;
                }
            ";
        } else {
            $css .= "
                {$productSelector} .product-body{
                    background: transparent;
                    border: none;
                    box-shadow: none;
                    padding: 0;
                    transition: transform 0.3s ease;
                }
                {$productSelector}:hover {
                    box-shadow: none;
                    transform: translateY(-4px);
                }
                {$productSelector} .product-img-holder,
                {$productSelector} .product-video video,
                {$productSelector} .product-audio {
                    border-radius: 12px;
                }
                {$productSelector} .product-body {
                    padding: 10px 2px 2px 2px;
                }
            ";
        }

        return minifyCss($css);
    }

    /**
     * Prepare data options for Blog Articles block
     */
    protected function prepareBlogArticlesData(string $uniqueId, array $options): array
    {
        $blockStyle = $options['block_style'] ?? 'grid';

        $gridClass = match ($blockStyle) {
            'list'  => 'row-cols-1 row-cols-lg-2',
            default => 'row-cols-1 row-cols-md-2 row-cols-lg-3',
        };

        $postClass = match ($blockStyle) {
            'list'  => 'blog-post-list',
            'split' => 'blog-post-split',
            default => 'blog-post-grid h-100',
        };

        return [
            'uniqueId'        => $uniqueId,
            'containerClass'  => $options['container_class'] ?? 'container container-default',
            'alingment'       => $options['block_alignment'] ?? 'start',
            'block_style'     => $blockStyle,
            'gridClass'       => $gridClass,
            'postClass'       => $postClass,
            'show_category'   => !isset($options['show_category'])   ? true : !empty($options['show_category']),
            'post_date'       => !isset($options['post_date'])       ? true : !empty($options['post_date']),
            'readmore_btn'    => !isset($options['readmore_btn'])    ? true : !empty($options['readmore_btn']),
            'author_name'     => !isset($options['author_name'])     ? true : !empty($options['author_name']),
            'blog_number'     => max(1, (int)($options['blog_number'] ?? 4)),
        ];
    }

    /**
     * Prepare Blog Categories block data
     */
    protected function prepareBlogCategoriesData(string $uniqueId, array $options): array
    {
        $blockStyle = $options['block_style'] ?? 'style1';

        $styleClass = ($blockStyle === 'style1')
            ? 'shadow-sm border-0 hover-lift'
            : 'shadow-sm border-1 border-primary bg-primary-light';

        return [
            'uniqueId' => $uniqueId,
            'containerClass' => $options['container_class'] ?? 'container container-default',
            'blogCategories' => BlogCategory::withCount('articles')->get(),
            'alignment' => $options['block_alignment'] ?? 'center',
            'styleClass' => $styleClass,
        ];
    }

    /**
     * Prepare Featured Seller block data
     */
    protected function prepareFeaturedSellerData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'containerClass' => $options['container_class'] ?? 'container container-default',
            'featuredSellerBlock' => $this->homePageService->getFeaturedSellerBlock(),
            'featuredSeller'      => $this->homePageService->getFeaturedSeller(),
            'bgStyle' => $options['featured_products_bg_style'] ?? '',
        ];
    }
}
