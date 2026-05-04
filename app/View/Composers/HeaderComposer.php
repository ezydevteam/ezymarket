<?php

namespace App\View\Composers;

use App\Enums\Menu\MenuLocation;
use App\Classes\{BuilderBlocks, GoogleFonts};
use App\Models\Appearance\Menu;
use App\Models\CartProduct;
use App\Models\Favorite;
use App\Models\Product\ProductCategory;
use App\Models\Settings;
use App\Models\User;
use Illuminate\View\View;

/**
 * HeaderComposer
 *
 * Prepares all data needed for the header/navbar layout view,
 * keeping the Blade template clean and focused on presentation.
 */
class HeaderComposer
{
    /**
     * Static cache for computed data to avoid redundant processing
     * when composer is attached to multiple views in the same request.
     */
    protected static array $cache = [];

    /**
     * Section definitions with defaults
     */
    protected array $sectionDefs = [
        'main_header' => [
            'class' => 'header-top d-none d-lg-block',
            'defaultBg' => '#f8f9fa',
            'defaultText' => '#6c757d',
            'border' => 'border-bottom'
        ],
        'bottom_header' => [
            'class' => 'header-main d-none d-lg-block',
            'defaultBg' => '#ffffff',
            'defaultText' => '#212529',
            'border' => 'border-bottom shadow-sm'
        ],
        'mobile_header_top' => [
            'class' => 'header-mobile-top d-lg-none',
            'defaultBg' => '#ffffff',
            'defaultText' => '#212529',
            'border' => 'border-bottom shadow-sm'
        ],
        'mobile_header_bottom' => [
            'class' => 'header-mobile-bottom d-lg-none',
            'defaultBg' => '#ffffff',
            'defaultText' => '#212529',
            'border' => 'border-top shadow-sm'
        ]
    ];

    /**
     * Default header layout configuration
     */
    protected array $defaultLayout = [
        [
            'id' => 'main_header',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 3, 'blocks' => [['id' => 'logo', 'title' => 'Logo', 'options' => []]]],
                ['width' => 6, 'blocks' => [['id' => 'search', 'title' => 'Search', 'options' => []]]],
                ['width' => 3, 'blocks' => [['id' => 'auth', 'title' => 'Auth', 'options' => []], ['id' => 'cart', 'title' => 'Cart', 'options' => []]]]
            ]
        ],
        [
            'id' => 'bottom_header',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 9, 'blocks' => []],
                ['width' => 3, 'blocks' => [['id' => 'menu', 'title' => 'Menu', 'options' => []]]]
            ]
        ],
        [
            'id' => 'mobile_header_top',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 3, 'blocks' => [['id' => 'logo', 'title' => 'Logo', 'options' => []]]],
                ['width' => 9, 'blocks' => [['id' => 'search', 'title' => 'Search', 'options' => []]]]
            ]
        ],
        [
            'id' => 'mobile_header_bottom',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 12, 'blocks' => []]
            ]
        ]
    ];

    /**
     * Bind data to the view.
     * This method is called by the framework when the view is rendered.
     * It prepares all necessary data for the header layout, including sections, menus, and fonts, and caches it for performance.
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        if (empty(self::$cache)) {
            $headerLayout = $this->getHeaderLayout();

            self::$cache = [
                'headerSections' => $this->buildSections($headerLayout),
                'headerFontsLink' => $this->getGoogleFontsLink($headerLayout),
                'topNavMenus' => $this->getTopNavMenus(),
                'bottomNavMenus' => $this->getBottomNavMenus(),
                'mobileMenus' => $this->getMobileMenus(),
                'footerMenus' => $this->getFooterMenus(),
                'cartProductsCount' => $this->getCartProductsCount(),
                'cartTotal' => $this->getCartTotal(),
                'cartProducts' => $this->getCartProducts(),
                'favoritesProductsCount' => $this->getFavoritesProductsCount(),
                'categories' => ProductCategory::all()
            ];
        }

        $view->with(self::$cache);
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
                if (!empty($section['options']['font_family'])) {
                    $fonts[] = $section['options']['font_family'];
                }
            }
        }

        return GoogleFonts::getLink($fonts);
    }

    /**
     * Get top navigation menus with children
     */
    protected function getTopNavMenus()
    {
        return Menu::getByLocation(MenuLocation::TOP);
    }

    /**
     * Get bottom navigation menus with children
     */
    protected function getBottomNavMenus()
    {
        return Menu::getByLocation(MenuLocation::BOTTOM);
    }

    /**
     * Get mobile menus with children
     */
    protected function getMobileMenus()
    {
        return Menu::getByLocation(MenuLocation::MOBILE);
    }

    /**
     * Get footer menus with children
     */
    protected function getFooterMenus()
    {
        return Menu::getByLocation(MenuLocation::FOOTER);
    }

    /**
     * Get cart products count for current user or session
     */
    protected function getCartProductsCount(): int
    {
        $user = authUser();

        if ($user) {
            $this->migrateSessionCartToUser($user);
            return CartProduct::where('user_id', $user->id)->sum('quantity');
        }

        if (session()->has('session_id')) {
            return CartProduct::where('session_id', session()->get('session_id'))->sum('quantity');
        }

        return 0;
    }

    /**
     * Get cart total amount
     */
    protected function getCartTotal(): float
    {
        $products = $this->getCartProducts();
        return $products->sum(fn($cartProduct) => $cartProduct->getTotalAmountWithSupport());
    }

    /**
     * Get cart products
     */
    protected function getCartProducts()
    {
        $user = authUser();

        if ($user) {
            $this->migrateSessionCartToUser($user);
            return CartProduct::where('user_id', $user->id)
                ->with(['product', 'supportPackage'])
                ->orderByDesc('id')
                ->get();
        }

        if (session()->has('session_id')) {
            return CartProduct::where('session_id', session()->get('session_id'))
                ->with(['product', 'supportPackage'])
                ->orderByDesc('id')
                ->get();
        }

        return collect();
    }

    /**
     * Get favorites products count for current user
     */
    protected function getFavoritesProductsCount(): int
    {
        $user = authUser();
        return $user ? Favorite::where('user_id', $user->id)->count() : 0;
    }

    /**
     * Migrate session cart products to authenticated user
     */
    protected function migrateSessionCartToUser(User $user): void
    {
        if (!session()->has('session_id')) {
            return;
        }

        $sessionId = session()->get('session_id');
        $cartProducts = CartProduct::where('session_id', $sessionId)->get();

        if ($cartProducts->isEmpty()) {
            session()->forget('session_id');
            return;
        }

        foreach ($cartProducts as $cartProduct) {
            // Skip if user already owns this product
            if ($user->products->contains('id', $cartProduct->product_id)) {
                $cartProduct->delete();
                continue;
            }

            // Merge with existing cart item or create new
            $existingCartProduct = CartProduct::where('user_id', $user->id)
                ->where('product_id', $cartProduct->product_id)
                ->first();

            if ($existingCartProduct) {
                $newQuantity = $existingCartProduct->quantity + $cartProduct->quantity;
                if ($newQuantity < 50) {
                    $existingCartProduct->increment('quantity', $cartProduct->quantity);
                }
                $cartProduct->delete();
            } else {
                $cartProduct->update([
                    'session_id' => null,
                    'user_id' => $user->id
                ]);
            }
        }

        session()->forget('session_id');
    }

    /**
     * Get header layout from settings
     */
    protected function getHeaderLayout(): array
    {
        $headerLayoutRaw = Settings::where('key', 'theme_header')->value('value');

        // Always convert to array (handles JSON string, stdClass, or already array)
        if (is_string($headerLayoutRaw)) {
            $headerLayout = json_decode($headerLayoutRaw, true);
        } elseif (is_object($headerLayoutRaw) || is_array($headerLayoutRaw)) {
            $headerLayout = json_decode(json_encode($headerLayoutRaw), true);
        } else {
            $headerLayout = null;
        }

        if (empty($headerLayout) || !is_array($headerLayout)) {
            return $this->defaultLayout;
        }

        return $headerLayout;
    }

    /**
     * Build sections with all computed properties
     */
    protected function buildSections(array $headerLayout): array
    {
        $sections = [];
        $sectionIndex = 0;

        foreach ($this->sectionDefs as $sectionId => $sectionDef) {
            $section = $this->findSection($headerLayout, $sectionId);

            if (!$section) {
                continue;
            }

            // Convert snake_case ID to kebab-case for HTML attributes
            $htmlId = str_replace('_', '-', $sectionId);

            $options = $section['options'] ?? [];
            // Parse boolean using filter_var to handle string "false", "0", etc. correctly
            $isEnabled = filter_var($options['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

            if (!$isEnabled) {
                continue;
            }

            $container = $this->buildContainerClass($options);
            $columns = $section['columns'] ?? [];
            $columnGap = $options['gap_between_columns'] ?? 'gap-3 gap-lg-4';
            $blockGapRaw = $options['gap_between_blocks'] ?? 'gap-3';
            $blockSpaceBetween = ($blockGapRaw === 'space-between');
            $blockGap = $blockSpaceBetween ? 'w-100 justify-content-between gap-2' : $blockGapRaw;

            // Get colors for CSS variables
            $bgColor = $options['bg_color'] ?? $sectionDef['defaultBg'];
            $textColor = $options['text_color'] ?? $sectionDef['defaultText'];
            $linkColor = $options['link_color'] ?? $textColor;

            // Determine if default utility classes should be removed in favor of custom styles
            $hasCustomPadding = !empty($options['padding_top']) || !empty($options['padding_bottom']) || !empty($options['padding_left']) || !empty($options['padding_right']);
            $paddingClass = $hasCustomPadding ? '' : ($options['padding'] ?? 'py-2');

            $hasCustomBorder = ($options['border_style'] ?? 'none') !== 'none';
            $borderClass = $hasCustomBorder ? '' : $sectionDef['border'];

            // Build custom styles string
            $customStyles = $this->buildCustomStyles($options);
            $rowStyles = $this->buildSectionStyles($options);

            // Build attributes string
            $attributes = [];
            if (($options['sticky_header_type'] ?? 'none') !== 'none') {
                $sectionDef['class'] .= ' sticky-header';
                $attributes[] = 'data-sticky-type="' . $options['sticky_header_type'] . '"';
                $attributes[] = 'data-sticky-offset="' . ($options['sticky_offset'] ?? 0) . '"';
                $attributes[] = 'data-sticky-transition="' . ($options['sticky_transition'] ?? 300) . '"';
            }

            if ($sectionId === 'mobile_header_bottom') {
                $attributes[] = 'data-mobile-style="' . ($options['mobile_bottom_style'] ?? 'default') . '"';
            }

            // Build section data
            $sectionData = [
                'id' => $htmlId,
                'class' => $sectionDef['class'],
                'border' => $borderClass,
                'padding' => $paddingClass,
                'container' => $container,
                'columnGap' => $columnGap,
                'blockGap' => $blockGap,
                'bgColor' => $bgColor,
                'textColor' => $textColor,
                'linkColor' => $linkColor,
                'customStyles' => $customStyles,
                'rowStyles' => $rowStyles,
                'attributes' => implode(' ', $attributes),
                'columns' => [],
            ];

            // Calculate flex overrides (If ALL are OFF or ALL are ON, force fill)
            $totalCols = count($columns);
            $activeFlexCols = 0;
            foreach ($columns as $col) {
                if (!empty($col['flexGrow'])) {
                    $activeFlexCols++;
                }
            }
            // If ALL are OFF (0) or ALL are ON (Total), force everyone to flex-grow: 1
            $forceFlexFill = ($activeFlexCols === 0 || $activeFlexCols === $totalCols);

            $columnStyles = '';
            $generatedBlockStyles = '';
            $firstBlockIconColor = null;

            // Build columns with computed properties
            foreach ($columns as $colIndex => $col) {
                // Support both 'blocks' (new) and 'items' (legacy) keys
                $blocks = $col['blocks'] ?? [];

                // Get Flex Grow setting
                $flexGrow = $col['flexGrow'] ?? 0;

                // Apply logic: If forceFlexFill is true, override to 1
                if ($forceFlexFill) {
                    $flexGrow = 1;
                }

                $columnStyles .= "#{$htmlId}-col-{$colIndex} { flex: {$flexGrow} 1 auto; min-width: min-content; } ";

                // Get Alignment setting
                $align = $col['align'] ?? 'start';
                $direction = $col['direction'] ?? 'row';

                $alignClass = match ($align) {
                    'center' => 'justify-content-center text-center',
                    'end', 'right' => 'justify-content-end text-end',
                    default => 'justify-content-start text-start'
                };

                // Override alignment if space-between block gap is selected (only for row direction)
                if ($blockSpaceBetween && ($direction !== 'column' || $direction !== 'column_reverse')) {
                    $alignClass = str_replace(['justify-content-start', 'justify-content-center', 'justify-content-end'], '', $alignClass);
                }

                // Direction Class (Header uses d-flex by default in columns usually, but let's confirm usage in blade)
                // If it's a row direction, we want items side-by-side (default behavior of header cols)
                // If it's a column direction, we want items stacked
                $directionClass = $direction === 'column' ? 'flex-column align-items-' . $align : 'flex-row align-items-center';

                if ($direction === 'column') {
                    // When stacked, justify-content controls vertical alignment (if height exists) or nothing really
                    // but align items controls horizontal alignment

                    $alignClass = match ($align) {
                        'center' => 'align-items-center text-center',
                        'end', 'right' => 'align-items-end text-end',
                        default => 'align-items-start text-start'
                    };
                }

                // Build blocks with resolved block IDs
                $resolvedBlocks = [];
                foreach ($blocks as $block) {
                    $itemId = $block['id'] ?? '';
                    if (!$itemId) {
                        continue;
                    }

                    // Filter out inactive blocks
                    $status = $block['status'] ?? 1;
                    if ((int)$status === 0) {
                        continue;
                    }

                    // Use BuilderBlocks to strip semantic prefixes
                    $cleanId = BuilderBlocks::getViewName($itemId, 'header_');

                    // Convert snake_case ID to kebab-case filename
                    $viewName = str($cleanId)->replace('_', '-');
                    $view = 'blocks.header.' . $viewName;

                    if (!theme_view_exists($view)) {
                        continue;
                    }

                    // Generate Unique Block ID for Styles
                    // Using htmlId ensuring uniqueness: section-col-blockIndex
                    // Note: We use count($resolvedBlocks) which is 0 for first block in this column
                    $uniqueBlockId = $htmlId . '-col-' . $colIndex . '-block-' . count($resolvedBlocks);

                    // Inject ID into options so Blade view can use it
                    if (!isset($block['options'])) {
                        $block['options'] = [];
                    }
                    $block['options']['uniqueId'] = $uniqueBlockId;
                    $block['options']['section_text_color'] = $textColor; // Pass section text color for fallback

                    // Inject Global Block Layout Options
                    $block['options']['spaceBetween'] = $blockSpaceBetween;
                    $block['options']['blockGap'] = $blockGap;

                    // Process block to generate necessary data and styles based on its type
                    $this->processBlock($cleanId, $uniqueBlockId, $block, $generatedBlockStyles);

                    // Calculate visibility class
                    $visibility = $block['options']['visibility'] ?? 'all';
                    $visibilityClass = match ($visibility) {
                        'desktop' => 'd-none d-lg-block',
                        'mobile' => 'd-block d-lg-none',
                        default => ''
                    };

                    // Add Custom CSS Class
                    if (!empty($block['options']['custom_class'])) {
                        $visibilityClass .= ' ' . $block['options']['custom_class'];
                    }

                    // Force expansion for compound blocks when Equal Spacing is enabled
                    // Removed logic that forces w-100 on auth/language_currency to allow flex space-between distribution
                    /*if ($blockSpaceBetween && in_array($cleanId, ['auth', 'language_currency'])) {
                        $visibilityClass .= ' flex-grow-1 w-100';
                    }*/

                    // Capture first block icon color for section default
                    if ($firstBlockIconColor === null && !empty($block['options']['icon_color'])) {
                        $firstBlockIconColor = $block['options']['icon_color'];
                    }

                    $resolvedBlocks[] = [
                        'id' => $itemId,
                        'view' => $view,
                        'options' => $block['options'] ?? [],
                        'iconColor' => $block['options']['icon_color'] ?? '',
                        'wrapperClass' => $visibilityClass
                    ];
                }

                $sectionData['columns'][] = [
                    'flexGrow' => (int) $flexGrow,
                    'alignClass' => $alignClass,
                    'directionClass' => $directionClass,
                    'blocks' => $resolvedBlocks,
                ];
            }

            // Determine section-wide icon color (fallback to text color)
            $sectionIconColor = $firstBlockIconColor ?? $textColor;

            // Header Z-Index Logic (Ensure Proper Stacking Context)
            // Start high (1030) and decrease to ensure top bars overlay bottom bars.
            // Sticky headers typically use 1020 in Bootstrap.
            $calculatedZIndex = 1030 - ($sectionIndex * 5);
            $isSticky = ($options['sticky_header_type'] ?? 'none') !== 'none';

            // We enforce position: relative to ensure z-index applies.
            // If sticky JS activates, it usually applies inline `position: fixed` or `position: sticky` which overrides this.
            $positionStyle = 'position:relative;';
            $zIndexStyle = "z-index:{$calculatedZIndex};";

            // ZIP all styles into one CSS block for the View
            $sectionCss = "#{$htmlId}{--header-text-color:{$textColor};--header-link-color:{$linkColor};--header-icon-color:{$sectionIconColor};background-color:{$bgColor};color:var(--header-text-color);{$customStyles};{$zIndexStyle}{$positionStyle}}#{$htmlId} .header-inner{{$rowStyles}}{$columnStyles}{$generatedBlockStyles}";

            // Mobile Bottom Fix: Ensure it's fixed at bottom if ID matches
            // z-index decreased to 1010 to be lower than Top Header (1030) so dropdowns can overlap it
            if ($sectionId === 'mobile_header_bottom') {
                $mobileStyle = $options['mobile_bottom_style'] ?? 'default';

                if ($mobileStyle === 'modern') {
                    // Modern Floating Pill Style
                    // Updated to fit blocks and max 300px around as requested
                    $sectionCss .= "#{$htmlId}{position:fixed;bottom:15px;left:50%;transform:translateX(-50%);min-width:fit-content;max-width:320px;border-radius:50rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,0.15);z-index:1010;padding-left:0.5rem!important;padding-right:0.5rem!important;backdrop-filter: blur(5px);-webkit-backdrop-filter: blur(5px);}";
                } else {
                    $sectionCss .= "#{$htmlId}{position:fixed;bottom:0;left:0;right:0;z-index:1010;}";
                }
            }

            $sectionData['css'] = minifyCss($sectionCss);
            $sections[] = $sectionData;
            $sectionIndex++;
        }

        return $sections;
    }

    /**
     * Find section by ID in layout
     */
    protected function findSection(array $headerLayout, string $sectionId): ?array
    {
        foreach ($headerLayout as $section) {
            if (($section['id'] ?? '') === $sectionId) {
                return $section;
            }
        }
        return null;
    }

    /**
     * Recursively convert object to array
     */
    protected function objectToArray($data): ?array
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (is_array($data)) {
            return array_map([$this, 'objectToArray'], $data);
        }
        return $data;
    }

    /**
     * Helper to add style with unit
     */
    protected function addStyleUnit(array &$styles, string $property, $value, string $unit = 'px'): void
    {
        if ($value !== null && $value !== '') {
            $styles[] = "{$property}: {$value}{$unit} !important";
        }
    }

    /**
     * Build container class based on width settings
     */
    protected function buildContainerClass(array $options): string
    {
        $widthType = $options['container_width'] ?? 'default';

        if ($widthType === 'full_width') {
            $containerClass = 'container-fluid';
        } elseif ($widthType === 'boxed') {
            $containerClass = 'container container-boxed';
        } else {
            $containerClass = 'container container-default';
        }

        return $containerClass;
    }

    /**
     * Build custom styles string from options
     */
    protected function getBlockHandlers(): array
    {
        return [
            'logo' => [
                'data' => 'prepareLogoData',
                'styles'  => 'buildLogoStyles',
            ],

            'offcanvas' => [
                'data' => 'prepareOffcanvasData',
                'styles'  => 'buildOffcanvasStyles',
            ],

            'auth' => [
                'data' => 'prepareAuthData',
                'styles'  => 'buildAuthStyles',
            ],

            'cart' => [
                'data' => 'prepareCartData',
                'iconStyle' => '.header-cart-icon',
            ],

            'favorites' => [
                'data' => 'prepareFavoritesData',
                'iconStyle' => '.header-favorites-icon',
            ],

            'social' => [
                'data' => 'prepareSocialData',
                'styles'  => 'buildSocialStyles',
            ],

            'language_currency' => [
                'data' => 'prepareLanguageCurrencyData',
                'iconStyle' => '.header-lc-icon',
            ],

            'countdown' => [
                'data' => 'prepareCountdownData',
            ],

            'icon' => [
                'data' => 'prepareIconData',
                'iconStyle' => '.header-custom-icon',
            ],

            'menu' => [
                'data' => 'prepareMenuData',
                'styles' => 'buildMenuStyles',
            ],

            'search' => [
                'data' => 'prepareSearchData',
                'styles'  => 'buildSearchStyles',
            ],

            'theme_toggle' => [
                'data' => 'prepareThemeToggleData',
                'styles'  => 'buildThemeToggleStyles',
            ],

            'divider' => [
                'data' => 'prepareDividerData',
                'styles'  => 'buildDividerStyles',
            ],

            'message' => [
                'data'   => 'prepareMessageData',
                'iconStyle' => '.header-chat-icon',
            ],

            'notification' => [
                'data'   => 'prepareNotificationData',
                'iconStyle' => '.header-notification-icon',
            ],

            'premium' => [
                'data' => 'preparePremiumData',
            ],

            'button' => [
                'data' => 'prepareButtonData',
            ],

            'html' => [
                'data' => 'prepareHtmlData',
            ]
        ];
    }

    /**
     * Process each block based on its type and generate necessary data and styles
     */
    protected function processBlock(string $cleanId, string $uniqueBlockId, array &$block, string &$generatedBlockStyles): void
    {
        $handlers = $this->getBlockHandlers();

        if (!isset($handlers[$cleanId])) {
            return;
        }

        $config = $handlers[$cleanId];

        // 🔹 PREPARE DATA
        if (!empty($config['data'])) {
            $method = $config['data'];

            if (method_exists($this, $method)) {
                $block['options'] = array_merge(
                    $block['options'],
                    $this->{$method}($uniqueBlockId, $block['options'])
                );
            }
        }

        // 🔹 BUILD NORMAL STYLES
        if (!empty($config['styles'])) {
            $method = $config['styles'];

            if (method_exists($this, $method)) {
                $generatedBlockStyles .= $this->{$method}(
                    $uniqueBlockId,
                    $block['options']
                );
            }
        }

        // 🔹 BUILD ICON STYLES (Unified)
        if (!empty($config['iconStyle'])) {
            $generatedBlockStyles .= $this->buildIconStyles(
                $uniqueBlockId,
                $block['options'],
                $config['iconStyle']
            );
        }
    }

    /**
     * Prepare Data for Offcanvas Block
     */
    protected function prepareOffcanvasData(string $uniqueId, array $options): array
    {
        $elements = $options['elements'] ?? [];

        // Pass necessary menu collections to closure via "use" is tricky if we don't fetch them here.
        // But we have methods to fetch them.
        $menus = [
            'mobile' => $this->getMobileMenus(),
            'main' => $this->getBottomNavMenus(),
            'top' => $this->getTopNavMenus(),
            'footer' => $this->getFooterMenus(),
        ];

        // Define Closures
        $isActive = function ($key, $section) use ($elements) {
            if (empty($elements[$key]['enabled'])) return false;
            $configSection = $elements[$key]['section'] ?? '';
            // Defaults if section not set
            if ($configSection === '') {
                if ($key === 'menu') $configSection = 'main';
                if ($key === 'search') $configSection = 'header';
            }
            return $configSection === $section;
        };

        $getMenuCollection = function ($key) use ($elements, $menus) {
            $loc = $elements['menu']['location'] ?? 'main';
            if (!isset($menus[$loc])) return [];
            return $menus[$loc];
        };

        // Calculate hasFooter
        $hasFooter = collect($elements)->contains(fn($el, $key) => $isActive($key, 'footer'));

        $labelPosition = $options['label_position'] ?? 'hidden';

        // Trigger Class & Label formatting
        $triggerClass = 'd-flex align-items-center';
        $labelClass = '';

        if ($labelPosition === 'bottom') {
            $triggerClass .= ' flex-column lh-1 gap-2';
            $labelClass = 'small lh-1';
        } else {
            $triggerClass .= ' gap-2';
        }

        return [
            'uniqueId' => $uniqueId,
            'iconClass' => $options['icon'] ?? 'bi-list',
            'iconSize' => $options['icon_size'] ?? 'fs-5',
            'iconColor' => $options['icon_color'] ?? '',
            'triggerColor' => ($options['icon_color'] ?? '') ?: 'currentColor',
            'label' => $options['label'] ?? translate('Menu'),
            'labelPosition' => $labelPosition,
            'hideLabelInOffcanvas' => $options['hide_label_offcanvas'] ?? false,
            'elements' => $elements,
            'isActive' => $isActive,
            'getMenuCollection' => $getMenuCollection,
            'hasFooter' => $hasFooter,
            'triggerClass' => $triggerClass,
            'labelClass' => $labelClass,
            'config' => [
                'backdrop' => $options['backdrop'] ?? true,
                'keyboard' => $options['keyboard'] ?? true,
                'scroll' => $options['scroll'] ?? false,
            ]
        ];
    }

    /**
     * Prepare Data for Auth Block
     */
    protected function prepareAuthData(string $uniqueId, array $options): array
    {
        $getBtnClass = function ($style, $displayMode) {
            $class = '';
            if ($style === 'none') {
                $class = 'p-0 border-0 bg-transparent';
            } elseif ($style === 'link') {
                $class = 'btn btn-link btn-sm';
            } else {
                $class = 'btn btn-sm btn-' . $style;
            }

            if ($displayMode === 'icon_text_bottom') {
                $class .= ' d-inline-flex flex-column align-items-center lh-1 gap-2';
            } else {
                $class .= ' d-inline-flex align-items-center';
            }

            return $class;
        };

        $globalRegistration = settings('general')->registration ?? true;
        // In Blade: $blockShowRegister = $options['show_register_btn'] ?? true;
        $blockShowRegister = $options['show_register_btn'] ?? true;

        // Logic: Registration is enabled ONLY if BOTH the global setting is on AND the block setting is enabled.
        $registrationEnabled = $globalRegistration && $blockShowRegister;

        // Login Logic
        $loginTrigger = $options['login_trigger_type'] ?? 'link';
        $loginDisplay = $options['login_display_mode'] ?? 'icon';
        $loginIcon = $options['login_icon'] ?? 'bi-box-arrow-in-right';
        $loginIconSize = $options['login_icon_size'] ?? 'fs-5';
        $loginText = $options['login_text'] ?? translate('Login');
        $loginStyle = $options['login_btn_style'] ?? 'outline-primary';
        $loginBtnClass = $getBtnClass($loginStyle, $loginDisplay);

        $loginWrapperAttrs = '';
        if ($loginDisplay === 'icon_text_tooltip') {
            $loginWrapperAttrs = 'data-bs-toggle="tooltip" title="' . $loginText . '"';
        }

        $showLoginIcon = in_array($loginDisplay, ['icon', 'icon_text', 'icon_text_bottom', 'icon_text_tooltip']);
        $showLoginText = in_array($loginDisplay, ['text', 'icon_text', 'icon_text_bottom']);

        // Register Logic
        $registerTrigger = $options['register_trigger_type'] ?? 'link';
        $registerDisplay = $options['register_display_mode'] ?? 'icon';
        $registerIcon = $options['register_icon'] ?? 'bi-person-plus';
        $registerIconSize = $options['register_icon_size'] ?? 'fs-5';
        $registerText = $options['register_text'] ?? translate('Register');
        $registerStyle = $options['register_btn_style'] ?? 'primary';
        $registerBtnClass = $getBtnClass($registerStyle, $registerDisplay);

        $registerWrapperAttrs = '';
        if ($registerDisplay === 'icon_text_tooltip') {
            $registerWrapperAttrs = 'data-bs-toggle="tooltip" title="' . $registerText . '"';
        }

        $showRegisterIcon = in_array($registerDisplay, ['icon', 'icon_text', 'icon_text_bottom', 'icon_text_tooltip']);
        $showRegisterText = in_array($registerDisplay, ['text', 'icon_text', 'icon_text_bottom']);

        return [
            'uniqueId' => $uniqueId,
            'loginTrigger' => $loginTrigger,
            'loginDisplay' => $loginDisplay,
            'loginIcon' => $loginIcon,
            'loginIconSize' => $loginIconSize,
            'loginText' => $loginText,
            'loginStyle' => $loginStyle,
            'loginBtnClass' => $loginBtnClass . ' auth-login-btn',
            'loginWrapperAttrs' => $loginWrapperAttrs,
            'showLoginIcon' => $showLoginIcon,
            'showLoginText' => $showLoginText,

            'registerTrigger' => $registerTrigger,
            'registerDisplay' => $registerDisplay,
            'registerIcon' => $registerIcon,
            'registerIconSize' => $registerIconSize,
            'registerText' => $registerText,
            'registerStyle' => $registerStyle,
            'registerBtnClass' => $registerBtnClass . ' auth-register-btn',
            'registerWrapperAttrs' => $registerWrapperAttrs,
            'showRegisterIcon' => $showRegisterIcon,
            'showRegisterText' => $showRegisterText,

            'authDisplay' => $options['auth_display'] ?? 'avatar_name',
            'registrationEnabled' => $registrationEnabled,
            'showModals' => ($loginTrigger === 'modal' || $registerTrigger === 'modal'),
        ];
    }

    /**
     * Prepare Data for Message Block
     */
    protected function prepareMessageData(string $uniqueId, array $options): array
    {
        $label = $options['message_label'] ?? '';
        $labelPosition = $options['label_position'] ?? 'none';
        $showBadge = $options['show_badge'] ?? true;
        $icon = $options['icon'] ?? 'bi-chat-dots';
        $iconSize = $options['icon_size'] ?? 'fs-5';

        $wrapperClass = 'd-inline-flex align-items-center';
        if ($labelPosition === 'bottom') {
            $wrapperClass .= ' flex-column lh-1 gap-2';
        }

        $tooltipAttr = '';
        if ($labelPosition === 'tooltip' && !empty($label)) {
            $tooltipAttr = 'data-bs-toggle="tooltip" title="' . $label . '"';
        } elseif ($labelPosition === 'none' || empty($label)) {
            $tooltipAttr = 'title="' . translate('Messages') . '"';
        }

        // Calculate unread messages
        $unreadMessages = 0;
        $user = authUser();
        if ($user && method_exists($user, 'getUnreadConversationsCount')) {
            // Assuming we want unread conversations count as per Badge logic,
            // but variable name suggests messages.
            // Using conversation count is safer as per available trait method.
            $unreadMessages = $user->getUnreadConversationsCount();
        }

        $formattedBadgeCount = ($unreadMessages > 9) ? '9+' : $unreadMessages;

        $showLabel = ($labelPosition !== 'none' && $labelPosition !== 'tooltip' && !empty($label));
        $labelClass = ($labelPosition === 'bottom') ? 'small lh-1' : 'ms-2';

        return [
            'uniqueId' => $uniqueId,
            'label' => $label,
            'labelPosition' => $labelPosition,
            'showBadge' => $showBadge,
            'icon' => $icon,
            'iconSize' => $iconSize,
            'wrapperClass' => $wrapperClass,
            'tooltipAttr' => $tooltipAttr,
            'unreadMessages' => $unreadMessages,
            'formattedBadgeCount' => $formattedBadgeCount,
            'showLabel' => $showLabel,
            'labelClass' => $labelClass,
            'isAuthenticated' => (bool)$user,
        ];
    }

    /**
     * Prepare Data for Cart Block
     */
    protected function prepareCartData(string $uniqueId, array $options): array
    {
        $amount = $this->getCartTotal();
        $formattedAmount = getAmount($amount);
        $cartProductsCount = $this->getCartProductsCount();

        $requireLogin = $options['require_login'] ?? false;
        $viewMode = $options['view_mode'] ?? 'page';

        $isLogged = authUser();
        $cartUrl = route('cart.index');
        $cartLabel = $options['cart_label'] ?? '';
        $labelPosition = $options['label_position'] ?? 'inline';
        $attrs = '';
        $loginClass = '';

        // Generate Unique Offcanvas ID
        $offcanvasId = 'offcanvasCart-' . $uniqueId;

        if ($viewMode === 'offcanvas') {
            $cartUrl = '#' . $offcanvasId;
            $attrs = 'data-bs-toggle="offcanvas" role="button" aria-controls="' . $offcanvasId . '"';
        } elseif ($requireLogin && !$isLogged) {
            $cartUrl = 'javascript:void(0)';
            $loginClass = 'needs-login-modal';
        }

        if ($cartLabel && $labelPosition == 'bottom') {
            $btnClass = 'd-flex flex-column lh-1 gap-2';
        } else {
            $btnClass = 'position-relative d-inline-flex';
        }

        $tooltipAttr = '';
        if ($cartLabel && $labelPosition == 'tooltip') {
            $tooltipAttr = 'data-bs-toggle="tooltip" title="' . e($cartLabel) . '"';
        }

        $showLabel = $cartLabel && $labelPosition != 'tooltip' && $labelPosition != 'none';
        $labelWrapperClass = ($labelPosition == 'bottom') ? 'small lh-1' : 'ms-2';

        return [
            'uniqueId' => $uniqueId,
            'showCount' => $options['show_count'] ?? true,
            'showTotal' => $options['show_total'] ?? false,
            'viewMode' => $viewMode,
            'requireLogin' => $requireLogin,
            'cartLabel' => $cartLabel,
            'labelPosition' => $labelPosition,
            'icon' => $options['icon'] ?? 'bi-cart3',
            'iconSize' => $options['icon_size'] ?? 'fs-5',
            'amount' => $formattedAmount,
            'cartProductsCount' => $cartProductsCount,
            'cartUrl' => $cartUrl,
            'attrs' => $attrs,
            'loginClass' => $loginClass,
            'isLogged' => $isLogged,
            'btnClass' => $btnClass,
            'tooltipAttr' => $tooltipAttr,
            'showLabel' => $showLabel,
            'labelWrapperClass' => $labelWrapperClass,
            'offcanvasId' => $offcanvasId,
        ];
    }

    /**
     * Prepare Data for Logo Block
     */
    protected function prepareLogoData(string $uniqueId, array $options): array
    {
        $siteName = settings('general')->site_name ?? 'EzyMarket';
        $themeSettings = themeSettings('general');

        $logoWidth = $options['logo_width'] ?? null;
        $logoHeight = $options['logo_height'] ?? null;

        $logoStyle = $options['logo_style'] ?? 'logo_dark';
        $logoUrl = ($logoStyle === 'logo_light')
            ? asset($themeSettings->logo_light ?? 'images/logo-light.png')
            : asset($themeSettings->logo_dark ?? 'images/logo.png');

        return [
            'uniqueId' => $uniqueId,
            'logoWidth' => $logoWidth,
            'logoHeight' => $logoHeight,
            'logoStyle' => $logoStyle,
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
        ];
    }

    /**
     * Prepare Data for Social Block
     */
    protected function prepareSocialData(string $uniqueId, array $options): array
    {
        $socials = settings('social_links') ?? [];

        // Trigger options
        $triggerText = $options['trigger_label'] ?? translate('Follow Us');
        $triggerIcon = $options['trigger_icon'] ?? 'bi-share';
        $triggerPos = $options['trigger_label_position'] ?? 'inline';

        $style = $options['trigger_button_style'] ?? 'light';
        if ($style === 'none') {
            $triggerBtnClass = 'bg-transparent border-0 p-0';
        } else {
            $triggerBtnClass = 'btn-' . $style;
        }

        $triggerSizeRaw = $options['trigger_size'] ?? 'md';
        $triggerSizeClass = ($triggerSizeRaw === 'md') ? '' : 'btn-' . $triggerSizeRaw;

        // Logic from Blade
        if (empty($triggerIcon)) {
            $triggerPos = 'inline';
        }

        $socialNames = [
            'facebook' => 'Facebook',
            'x' => 'X (Twitter)',
            'twitter' => 'Twitter',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'pinterest' => 'Pinterest',
            'tiktok' => 'TikTok',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'snapchat' => 'Snapchat',
            'discord' => 'Discord',
            'github' => 'GitHub',
            'dribbble' => 'Dribbble',
            'behance' => 'Behance',
        ];

        $socialIcons = [
            'facebook' => 'bi-facebook',
            'x' => 'bi-twitter-x',
            'twitter' => 'bi-twitter',
            'instagram' => 'bi-instagram',
            'linkedin' => 'bi-linkedin',
            'youtube' => 'bi-youtube',
            'pinterest' => 'bi-pinterest',
            'tiktok' => 'bi-tiktok',
            'whatsapp' => 'bi-whatsapp',
            'telegram' => 'bi-telegram',
            'snapchat' => 'bi-snapchat',
            'discord' => 'bi-discord',
            'github' => 'bi-github',
            'dribbble' => 'bi-dribbble',
            'behance' => 'bi-behance',
        ];

        $brandColors = [
            'facebook' => '#1877F2',
            'x' => '#000000',
            'twitter' => '#1DA1F2',
            'instagram' => '#E4405F',
            'linkedin' => '#0A66C2',
            'youtube' => '#FF0000',
            'pinterest' => '#BD081C',
            'tiktok' => '#000000',
            'whatsapp' => '#25D366',
            'telegram' => '#0088cc',
            'snapchat' => '#FFFC00',
            'discord' => '#5865F2',
            'github' => '#181717',
            'dribbble' => '#ea4c89',
            'behance' => '#1769ff',
        ];

        return [
            'socials' => $socials,
            'socialNames' => $socialNames,
            'socialIcons' => $socialIcons,
            'brandColors' => $brandColors,
            'iconSize' => $options['icon_size'] ?? 'fs-6',
            'iconStyle' => $options['icon_style'] ?? 'default',
            'viewStyle' => $options['view_style'] ?? 'regular',
            'displayStyle' => $options['display_style'] ?? 'icon_only',
            'colorStyle' => $options['color_style'] ?? 'monochrome',
            'activeHoverEffects' => $options['active_hover_effects'] ?? false,
            'triggerText' => $triggerText,
            'triggerIcon' => $triggerIcon,
            'triggerPos' => $triggerPos,
            'triggerBtnClass' => $triggerBtnClass,
            'triggerSizeClass' => $triggerSizeClass,
            'triggerShape' => $options['trigger_shape'] ?? '',
            'hideDropdownIcon' => $options['hide_dropdown_icon'] ?? false,
        ];
    }

    /**
     * Prepare Data for Language/Currency Block
     */
    protected function prepareLanguageCurrencyData(string $uniqueId, array $options): array
    {
        $languages = getLanguageSwiter();
        $currencies = currencies();
        $currentLangCode = app()->getLocale();

        $currentCurrObj = currentCurrency();
        $currentCurrCode = $currentCurrObj->code ?? 'USD';
        $currentCurrSymbol = $currentCurrObj->symbol ?? '$';

        $triggerType = $options['trigger_type'] ?? 'both';
        $dropdownContent = $options['dropdown_content'] ?? 'respective';
        $style = $options['display_style'] ?? 'dropdown';
        $langFormat = $options['lang_format'] ?? 'code';
        $currFormat = $options['currency_format'] ?? 'code';
        $labelPosition = $options['label_position'] ?? 'inline';
        $customIcon = $options['icon'] ?? 'bi-globe';
        $iconSize = $options['icon_size'] ?? 'fs-5';
        $hideDropdownIcon = $options['hide_lc_drop_icon'] ?? false;

        // Define base triggers
        $rawTriggers = [];
        if ($triggerType === 'both') {
            $rawTriggers[] = ['mode' => 'language', 'content' => 'language'];
            $rawTriggers[] = ['mode' => 'currency', 'content' => 'currency'];
        } else {
            $content = ($dropdownContent === 'both') ? 'both' : $triggerType;
            $rawTriggers[] = ['mode' => $triggerType, 'content' => $content];
        }

        // Process triggers data for View
        $triggers = [];
        foreach ($rawTriggers as $rt) {
            $mode = $rt['mode'];

            // Determine Label
            $label = '';
            if ($mode === 'language') {
                if ($langFormat === 'name') {
                    $label = $languages[$currentLangCode] ?? strtoupper($currentLangCode);
                } elseif ($langFormat === 'flag') {
                    $label = '';
                } else {
                    $label = strtoupper($currentLangCode);
                }
            } elseif ($mode === 'currency') {
                if ($currFormat === 'symbol') {
                    $label = $currentCurrSymbol;
                } elseif ($currFormat === 'symbol_code') {
                    $label = $currentCurrSymbol . ' ' . $currentCurrCode;
                } else {
                    $label = $currentCurrCode;
                }
            }

            // CSS Classes
            $wrapperClass = 'header-lang-currency ' . ($style === 'dropdown' ? 'dropdown' : '');
            $wrapperId = 'langCurrency-' . $mode;

            $showLabel = ($labelPosition === 'inline' || $labelPosition === 'bottom');
            $isTooltip = ($labelPosition === 'tooltip');
            $wrapperAttrs = $isTooltip ? 'data-bs-toggle="tooltip" title="' . $label . '"' : '';

            // Modal Target
            $targetId = '#switchCurrencyLanguage';
            if ($triggerType === 'both') {
                $targetId = ($mode === 'language') ? '#switchLanguage' : '#switchCurrency';
            }

            // Layout
            $contentLayout = ($labelPosition === 'bottom')
                ? 'd-flex flex-column align-items-center gap-2 lh-1'
                : 'd-flex align-items-center gap-2';

            // Link Attributes
            $linkAttrs = '';
            if ($style === 'dropdown') {
                $linkAttrs = 'data-bs-toggle="dropdown" aria-expanded="false"';
                $contentLayout .= ' ' . ($hideDropdownIcon ? '' : 'dropdown-toggle');
            } else {
                $linkAttrs = 'data-bs-toggle="modal" data-bs-target="' . $targetId . '"';
            }

            $triggers[] = [
                'mode' => $mode,
                'content' => $rt['content'],
                'label' => $label,
                'wrapperClass' => $wrapperClass,
                'wrapperId' => $wrapperId,
                'wrapperAttrs' => $wrapperAttrs,
                'contentLayout' => $contentLayout,
                'linkAttrs' => $linkAttrs,
                'showLabel' => $showLabel,
                'labelPosition' => $labelPosition,
            ];
        }

        return [
            'uniqueId' => $uniqueId,
            'triggerType' => $triggerType,
            'style' => $style,
            'customIcon' => $customIcon,
            'iconSize' => $iconSize,
            'languages' => $languages,
            'currencies' => $currencies,
            'currentLangCode' => $currentLangCode,
            'currentCurrCode' => $currentCurrCode,
            'triggers' => $triggers,
        ];
    }

    /**
     * Prepare Data for Countdown Block
     */
    protected function prepareCountdownData(string $uniqueId, array $options): array
    {
        $targetDate = $options['target_date'] ?? now()->addDays(7)->format('Y-m-d H:i:s');
        $label = $options['label'] ?? '';
        $labelIcon = $options['label_icon'] ?? '';
        $showDays = $options['show_days'] ?? true;
        $showHours = $options['show_hours'] ?? true;
        $showMinutes = $options['show_minutes'] ?? true;
        $showSeconds = $options['show_seconds'] ?? true;
        $style = $options['style'] ?? 'inline';
        $boxStyle = $options['box_style'] ?? 'primary';
        $matchLabelStyle = $options['match_label_style'] ?? false;

        // Determine Box Class
        $boxClass = $style === 'boxed' ? 'bg-' . $boxStyle . ' text-white rounded px-2 py-1' : '';
        // Determine Label Class
        $labelClass = $matchLabelStyle && $style === 'boxed'
            ? 'countdown-label small fw-medium bg-' . $boxStyle . ' text-white rounded bg-opacity-75 px-2 py-1'
            : 'countdown-label small fw-medium';

        // Handle Light/Dark text for Light background
        if ($boxStyle === 'light' || $boxStyle === 'white') {
            $boxClass = str_replace('text-white', 'text-dark', $boxClass);
            if (str_contains($labelClass, 'bg-' . $boxStyle)) {
                $labelClass = str_replace('text-white', 'text-dark', $labelClass);
            }
        }

        $isExpired = false;
        try {
            if ($targetDate && \Carbon\Carbon::parse($targetDate)->isPast()) {
                $isExpired = true;
            }
        } catch (\Throwable $e) {
        }

        return [
            'uniqueId' => $uniqueId,
            'targetDate' => $targetDate,
            'label' => $label,
            'labelIcon' => $labelIcon,
            'showDays' => $showDays,
            'showHours' => $showHours,
            'showMinutes' => $showMinutes,
            'showSeconds' => $showSeconds,
            'style' => $style,
            'boxClass' => $boxClass,
            'labelClass' => $labelClass,
            'isExpired' => $isExpired,
        ];
    }

    /**
     * Prepare Data for Icon Block
     */
    protected function prepareIconData(string $uniqueId, array $options): array
    {
        $textColor = $options['section_text_color'] ?? '';

        $iconClass = $options['icon'] ?? 'bi-star';
        $iconSize = $options['icon_size'] ?? 'fs-5';
        $link = $options['link'] ?? '';
        $linkTarget = $options['link_target'] ?? '_self';
        $tooltip = $options['tooltip'] ?? '';
        $showLabel = ($options['show_label'] ?? '0') == '1';
        $labelText = $options['label_text'] ?? '';
        $labelPosition = $options['label_position'] ?? 'right';

        $wrapperTag = $link ? 'a' : 'span';

        $flexClasses = 'd-inline-flex align-items-center';
        $directionClass = ($showLabel && $labelText && $labelPosition === 'bottom')
            ? 'flex-column gap-2 lh-1'
            : 'gap-2';

        $wrapperClass = "header-custom-icon {$flexClasses} {$directionClass}";

        $wrapperAttrs = 'class="' . $wrapperClass . '"';
        if ($link) {
            $wrapperAttrs .= ' href="' . $link . '" target="' . $linkTarget . '"';
        }

        if ($tooltip) {
            $wrapperAttrs .= ' data-bs-toggle="tooltip" title="' . $tooltip . '"';
        }

        return [
            'uniqueId' => $uniqueId,
            'wrapperTag' => $wrapperTag,
            'wrapperAttrs' => $wrapperAttrs,
            'iconClass' => $iconClass,
            'iconSize' => $iconSize,
            'showLabel' => $showLabel,
            'labelText' => $labelText,
            'labelPosition' => $labelPosition,
        ];
    }

    /**
     * Prepare Data for Menu Block
     */
    protected function prepareMenuData(string $uniqueId, array $options): array
    {
        $location = $options['menu_location'] ?? 'top';
        $menuStyle = $options['menu_style'] ?? 'horizontal';
        $verticalLabel = $options['vertical_menu_label'] ?? translate('All Categories');
        $verticalIcon = $options['vertical_menu_icon'] ?? '';
        $btnStyle = $options['btn_style'] ?? 'primary';
        $btnSize = $options['btn_size'] ?? '';
        $isOpened = $options['initially_open'] ?? false;
        $hideDropdownIcon = $options['vr_hide_dropdown_icon'] ?? false;

        $navMenu = match ($location) {
            'bottom' => $this->getBottomNavMenus(),
            'mobile' => $this->getMobileMenus(),
            'footer' => $this->getFooterMenus(),
            default  => $this->getTopNavMenus(),
        };

        return [
            'uniqueId' => $uniqueId,
            'navMenu' => $navMenu,
            'menuStyle' => $menuStyle,
            'verticalLabel' => $verticalLabel,
            'verticalIcon' => $verticalIcon,
            'btnStyle' => $btnStyle,
            'btnSize' => $btnSize,
            'initiallyOpen' => $isOpened,
            'hideDropdownIcon' => $hideDropdownIcon,
        ];
    }

    /**
     * Prepare Data for Search Block
     */
    protected function prepareSearchData(string $uniqueId, array $options): array
    {
        $style = $options['search_style'] ?? 'standard';
        $placeholder = $options['placeholder'] ?? translate('Search here...');

        // Button Options
        $showBtn = $options['show_search_btn'] ?? true;
        $defaultPos = $showBtn ? 'right' : 'none';
        $btnPosition = $options['search_btn_position'] ?? $defaultPos;

        $formId = 'search-form-' . $uniqueId;

        // Wrapper Classes
        $wrapperClasses = 'header-search';
        if ($style === 'standard') {
            $wrapperClasses .= ' flex-grow-1 mx-3 position-relative';
        } elseif ($style === 'full_width') {
            $wrapperClasses .= ' position-static';
        } else {
            $wrapperClasses .= ' position-relative';
        }

        $triggerMode = $options['trigger_display_mode'] ?? 'icon';
        $triggerPos = $options['trigger_icon_position'] ?? 'left';

        // Trigger Class & Label formatting
        $triggerClass = 'd-flex align-items-center';
        $labelClass = '';

        if ($triggerPos === 'bottom') {
            $triggerClass .= ' flex-column lh-1 gap-2';
            $labelClass = 'small lh-1';
        } else {
            $triggerClass .= ' gap-2';
        }

        return [
            'uniqueId' => $uniqueId,
            'formId' => $formId,
            'style' => $style,
            'wrapperClasses' => $wrapperClasses,
            'placeholder' => $placeholder,
            'liveSearch' => $options['live_search'] ?? false,
            'btnPosition' => $btnPosition,
            'btnIcon' => $options['btn_icon'] ?? 'bi-search',
            'showBtnText' => $options['show_btn_text'] ?? false,
            'triggerMode' => $triggerMode,
            'triggerPos' => $triggerPos,
            'triggerClass' => $triggerClass,
            'labelClass' => $labelClass,
            'triggerText' => $options['trigger_text'] ?? translate('Search'),
            'triggerIconSize' => $options['trigger_icon_size'] ?? 'fs-5',
        ];
    }

    /**
     * Prepare Data for Theme Toggle Block
     */
    protected function prepareThemeToggleData(string $uniqueId, array $options): array
    {
        $style = $options['toggle_style'] ?? 'icon';
        $label = $options['toggle_label'] ?? translate('Theme');
        $position = $options['label_position'] ?? 'hidden';
        $iconSize = $options['icon_size'] ?? 'fs-5';

        $wrapperClasses = 'header-theme-toggle d-flex align-items-center';
        $textClasses = '';

        if ($position === 'bottom') {
            $wrapperClasses .= ' flex-column lh-1 gap-2';
            $textClasses = 'small lh-1';
        } elseif ($position === 'inline') {
            $wrapperClasses .= ' gap-2';
        }

        $isTooltip = ($position === 'tooltip');
        $tooltipAttrs = $isTooltip ? 'data-bs-toggle="tooltip" title="' . $label . '"' : '';

        return [
            'uniqueId' => $uniqueId,
            'style' => $style,
            'label' => $label,
            'position' => $position,
            'iconSize' => $iconSize,
            'wrapperClasses' => $wrapperClasses,
            'textClasses' => $textClasses,
            'tooltipAttrs' => $tooltipAttrs,
        ];
    }

    /**
     * Prepare Data for Notification Block
     */
    protected function prepareNotificationData(string $uniqueId, array $options): array
    {
        $unreadCount = authUser()?->unreadNotifications?->count() ?? 0;

        // Formatted Badge Count (9+)
        $formattedBadgeCount = $unreadCount > 9 ? '9+' : $unreadCount;

        // Label handling
        $label = $options['notification_label'] ?? '';
        $labelPosition = $options['label_position'] ?? 'tooltip'; // tooltip, left, right, none

        $tooltipAttr = '';
        if (!empty($label) && $labelPosition === 'tooltip') {
            $tooltipAttr = 'title="' . e($label) . '" data-bs-toggle="tooltip"';
            $label = ''; // Clear label for display since it's in tooltip
        } else if ($labelPosition === 'none') {
            $label = '';
        }

        $icon = $options['icon'] ?? 'bi-bell';
        $iconSize = $options['icon_size'] ?? 'fs-5';

        // Badge Visibility
        $optionShowBadge = isset($options['show_badge']) ? $options['show_badge'] : true;
        $showBadge = $optionShowBadge && ($unreadCount > 0);

        // Label Class
        $labelClass = ($labelPosition === 'bottom') ? 'small lh-1' : 'ms-2';

        // Layout wrapper class
        $wrapperClass = 'd-flex align-items-center';
        if ($labelPosition === 'bottom') {
            $wrapperClass .= ' flex-column lh-1 gap-2';
        }

        return [
            'uniqueId' => $uniqueId,
            'tooltipAttr' => $tooltipAttr,
            'wrapperClass' => $wrapperClass,
            'icon' => $icon,
            'iconSize' => $iconSize,
            'showBadge' => $showBadge,
            'formattedBadgeCount' => $formattedBadgeCount,
            'unreadCount' => $unreadCount,
            'showLabel' => !empty($label),
            'label' => $label,
            'labelClass' => $labelClass,
        ];
    }

    /**
     * Prepare Data for Favorites Block
     */
    protected function prepareFavoritesData(string $uniqueId, array $options): array
    {
        $favoritesProductsCount = $this->getFavoritesProductsCount();
        $favoritesUrl = route('favorites.index');
        $favoritesLabel = $options['favorites_label'] ?? '';
        $labelPosition = $options['label_position'] ?? 'inline';
        $attrs = '';

        if ($favoritesLabel && $labelPosition == 'bottom') {
            $btnClass = 'd-flex flex-column lh-1 gap-2';
        } else {
            $btnClass = 'position-relative d-inline-flex';
        }

        $tooltipAttr = '';
        if ($favoritesLabel && $labelPosition == 'tooltip') {
            $tooltipAttr = 'data-bs-toggle="tooltip" title="' . e($favoritesLabel) . '"';
        }

        $showLabel = $favoritesLabel && $labelPosition != 'tooltip' && $labelPosition != 'none';
        $labelWrapperClass = ($labelPosition == 'bottom') ? 'small lh-1' : 'ms-2';

        return [
            'uniqueId' => $uniqueId,
            'showCount' => $options['show_count'] ?? true,
            'favoritesLabel' => $favoritesLabel,
            'labelPosition' => $labelPosition,
            'icon' => $options['icon'] ?? 'bi-heart',
            'iconSize' => $options['icon_size'] ?? 'fs-5',
            'favoritesUrl' => $favoritesUrl,
            'favoritesProductsCount' => $favoritesProductsCount,
            'btnClass' => $btnClass,
            'attrs' => $attrs,
            'tooltipAttr' => $tooltipAttr,
            'showLabel' => $showLabel,
            'labelWrapperClass' => $labelWrapperClass,
        ];
    }

    /**
     * Prepare Data for Premium Block
     */
    protected function preparePremiumData(string $uniqueId, array $options): array
    {
        $icon = $options['icon'] ?? 'bi-gem';
        $btnClass = 'btn-' . ($options['button_style'] ?? 'warning');
        $sizeClass = 'btn-' . ($options['button_size'] ?? 'sm');
        $shape = $options['button_shape'] ?? '';
        $text = $options['button_text'] ?? translate('Premium');
        $position = $options['label_position'] ?? 'inline';

        $url = $options['button_url'] ?? null;
        if (empty($url)) {
            $url = route('premium.plans');
        }

        // Force show text if no icon
        if (empty($icon)) {
            $position = 'inline';
        }

        // Wrapper/Button Class Logic
        $layoutClass = ($position === 'bottom')
            ? 'flex-column justify-content-center text-center lh-1 gap-2'
            : 'gap-2';

        $buttonClasses = "btn {$btnClass} {$sizeClass} {$shape} fw-semibold d-inline-flex align-items-center {$layoutClass}";

        // Icon Class Logic
        $iconClass = "bi {$icon}";
        if ($position !== 'inline' && $position !== 'bottom') {
            $iconClass .= ' fs-5';
        }

        // Tooltip logic
        $tooltipAttr = '';
        $ariaLabel = '';

        if ($position === 'tooltip') {
            $tooltipAttr = 'data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . e($text) . '"';
            $ariaLabel = 'aria-label="' . e($text) . '"';
        } elseif ($position === 'hidden') {
            $ariaLabel = 'aria-label="' . e($text) . '"';
        }

        return [
            'uniqueId' => $uniqueId,
            'url' => $url,
            'text' => $text,
            'icon' => $icon,
            'position' => $position,
            'buttonClasses' => $buttonClasses,
            'iconClass' => $iconClass,
            'tooltipAttr' => $tooltipAttr,
            'ariaLabel' => $ariaLabel,
            'showIcon' => !empty($icon),
            'showLabel' => ($position === 'inline' || $position === 'bottom'),
            'labelClass' => ($position === 'bottom' ? 'small' : '')
        ];
    }

    /**
     * Prepare Data for Divider Block
     */
    protected function prepareDividerData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'margin' => $options['margin'] ?? '3',
        ];
    }

    /**
     * Prepare Data for Button Block
     */
    protected function prepareButtonData(string $uniqueId, array $options): array
    {
        $style = $options['button_style'] ?? 'primary';
        $size = $options['button_size'] ?? 'md';
        $shape = $options['button_shape'] ?? '';
        $text = $options['button_text'] ?? translate('Button');
        $url = $options['button_url'] ?? '#';
        $icon = $options['icon'] ?? '';
        $position = $options['label_position'] ?? 'inline';
        $target = ($options['open_new_tab'] ?? false) ? '_blank' : '_self';

        if (empty($icon)) {
            $position = 'inline';
        }

        return [
            'uniqueId' => $uniqueId,
            'text' => $text,
            'url' => $url,
            'style' => $style,
            'size' => $size,
            'shape' => $shape,
            'icon' => $icon,
            'position' => $position,
            'target' => $target,
        ];
    }

    /**
     * Prepare Data for HTML Block
     */
    protected function prepareHtmlData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'htmlContent' => $options['html_content'] ?? '',
        ];
    }

    /**
     * Generate custom CSS for section/header layout
     */
    protected function buildSectionStyles(array $options): string
    {
        $styles = [];
        $styles[] = "display: flex";
        $styles[] = "width: 100%";
        $styles[] = "flex-wrap: wrap"; // Ensure columns wrap appropriately

        // Container Width: Boxed (1080px) - Applied to inner row
        if (($options['container_width'] ?? '') === 'boxed') {
            $styles[] = "max-width: 1080px";
            $styles[] = "margin-left: auto";
            $styles[] = "margin-right: auto";
        }

        // Apply Min Height here so align-items works relative to this height
        $this->addStyleUnit($styles, 'min-height', $options['min_height'] ?? null);

        $styles[] = "flex-direction: " . ($options['flex_direction'] ?? 'row');
        $styles[] = "justify-content: " . ($options['justify_content'] ?? 'space-between');
        $styles[] = "align-items: " . ($options['align_items'] ?? 'center');
        return implode('; ', $styles);
    }

    /**
     * Generate custom CSS styles from options
     */
    protected function buildCustomStyles(array $options): string
    {
        $styles = [];

        // Margins
        $this->addStyleUnit($styles, 'margin-top', $options['margin_top'] ?? null);
        $this->addStyleUnit($styles, 'margin-right', $options['margin_right'] ?? null);
        $this->addStyleUnit($styles, 'margin-bottom', $options['margin_bottom'] ?? null);
        $this->addStyleUnit($styles, 'margin-left', $options['margin_left'] ?? null);

        // Paddings
        $this->addStyleUnit($styles, 'padding-top', $options['padding_top'] ?? null);
        $this->addStyleUnit($styles, 'padding-right', $options['padding_right'] ?? null);
        $this->addStyleUnit($styles, 'padding-bottom', $options['padding_bottom'] ?? null);
        $this->addStyleUnit($styles, 'padding-left', $options['padding_left'] ?? null);

        // Borders
        $borderStyle = $options['border_style'] ?? null;

        if ($borderStyle && $borderStyle !== 'none') {
            $styles[] = "border-style: {$borderStyle}";
            $styles[] = "border-color: " . ($options['border_color'] ?? '#dee2e6');

            // Explicitly set width to 0 if not provided when style is active (prevents default UA width)
            $styles[] = "border-top-width: " . ($options['border_top_width'] ?: '0') . "px";
            $styles[] = "border-right-width: " . ($options['border_right_width'] ?: '0') . "px";
            $styles[] = "border-bottom-width: " . ($options['border_bottom_width'] ?: '0') . "px";
            $styles[] = "border-left-width: " . ($options['border_left_width'] ?: '0') . "px";
        } elseif ($borderStyle === 'none') {
            // Only explicitly remove border if User selected "None"
            // If unset (null), we allow the default 'border-bottom' classes to work
            $styles[] = "border: none !important";
        }

        // Border Radius
        $this->addStyleUnit($styles, 'border-radius', $options['border_radius'] ?? null);

        // Background Image
        if (!empty($options['bg_image'])) {
            $bg = $options['bg_image'];
            $bgUrl = (str_starts_with($bg, 'http') || str_starts_with($bg, '//')) ? $bg : url($bg);
            $styles[] = "background-image: url('" . $bgUrl . "')";
            $styles[] = "background-repeat: " . ($options['bg_repeat'] ?? 'no-repeat');
            $styles[] = "background-size: " . ($options['bg_size'] ?? 'cover');
            $styles[] = "background-position: " . ($options['bg_position'] ?? 'center center');
        }

        // Font Family
        if (!empty($options['font_family'])) {
            $styles[] = "font-family: " . $options['font_family'];
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

        return implode('; ', $styles);
    }

    /**
     * Generate CSS for Logo Block
     */
    protected function buildLogoStyles(string $uniqueId, array $options): string
    {
        $css = [];

        // Get options
        $logoWidth  = $options['logo_width'] ?? null;
        $logoHeight = $options['logo_height'] ?? null;
        $objectFit  = $options['object_fit'] ?? 'contain';

        // Width & Height
        if (!empty($logoWidth)) {
            $css[] = "width: {$logoWidth}px;";
        }
        if (!empty($logoHeight)) {
            $css[] = "max-height: {$logoHeight}px;";
        }

        // Default fallback if neither provided
        if (empty($logoWidth) && empty($logoHeight)) {
            $css[] = "width: 180px;";
            $css[] = "max-height: 120px;";
        }

        // Object fit
        $css[] = "object-fit: {$objectFit};";

        // Responsive support: automatically scale on small screens
        $responsiveCss = "
            @media (max-width: 576px) {
                #{$uniqueId} .site-logo {
                    width: 120px !important;
                    max-height: 80px !important;
                }
            }
        ";

        $logoCss = implode(' ', $css);

        // Return full CSS
        return "#{$uniqueId} .site-logo { {$logoCss} } {$responsiveCss}";
    }

    /**
     * Generate CSS for Offcanvas Block
     */
    protected function buildOffcanvasStyles(string $uniqueId, array $options): string
    {
        $bgColor = $options['bg_color'] ?? '#ffffff';
        $textColor = $options['text_color'] ?? '#212529';
        $iconColor = $options['icon_color'] ?? $options['section_text_color'] ?? $textColor;

        $offcanvasWidth = $options['offcanvas_width'] ?? 'default';
        $socialBrand = ($options['social']['color_style'] ?? 'monochrome') === 'brand';

        $widthMap = [
            'sm' => '300px',
            'md' => '500px',
            'lg' => '800px',
            'full' => '100%'
        ];
        $widthValue = $widthMap[$offcanvasWidth] ?? null;

        // Logic from Blade
        $border = $textColor === '#ffffff' ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
        $hoverBg = $textColor === '#ffffff' ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

        $css = "#{$uniqueId}{--oc-bg:{$bgColor};--oc-color:{$textColor};--oc-border:{$border};--oc-hover-bg:{$hoverBg};background-color:var(--oc-bg);color:var(--oc-color);}";

        if ($widthValue) {
            $css .= "#{$uniqueId}{width:{$widthValue};}";
        }

        $css .= "#{$uniqueId}-trigger {color: {$iconColor} !important;}";
        $css .= "#{$uniqueId} .btn-close{background:transparent !important;color:var(--oc-color) !important;}#{$uniqueId} .btn-close:hover{opacity:1;}";
        $css .= "#{$uniqueId} a,#{$uniqueId} button:not(.btn-primary):not(.btn-warning):not(.btn-danger):not(.btn-info):not(.btn-success),#{$uniqueId} .h1,#{$uniqueId} .h2,#{$uniqueId} .h3,#{$uniqueId} .h4,#{$uniqueId} .h5,#{$uniqueId} .h6{color:{$textColor};}";
        $css .= "#{$uniqueId} .offcanvas-header,#{$uniqueId} .offcanvas-footer,#{$uniqueId} .border-bottom,#{$uniqueId} .border-top{border-color:var(--oc-border) !important;}";
        $css .= "#{$uniqueId} .nav-link:hover,#{$uniqueId} .btn-action:hover,#{$uniqueId} .dropdown-item:hover{background-color:var(--oc-hover-bg);}";

        if ($socialBrand) {
            $css .= "#{$uniqueId} .social-link-facebook{color:#1877f2 !important;}" .
                "#{$uniqueId} .social-link-twitter{color:#1da1f2 !important;}" .
                "#{$uniqueId} .social-link-instagram{color:#e4405f !important;}" .
                "#{$uniqueId} .social-link-linkedin{color:#0a66c2 !important;}" .
                "#{$uniqueId} .social-link-youtube{color:#ff0000 !important;}" .
                "#{$uniqueId} .social-link-pinterest{color:#bd081c !important;}" .
                "#{$uniqueId} .social-link-tiktok{color:#000000 !important;}" .
                "#{$uniqueId} .social-link-whatsapp{color:#25d366 !important;}";
        }

        return minifyCss($css);
    }

    /**
     * Generate CSS for Divider Block
     */
    protected function buildDividerStyles(string $uniqueId, array $options): string
    {
        $height = $options['height'] ?? '24';
        $color = $options['color'] ?? '#dee2e6';

        return "#{$uniqueId} .vr{height:{$height}px;background-color:{$color};opacity:1;}";
    }

    /**
     * Generate CSS for Search Block
     */
    protected function buildSearchStyles(string $uniqueId, array $options): string
    {
        $inputBgColor = $options['input_bg_color'] ?? '';
        $inputTextColor = $options['input_text_color'] ?? '';
        $inputTransparent = $options['input_transparent'] ?? false;

        $btnBg = $options['btn_bg_color'] ?? '';
        $btnTransparent = $options['btn_transparent'] ?? false;
        $style = $options['search_style'] ?? 'standard';

        $formId = 'search-form-' . $uniqueId;

        // Trigger Color
        $css = "#searchTrigger-{$uniqueId}{color:var(--header-icon-color, inherit);}";

        // Input Styles
        $css .= "#{$formId} .search-input{";
        if ($inputTransparent) {
            $css .= "background-color:transparent;";
            $css .= "border-color:" . ($inputBgColor ?: '#fff') . ";";
            $css .= "color:" . ($inputTextColor ?: ($inputBgColor ?: '#fff')) . ";";
        } else {
            $css .= "background-color:" . ($inputBgColor ?: '#fff') . ";";
            if ($inputTextColor) {
                $css .= "color:{$inputTextColor};";
            }
        }
        $css .= "}";

        // Placeholder
        $phColor = ($inputTransparent && !$inputTextColor) ? ($inputBgColor ?: '#fff') : ($inputTextColor ?: '');
        if ($phColor) {
            $css .= "#{$formId} .search-input::placeholder{color:{$phColor} !important;opacity:0.8;}";
            $css .= "#{$formId} .search-input::-webkit-input-placeholder{color:{$phColor} !important;}";
        }

        // Button Styles
        $css .= "#{$formId} .search-btn{";
        if ($btnTransparent) {
            $css .= "background-color:transparent;";
            $css .= "border-color:" . ($btnBg ?: '#fff') . ";";
            $css .= "color:" . ($btnBg ?: '#fff') . ";";
        } else {
            if ($btnBg) {
                $css .= "background-color:{$btnBg};border-color:{$btnBg};";
            }
        }
        $css .= "}";

        // Expandable Style Specific
        if ($style === 'expandable') {
            $css .= "#{$formId}{min-width:300px;}";
        }

        return minifyCss($css);
    }

    /**
     * Build styles for Auth Block
     */
    protected function buildAuthStyles(string $uniqueId, array $options): string
    {
        $styles = [];

        $loginStyle = $options['login_btn_style'] ?? 'outline-primary';
        if ($loginStyle === 'none' || $loginStyle === 'link') {
            $styles[] = "#{$uniqueId} .auth-login-btn { color: var(--header-icon-color, inherit); }";
        }

        $registerStyle = $options['register_btn_style'] ?? 'primary';
        if ($registerStyle === 'none' || $registerStyle === 'link') {
            $styles[] = "#{$uniqueId} .auth-register-btn { color: var(--header-icon-color, inherit); }";
        }

        $authDisplay = $options['auth_display'] ?? 'avatar_name';
        if ($authDisplay !== 'avatar_only') {
            $styles[] = "#{$uniqueId} .header-user-name { color: var(--header-icon-color, inherit); }";
        }

        return implode(' ', $styles);
    }

    /**
     * Generate CSS for Social Block
     */
    protected function buildSocialStyles(string $uniqueId, array $options): string
    {
        $viewStyle = $options['view_style'] ?? 'regular';
        $displayStyle = $options['display_style'] ?? 'icon_only';
        $colorStyle = $options['color_style'] ?? 'monochrome';
        $activeHoverEffects = $options['active_hover_effects'] ?? false;

        $css = "#{$uniqueId} a,#{$uniqueId} i{transition:all 0.3s ease;}";
        $css .= "#{$uniqueId} .icon-circle-box{width:32px;height:32px;}";

        if ($viewStyle == 'dropdown') {
            $minWidth = ($displayStyle == 'icon_name') ? '320px' : '160px';
            $css .= "#{$uniqueId} .dropdown-menu{min-width:{$minWidth};}";

            $cols = ($displayStyle == 'icon_name') ? 3 : 2;
            $css .= "#{$uniqueId} .social-grid-layout{display:grid;grid-template-columns:repeat({$cols},1fr);padding:0.5rem;}";
        }

        if ($colorStyle == 'multicolor') {
            $brandColors = [
                'facebook' => '#1877F2',
                'x' => '#000000',
                'twitter' => '#1DA1F2',
                'instagram' => '#E4405F',
                'linkedin' => '#0A66C2',
                'youtube' => '#FF0000',
                'pinterest' => '#BD081C',
                'tiktok' => '#000000',
                'whatsapp' => '#25D366',
                'telegram' => '#0088cc',
                'snapchat' => '#FFFC00',
                'discord' => '#5865F2',
                'github' => '#181717',
                'dribbble' => '#EA4C89',
                'behance' => '#1769FF',
            ];

            foreach ($brandColors as $brand => $color) {
                $css .= "#{$uniqueId} .text-{$brand}{color:{$color} !important;}";
                $css .= "#{$uniqueId} .border-{$brand}{border-color:{$color} !important;}";
                if ($activeHoverEffects) {
                    $css .= "#{$uniqueId} a.text-{$brand}:hover{background-color:{$color} !important;color:#fff !important;}";
                    $css .= "#{$uniqueId} a.text-{$brand}:hover i{color:#fff !important;}";
                }
            }
        }

        return minifyCss($css);
    }

    /**
     * Generate CSS for Theme Toggle Block
     */
    protected function buildThemeToggleStyles(string $uniqueId, array $options): string
    {
        $iconColor = $options['icon_color'] ?? '';

        $css = "";
        if ($iconColor) {
            $css .= "#{$uniqueId} {color:{$iconColor};}";
        }

        // Add the animation styles scoped to this uniqueId
        $css .= "#{$uniqueId} .theme-toggle-label{position:relative;display:inline-block;width:24px;height:24px;cursor:pointer;vertical-align:middle;}";
        $css .= "#{$uniqueId} .theme-toggle-icon{position:absolute;top:50%;left:50%;transform-origin:center;transition:all 0.4s cubic-bezier(0.4,0.0,0.2,1);}";

        $css .= "#{$uniqueId} .theme-toggle-label .sun-icon{opacity:0;transform:translate(-50%,-50%) rotate(90deg) scale(0.5);}";
        $css .= "#{$uniqueId} .theme-toggle-label .moon-icon{opacity:1;transform:translate(-50%,-50%) rotate(0deg) scale(1);}";

        // Use global attribute selector for dark theme
        $css .= "[data-bs-theme=\"dark\"] #{$uniqueId} .theme-toggle-label .sun-icon{opacity:1;transform:translate(-50%,-50%) rotate(0deg) scale(1);}";
        $css .= "[data-bs-theme=\"dark\"] #{$uniqueId} .theme-toggle-label .moon-icon{opacity:0;transform:translate(-50%,-50%) rotate(-90deg) scale(0.5);}";

        return minifyCss($css);
    }

    /**
     * Generate CSS for Icon in Block
     */
    protected function buildIconStyles(string $uniqueId, array $options, string $selector): string
    {
        $iconColor = $options['icon_color'] ?? '';
        $colorValue = $iconColor ?: 'var(--header-text-color)';

        // Escape numeric ID if it starts with a digit
        // CSS numeric IDs need a backslash
        $escapedId = preg_replace_callback('/^(\d)/', fn($m) => "\\" . $m[1] . " ", ltrim($uniqueId, '#'));
        $uniqueIdSelector = "#{$escapedId}";

        $selector = trim($selector);

        // Generate both patterns
        $finalSelector = "{$uniqueIdSelector}{$selector}, {$uniqueIdSelector} {$selector}";

        return "{$finalSelector} { color: {$colorValue} !important; }";
    }

    /**
     * Generate CSS for Menu Block
     */
    protected function buildMenuStyles(string $uniqueId, array $options): string
    {
        $textColor = $options['text_color'] ?? '';
        $hoverTextColor = $options['hover_text_color'] ?? '';
        $hoverBgColor = $options['hover_bg_color'] ?? '';

        $fontSize = $options['font_size'] ?? '';
        $fontWeight = $options['font_weight'] ?? '';
        $paddingX = $options['padding_x'] ?? '7';
        $paddingY = $options['padding_y'] ?? '';

        $hoverEffect = $options['hover_style'] ?? 'none';

        $dropdownBg = $options['dropdown_bg'] ?? '';
        $dropdownColor = $options['dropdown_color'] ?? '';
        $dropdownHoverBg = $options['dropdown_hover_bg'] ?? '';
        $dropdownHoverColor = $options['dropdown_hover_color'] ?? '';
        $dropdownPadding = $options['dropdown_padding'] ?? '';

        $styles = [];

        // Base Link Styles
        $linkStyles = [];
        if ($textColor) $linkStyles[] = "color: {$textColor} !important";
        if ($fontSize) $linkStyles[] = "font-size: {$fontSize}px !important";
        if ($fontWeight) $linkStyles[] = "font-weight: {$fontWeight} !important";

        if ($paddingX !== '') {
            $linkStyles[] = "padding-left: {$paddingX}px !important";
            $linkStyles[] = "padding-right: {$paddingX}px !important";
        }
        if ($paddingY !== '') {
            $linkStyles[] = "padding-top: {$paddingY}px !important";
            $linkStyles[] = "padding-bottom: {$paddingY}px !important";
        }

        if (!empty($linkStyles)) {
            $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .nav-link.dropdown-trigger, #{$uniqueId} .mobile-nav-link { " . implode('; ', $linkStyles) . "; }";
        }

        // Parent Hover Styles
        $hoverStyles = [];
        if ($hoverTextColor) $hoverStyles[] = "color: {$hoverTextColor} !important";
        if ($hoverBgColor) $hoverStyles[] = "background-color: {$hoverBgColor} !important";

        if (!empty($hoverStyles)) {
            $styles[] = "#{$uniqueId} .nav-link:hover, #{$uniqueId} .nav-link:focus, #{$uniqueId} .nav-dropdown:hover > .nav-link, #{$uniqueId} .mobile-nav-link:hover { " . implode('; ', $hoverStyles) . "; }";
        }

        if ($hoverTextColor) {
            // Apply Main Menu Active Color to Submenu Parent when its Dropdown is Open (hovering the item)
            $styles[] = "#{$uniqueId} .nav-dropdown-item:hover > .submenu-parent, #{$uniqueId} .nav-submenu-item:hover > .submenu-parent { color: {$hoverTextColor} !important; }";
        }

        // Hide Dropdown Icon
        if (!empty($options['hide_dropdown_icon'])) {
            $styles[] = "#{$uniqueId} .nav-link > .bi-chevron-down { display: none !important; }";
        }

        // Vertical Menu Styles
        if (($options['menu_style'] ?? 'horizontal') === 'vertical') {
            $styles[] = "#{$uniqueId} {
                display: block !important;
                position: relative;
                width: 100%;
                max-width: 300px; /* Typical width */
            }";

            // The list container
            $bg = $dropdownBg ?? '#ffffff';
            $styles[] = "#{$uniqueId} .vertical-menu-list {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: {$bg};
                border: 1px solid #e5e5e5;
                border-top: none;
                border-radius: 0.375rem;
                z-index: 1000;
                display: none;
                flex-direction: column;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }";

            // Initially Open
            if (!empty($options['initially_open'])) {
                $styles[] = "#{$uniqueId} .vertical-menu-list { display: flex; }";
            } else {
                // Show on Hover of parent
                $styles[] = "#{$uniqueId}:hover .vertical-menu-list { display: flex; }";
            }

            $styles[] = "#{$uniqueId} .nav-dropdown {
                display: block;
                width: 100%;
                position: relative;
            }";

            $py = $paddingY ?: '10';
            $px = $paddingX ?: '15';

            // Boost specificity to override generic styles for Vertical Menu items specifically
            $styles[] = "#{$uniqueId} .nav-link {
                display: flex !important;
                color: var(--header-text-color) !important;
                justify-content: space-between;
                width: 100%;
                padding: {$py}px {$px}px !important;
            }";

            // Dropdowns (Flyout to the right)
            $styles[] = "#{$uniqueId} .nav-dropdown-menu {
                top: 0 !important;
                left: 100% !important;
                margin-top: 0 !important;
                min-height: 100%;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
             }";

            // Correct Spike Position for Vertical Menu (Left side)
            $styles[] = "#{$uniqueId} .nav-dropdown-menu::before {
                top: 12px !important;
                left: -6px !important;
                border-top: none !important;
                border-right: none !important;
                border-bottom: 1px solid rgba(0,0,0,0.03) !important;
                border-left: 1px solid rgba(0,0,0,0.03) !important;
            }";
        }

        // Special Hover Effects
        $effectColor = $hoverTextColor ?: 'currentColor';

        switch ($hoverEffect) {
            case 'underline':
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { position: relative; }";
                $styles[] = "#{$uniqueId} .nav-link::after, #{$uniqueId} .mobile-nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0; background-color: {$effectColor}; transition: width 0.5s; }";
                $styles[] = "#{$uniqueId} .nav-link:hover::after, #{$uniqueId} .mobile-nav-link:hover::after { width: 100%; }";
                break;
            case 'border_top':
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { position: relative; }";
                $styles[] = "#{$uniqueId} .nav-link::before, #{$uniqueId} .mobile-nav-link::before { content: ''; position: absolute; width: 0; height: 2px; top: 0; left: 0; background-color: {$effectColor}; transition: width 0.5s; }";
                $styles[] = "#{$uniqueId} .nav-link:hover::before, #{$uniqueId} .mobile-nav-link:hover::before { width: 100%; }";
                break;
            case 'border_top_bottom':
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { position: relative; }";
                $styles[] = "#{$uniqueId} .nav-link::before, #{$uniqueId} .mobile-nav-link::before { content: ''; position: absolute; width: 0; height: 2px; top: 0; left: 0; background-color: {$effectColor}; transition: width 0.5s; }";
                $styles[] = "#{$uniqueId} .nav-link::after, #{$uniqueId} .mobile-nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; right: 0; background-color: {$effectColor}; transition: width 0.5s; }";
                $styles[] = "#{$uniqueId} .nav-link:hover::before, #{$uniqueId} .mobile-nav-link:hover::before { width: 100%; }";
                $styles[] = "#{$uniqueId} .nav-link:hover::after, #{$uniqueId} .mobile-nav-link:hover::after { width: 100%; }";
                break;
            case 'background':
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { border-radius: 50rem; transition: background-color 0.2s; }";
                break;
            case 'background_rounded':
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { border-radius: 0.25rem; transition: background-color 0.2s; }";
                break;
            case 'glow':
                $glowColor = $hoverBgColor ?: 'rgba(0,0,0,0.1)';
                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { border-radius: 0.5rem; transition: all 0.3s ease; }";
                $styles[] = "#{$uniqueId} .nav-link:hover, #{$uniqueId} .nav-link:focus, #{$uniqueId} .nav-dropdown:hover > .nav-link, #{$uniqueId} .mobile-nav-link:hover { box-shadow: 0 0 15px {$glowColor}; }";
                break;
            case 'parallelogram':
                $paraBg = $hoverBgColor ?: 'transparent';
                // We prevent the default hover BG from applying to the main element so it doesn't look square
                // But we can't easily undo the previous rule without complex specificity or !important overrides.
                // Actually, CSS order matters. If we add a rule here with higher specificity or !important, it overrides.
                if ($hoverBgColor) {
                    $styles[] = "#{$uniqueId} .nav-link:hover, #{$uniqueId} .nav-link:focus, #{$uniqueId} .nav-dropdown:hover > .nav-link, #{$uniqueId} .mobile-nav-link:hover { background-color: transparent !important; }";
                }

                $styles[] = "#{$uniqueId} .nav-link, #{$uniqueId} .mobile-nav-link { position: relative; z-index: 1; margin: 0 5px; }";
                $styles[] = "#{$uniqueId} .nav-link::before, #{$uniqueId} .mobile-nav-link::before { content: ''; position: absolute; top:0; left:0; right:0; bottom:0; background-color: {$paraBg}; transform: skew(-20deg) scaleX(0); transform-origin: center; transition: transform 0.3s ease; z-index: -1; border-radius: 4px; opacity: 0; }";
                $styles[] = "#{$uniqueId} .nav-link:hover::before, #{$uniqueId} .nav-link:focus::before, #{$uniqueId} .nav-dropdown:hover > .nav-link::before, #{$uniqueId} .mobile-nav-link:hover::before { transform: skew(-20deg) scaleX(1); opacity: 1; }";
                break;
        }

        // Dropdown Styles
        if ($dropdownBg || $dropdownPadding) {
            $dropStyles = [];
            if ($dropdownBg) $dropStyles[] = "background-color: {$dropdownBg} !important";
            if ($dropdownPadding) $dropStyles[] = "padding: {$dropdownPadding}px !important";
            $styles[] = "#{$uniqueId} .nav-dropdown-menu, #{$uniqueId} .nav-submenu, #{$uniqueId} .mobile-dropdown-content, #{$uniqueId} .mobile-sub-nav-list { " . implode('; ', $dropStyles) . "; }";
        }

        // Dropdown Items
        $dropItemStyles = [];
        // Fallback Logic
        $fallbackColor = $options['section_text_color'] ?? '';
        $navColor = $textColor ?: $fallbackColor;
        $dc = $dropdownColor ?: $navColor;

        if ($dc) {
            $dropItemStyles[] = "color: {$dc} !important";
        }

        if (!empty($dropItemStyles)) {
            $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link, #{$uniqueId} .mobile-sub-list > a { " . implode('; ', $dropItemStyles) . "; }";
            // Ensure arrow icon inherits color
            $styles[] = "#{$uniqueId} .nav-dropdown-item > a i, #{$uniqueId} .nav-submenu-item > a i, #{$uniqueId} .mobile-dropdown-link i { color: inherit !important; }";
        }

        // Dropdown Item Hover
        $dropHoverStyles = [];
        $dropdownHoverStyle = $options['dropdown_hover_style'] ?? 'none';

        // Basic color changes only applied if style is none, OR if we want custom colors on top of styles
        // But some styles like background_rounded handle their own BG.

        // Apply text color always if set
        if ($dropdownHoverColor) {
            $dropHoverStyles[] = "color: {$dropdownHoverColor} !important";
        }

        // Apply BG Color if "none" style (traditional)
        if ($dropdownHoverBg && $dropdownHoverStyle === 'none') {
            $dropHoverStyles[] = "background-color: {$dropdownHoverBg} !important";
        }

        if (!empty($dropHoverStyles)) {
            $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover, #{$uniqueId} .nav-submenu-item > a:hover, #{$uniqueId} .mobile-dropdown-link:hover, #{$uniqueId} .mobile-sub-list > a:hover { " . implode('; ', $dropHoverStyles) . "; }";
            // Keep parent active (using Dropdown Hover styles) when hovering submenu
            $styles[] = "#{$uniqueId} .nav-dropdown-item:hover > .submenu-parent, #{$uniqueId} .nav-submenu-item:hover > .submenu-parent { " . implode('; ', $dropHoverStyles) . "; }";
        }

        // Special Dropdown Hover Effects
        $dropEffectColor = $dropdownHoverColor ?: 'currentColor';

        switch ($dropdownHoverStyle) {
            case 'underline':
                // Set relative positioning
                // We target specific link classes to ensure position relative is applied to the exact element needing it
                // Adding 'span' support for complex mega menu items where text might be wrapped
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link, #{$uniqueId} .nav-dropdown-link, #{$uniqueId} .nav-submenu-link { position: relative !important; width: fit-content; min-width: 100%; display: block; }";

                // 1. If click mode is active, handle 'is-open' state (Avoid forcing display:block so Grid mega menus work)
                $styles[] = "#{$uniqueId} .nav-dropdown.is-open > .nav-dropdown-menu { opacity: 1 !important; visibility: visible !important; transform: translateY(0) !important; pointer-events: auto !important; }";

                // 2. Disable default hover effect if trigger is set to click (conflicting with app.css base styles)
                $styles[] = "#{$uniqueId} .nav-dropdown[data-trigger-type='click']:hover > .nav-dropdown-menu { opacity: 0; visibility: hidden; transform: translateY(10px); }";

                // 3. Re-enable if it IS open (so hovering doesn't hide it)
                $styles[] = "#{$uniqueId} .nav-dropdown[data-trigger-type='click'].is-open:hover > .nav-dropdown-menu { opacity: 1 !important; visibility: visible !important; transform: translateY(0) !important; }";

                if (!empty($options['show_dropdown_border'])) {
                    // Full width underline that overlaps the item border (Using bottom:0 to align with ::before border)
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a::after, #{$uniqueId} .nav-submenu-item > a::after, #{$uniqueId} .mobile-dropdown-link::after, #{$uniqueId} .nav-dropdown-link::after { content: ''; position: absolute; width: 0; height: 1px; bottom: 0; left: 0; background-color: {$dropEffectColor}; transition: width 0.5s ease-out; z-index: 10; }";
                    // Expand width on hover to 100%
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover::after, #{$uniqueId} .nav-submenu-item > a:hover::after, #{$uniqueId} .mobile-dropdown-link:hover::after, #{$uniqueId} .nav-dropdown-link:hover::after { width: 100%; }";
                } else {
                    // Standard floating underline (inset)
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a::after, #{$uniqueId} .nav-submenu-item > a::after, #{$uniqueId} .mobile-dropdown-link::after, #{$uniqueId} .nav-dropdown-link::after { content: ''; position: absolute; width: 0; height: 1px; bottom: 4px; left: 1rem; background-color: {$dropEffectColor}; transition: width 0.5s ease-out; }";
                    // Expand width on hover (inset)
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover::after, #{$uniqueId} .nav-submenu-item > a:hover::after, #{$uniqueId} .mobile-dropdown-link:hover::after, #{$uniqueId} .nav-dropdown-link:hover::after { width: calc(100% - 2rem); }";
                }

                // Remove default background on hover since we are using underline
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover, #{$uniqueId} .nav-submenu-item > a:hover, #{$uniqueId} .mobile-dropdown-link:hover { background-color: transparent !important; color: {$dropEffectColor} !important; }";
                break;

            case 'background_rounded':
                // Add margins to create spacing for the rounded effect
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link { margin: 2px 8px; border-radius: 6px; transition: background-color 0.2s; width: auto; }";
                if ($dropdownHoverBg) {
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover, #{$uniqueId} .nav-submenu-item > a:hover, #{$uniqueId} .mobile-dropdown-link:hover { background-color: {$dropdownHoverBg} !important; }";
                }
                break;

            case 'background_pill':
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link { margin: 2px 8px; border-radius: 50rem; transition: background-color 0.2s; width: auto; }";
                if ($dropdownHoverBg) {
                    $styles[] = "#{$uniqueId} .nav-dropdown-item > a:hover, #{$uniqueId} .nav-submenu-item > a:hover, #{$uniqueId} .mobile-dropdown-link:hover { background-color: {$dropdownHoverBg} !important; }";
                }
                break;
        }

        // Dropdown Item Bottom Border
        if (!empty($options['show_dropdown_border'])) {
            // Calculate border color based on text color ($dc) with 5% opacity
            $borderColor = 'rgba(0, 0, 0, 0.15)'; // Fallback

            if (!empty($dc)) {
                if (str_starts_with($dc, '#')) {
                    $hex = ltrim($dc, '#');
                    if (strlen($hex) == 3) {
                        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                    }
                    if (strlen($hex) == 6) {
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                        $borderColor = "rgba({$r}, {$g}, {$b}, 0.15)";
                    }
                } elseif (str_starts_with($dc, 'rgb')) {
                    // Use CSS color-mix for modern browsers if input is already rgb/rgba
                    $borderColor = "color-mix(in srgb, {$dc}, transparent 95%)";
                }
            }

            // Apply border to the Link (A) instead of Item (LI) to ensure underline overlap matches perfectly
            // SAFETY: Remove any potential wrapper borders
            $styles[] = "#{$uniqueId} .nav-dropdown-item, #{$uniqueId} .nav-submenu-item { border: none !important; }";

            // Prevent Top/Side borders on Links to avoid double-line issues on First Item
            $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link { border-top: none !important; border-left: none !important; border-right: none !important; }";

            // Bootstrap's .dropdown-menu class applies 'display: block', breaking the 'display: grid' layout of mega menus.
            // We must force 'display: grid' back with !important when these specific classes are present.
            $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-2col { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; }";
            $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-3col { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; }";
            $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-4col { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; }";
            $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-full { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; }";

            // If Underline Style is used, we use ::before for the border to ensure exact overlap with ::after
            if (($options['dropdown_hover_style'] ?? 'none') === 'underline') {
                // We target ANY anchor inside these containers to catch mega menu links too.
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link, #{$uniqueId} .nav-dropdown-link, #{$uniqueId} .nav-submenu-link { position: relative !important; display: flex; width: 100%; }";

                // STATIC BOTTOM BORDER (::before)
                // - Only applied if not disabled by specific last-child rules
                // - z-index: 1 (Lowest)
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a::before, #{$uniqueId} .nav-submenu-item > a::before, #{$uniqueId} .mobile-dropdown-link::before, #{$uniqueId} .nav-dropdown-link::before { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background-color: {$borderColor}; z-index: 1; pointer-events: none; }";

                // ANIMATED HOVER UNDERLINE (::after)
                // - We ensure it sits ON TOP of the static border
                // - z-index: 10 (Higher)
                // - We assume the 'buildMenuStyles' generated earlier already added the basic ::after, but we reinforce z-index here.
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a::after, #{$uniqueId} .nav-submenu-item > a::after, #{$uniqueId} .mobile-dropdown-link::after, #{$uniqueId} .nav-dropdown-link::after { z-index: 10 !important; bottom: 0 !important; }";

                // 1. Dropdown Menus (Standard) - Last item should have NO static border
                $styles[] = "#{$uniqueId} .nav-dropdown-menu > .nav-dropdown-item:last-child > a::before { display: none !important; content: none !important; }";

                // Mega Menu 2-Col: Remove border for last 2 items (approximate bottom row)
                $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-2col > .nav-dropdown-item:nth-last-child(-n+2) > a::before { display: none !important; content: none !important; }";

                // Mega Menu 3-Col: Remove border for last 3 items
                $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-3col > .nav-dropdown-item:nth-last-child(-n+3) > a::before { display: none !important; content: none !important; }";

                // Mega Menu 4-Col: Remove border for last 4 items
                $styles[] = "#{$uniqueId} .nav-dropdown-menu.mega-menu-4col > .nav-dropdown-item:nth-last-child(-n+4) > a::before { display: none !important; content: none !important; }";
                // 2. Submenus - Last item should have NO border
                $styles[] = "#{$uniqueId} .nav-submenu > .nav-submenu-item:last-child > a::before { display: none !important; content: none !important; }";
                // 3. Mobile
                // Corrected selector to match 'mobile-nav-product' used in menu-mobile.blade.php
                $styles[] = "#{$uniqueId} .mobile-nav-product:last-child > .mobile-dropdown-link::before { display: none !important; }";
                $styles[] = "#{$uniqueId} .mobile-sub-list:last-child > .mobile-dropdown-link::before { display: none !important; }"; // For submenu items

                // Reset actual border property
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link, #{$uniqueId} .nav-dropdown-link, #{$uniqueId} .nav-submenu-link { border-bottom: none !important; }";
            } else {
                // Standard CSS Border
                $styles[] = "#{$uniqueId} .nav-dropdown-item > a, #{$uniqueId} .nav-submenu-item > a, #{$uniqueId} .mobile-dropdown-link { border-bottom: 1px solid {$borderColor}; }";

                // Remove for last child
                $styles[] = "#{$uniqueId} .nav-dropdown-menu.dropdown-standard > .nav-dropdown-item:last-child > a { border-bottom: none !important; }";
                $styles[] = "#{$uniqueId} .nav-dropdown-menu > .nav-dropdown-item:last-child > a, #{$uniqueId} .nav-submenu > .nav-submenu-item:last-child > a { border-bottom: none !important; }";
                $styles[] = "#{$uniqueId} .mobile-nav-item:last-child > .mobile-dropdown-link { border-bottom: none !important; }";
            }

            // Vertical Menu Dropdowns should be full width with bottom borders by default for better separation
            if (($options['menu_style'] ?? 'horizontal') === 'vertical') {
                $styles[] = "#{$uniqueId} .nav-dropdown {
                    border-bottom: 1px solid {$borderColor};
                }";

                $styles[] = "#{$uniqueId} .nav-dropdown:last-child {
                    border-bottom: none;
                }";
            }
        }

        /* Hide Dropdown Arrows
        if (!empty($options['hide_dropdown_icon'])) {
            $styles[] = "#{$uniqueId} .bi-chevron-down, #{$uniqueId} .dropdown-arrow, #{$uniqueId} .mobile-arrow { display: none !important; }";
        }*/

        // Desktop Visual Improvements (Rounded Edges & Spike)
        $spikeColor = $dropdownBg ?: '#ffffff';
        $spikeBorder = !empty($options['show_dropdown_border']) && isset($borderColor) ? $borderColor : 'rgba(0,0,0,0.03)';

        $styles[] = "@media (min-width: 992px) {
            #{$uniqueId} .nav-dropdown-menu {
                margin-top: 12px !important;
            }
            #{$uniqueId} .nav-dropdown-menu::before {
                content: '';
                position: absolute;
                top: -6px;
                left: 24px;
                width: 12px;
                height: 12px;
                background-color: {$spikeColor};
                transform: rotate(45deg);
                border-top: 1px solid {$spikeBorder};
                border-left: 1px solid {$spikeBorder};
                z-index: 0;
            }
            /* Bridge to prevent closing on hover of the gap */
            #{$uniqueId} .nav-dropdown-menu::after {
                content: '';
                position: absolute;
                top: -20px;
                left: 0;
                width: 100%;
                height: 20px;
                background: transparent;
                z-index: -1;
            }
             /* Ensure content sits above spike background but below text */
            #{$uniqueId} .nav-dropdown-menu > * { position: relative; z-index: 1; }
        }";

        // Trigger Type Support (Frontend class)

        if (($options['trigger_type'] ?? 'hover') === 'click') {
            // Disable hover effect
            $styles[] = "#{$uniqueId} .nav-dropdown:hover > .nav-dropdown-menu { opacity: 0; visibility: hidden; }";

            // Apply base transition styles for Desktop
            $styles[] = "@media (min-width: 992px) {
                #{$uniqueId} .nav-dropdown-menu {
                    display: block !important; /* Force display block to allow opacity transition */
                    opacity: 0;
                    visibility: hidden;
                    pointer-events: none;
                    transform: translateY(15px);
                    transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
                }
                #{$uniqueId} .nav-dropdown-menu.show {
                    opacity: 1 !important;
                    visibility: visible !important;
                    pointer-events: auto !important;
                    transform: translateY(0) !important;
                }
            }";
        } else {
            // We ensure pointer-events are disabled when hidden so large invisible grids don't block interaction
            // Added translateY and transition for smoother entrance
            $styles[] = "@media (min-width: 992px) { #{$uniqueId} .nav-dropdown-menu { visibility: hidden; opacity: 0; pointer-events: none; transform: translateY(15px); transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1); } #{$uniqueId} .nav-dropdown:hover > .nav-dropdown-menu { visibility: visible; opacity: 1; pointer-events: auto; transform: translateY(0); } }";
        }

        return implode(' ', $styles);
    }
}
