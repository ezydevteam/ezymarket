<?php

namespace App\View\Composers;

use App\Enums\Menu\MenuLocation;
use App\Classes\{BuilderBlocks, GoogleFonts};
use App\Models\Appearance\Menu;
use App\Models\Settings;
use Illuminate\View\View;

/**
 * FooterComposer
 *
 * Prepares all data needed for the footer layout view,
 * keeping the Blade template clean and focused on presentation.
 */
class FooterComposer
{
    /**
     * Static cache for computed data to avoid redundant processing
     */
    protected static array $cache = [];

    /**
     * Section definitions with defaults
     */
    protected array $sectionDefs = [
        'footer_widget_section' => [
            'class' => 'footer-widget-section',
            'defaultBg' => '#1e293b',
            'defaultText' => '#e2e8f0',
            'defaultPadding' => 'py-5'
        ],
        'footer_menu_section' => [
            'class' => 'footer-menu-section',
            'defaultBg' => '#1e293b',
            'defaultText' => '#e2e8f0',
            'defaultPadding' => 'py-3'
        ],
        'footer_bottom_section' => [
            'class' => 'footer-bottom-section',
            'defaultBg' => '#0f172a',
            'defaultText' => '#94a3b8',
            'defaultPadding' => 'py-3'
        ]
    ];

    /**
     * Default footer layout configuration
     */
    protected array $defaultLayout = [
        [
            'id' => 'footer_widget_section',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 3, 'blocks' => [['id' => 'footer_logo', 'title' => 'Footer Logo', 'options' => []], ['id' => 'footer_about', 'title' => 'About Text', 'options' => []]]],
                ['width' => 3, 'blocks' => [['id' => 'footer_newsletter', 'title' => 'Footer Newsletter', 'options' => []]]],
                ['width' => 3, 'blocks' => [['id' => 'footer_contact', 'title' => 'Contact Info', 'options' => []]]],
                ['width' => 3, 'blocks' => [['id' => 'footer_social', 'title' => 'Social Icons', 'options' => []]]]
            ]
        ],
        [
            'id' => 'footer_menu_section',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 12, 'blocks' => [['id' => 'footer_menu', 'title' => 'Footer Menu', 'options' => []]]]
            ]
        ],
        [
            'id' => 'footer_bottom_section',
            'options' => ['enabled' => true],
            'columns' => [
                ['width' => 4, 'blocks' => [['id' => 'footer_copyright', 'title' => 'Copyright Text', 'options' => []]]],
                ['width' => 8, 'blocks' => [['id' => 'footer_payment_icons', 'title' => 'Payment Icons', 'options' => []]]]
            ]
        ]
    ];

    /**
     * Bind data to the view.
     * This method is called by the framework when the view is rendered.
     * It prepares all necessary data for the footer layout, including sections, menus, and fonts, and caches it for performance.
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        if (empty(self::$cache)) {
            $footerLayout = $this->getFooterLayout();

            self::$cache = [
                'footerSections' => $this->buildSections($footerLayout),
                'footerFontsLink' => $this->getGoogleFontsLink($footerLayout),
                'footerMenus' => $this->getFooterMenus()
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
     * Get footer menus with children
     */
    protected function getFooterMenus()
    {
        return Menu::getByLocation(MenuLocation::FOOTER);
    }

    /**
     * Get footer layout from settings
     */
    protected function getFooterLayout(): array
    {
        $footerLayoutRaw = Settings::where('key', 'theme_footer')->value('value');

        // Always convert to array (handles JSON string, stdClass, or already array)
        if (is_string($footerLayoutRaw)) {
            $footerLayout = json_decode($footerLayoutRaw, true);
        } elseif (is_object($footerLayoutRaw) || is_array($footerLayoutRaw)) {
            $footerLayout = json_decode(json_encode($footerLayoutRaw), true);
        } else {
            $footerLayout = null;
        }

        if (empty($footerLayout) || !is_array($footerLayout)) {
            return $this->defaultLayout;
        }

        return $footerLayout;
    }

    /**
     * Build sections with all computed properties
     */
    protected function buildSections(array $footerLayout): array
    {
        $sections = [];

        foreach ($this->sectionDefs as $sectionId => $sectionDef) {
            $section = $this->findSection($footerLayout, $sectionId);

            if (!$section) {
                continue;
            }

            // Convert snake_case ID to kebab-case for HTML attributes
            $htmlId = str_replace('_', '-', $sectionId);

            $options = $section['options'] ?? [];
            $isEnabled = ($options['enabled'] ?? true) !== false;

            if (!$isEnabled) {
                continue;
            }

            $container = $this->buildContainerClass($options);
            $columns = $section['columns'] ?? [];
            $columnCount = count($columns);

            $columnGap = $options['gap_between_columns'] ?? 'gap-3 gap-lg-4';
            $blockGap = $options['gap_between_blocks'] ?? 'gap-3';

            // Get colors for CSS variables
            $bgColor = $options['bg_color'] ?? $sectionDef['defaultBg'];
            $textColor = $options['text_color'] ?? $sectionDef['defaultText'];

            // Determine if default utility classes should be removed in favor of custom styles
            $hasCustomPadding = !empty($options['padding_top']) || !empty($options['padding_bottom']) || !empty($options['padding_left']) || !empty($options['padding_right']);
            $paddingClass = $hasCustomPadding ? '' : ($options['padding'] ?? $sectionDef['defaultPadding']);

            $hasCustomBorder = ($options['border_style'] ?? 'none') !== 'none';
            $borderClass = $hasCustomBorder ? '' : '';

            // Build custom styles string
            $customStyles = $this->buildCustomStyles($options);
            $rowStyles = $this->buildSectionStyles($options);

            $mobileColReverse = $options['mobile_col_reverse'] ?? false;

            $isBottomSection = $sectionId === 'footer_bottom_section';
            $directionClass = $isBottomSection ? 'flex-row align-items-center flex-wrap' : 'flex-column';
            // Bottom section: w-auto lets blocks size to content
            // Other sections: w-100 makes blocks stack vertically full-width
            $blockWidthClass = $isBottomSection ? 'w-auto' : 'w-100';

            // Build section data
            $sectionData = [
                'id' => $htmlId,
                'class' => $sectionDef['class'],
                'container' => $container,
                'columns' => [],
                'isBottomSection' => $isBottomSection,
                'mobileColReverse' => $mobileColReverse,
                'border' => $borderClass,
                'padding' => $paddingClass,
                'columnGap' => $columnGap,
                'blockGap' => $blockGap,
                'bgColor' => $bgColor,
                'textColor' => $textColor,
                'customStyles' => $customStyles,
                'rowStyles' => $rowStyles,
                'directionClass' => $directionClass,
                'blockWidthClass' => $blockWidthClass
            ];

            // Build CSS string
            $sectionCss = "#{$htmlId}{background-color:{$bgColor};color:{$textColor};{$customStyles}}#{$htmlId} .footer-inner{{$rowStyles}}";

            // Build columns with computed properties
            foreach ($columns as $colIndex => $col) {
                $blocks = $col['blocks'] ?? [];
                $blockCount = count($blocks);

                // Compute flex-grow
                if (isset($col['flexGrow'])) {
                    $flexGrow = (int)$col['flexGrow'];
                } else {
                    $flexGrow = $columnCount > 1 ? max(1, $blockCount) : 1;
                }

                // Compute alignment from User Setting (Builder)
                $align = $col['align'] ?? 'start';
                $alignClass = match ($align) {
                    'center' => 'text-center align-items-center',
                    'end', 'right' => 'text-end align-items-end',
                    default => 'text-start align-items-start'
                };

                // For bottom section which uses flex-row on columns
                if ($isBottomSection) {
                    $alignClass = str_replace('align-items-', 'justify-content-', $alignClass);
                }

                // CSS for this column
                $colCssId = "#{$htmlId}-col-{$colIndex}";
                if ($columnCount == 1) {
                    $sectionCss .= "{$colCssId}{flex:0 0 100% !important;width:100% !important;max-width:100% !important;}";
                } elseif ($isBottomSection) {
                    $sectionCss .= "{$colCssId}{flex:0 0 auto;}";
                } else {
                    $sectionCss .= "{$colCssId}{flex:{$flexGrow} 0 0%;min-width:20%;}";
                }

                // Build blocks with resolved view paths
                $resolvedBlocks = [];
                foreach ($blocks as $block) {
                    $blockId = $block['id'] ?? '';

                    if (!$blockId) {
                        continue;
                    }

                    // Filter out inactive blocks
                    $status = $block['status'] ?? $block['options']['is_active'] ?? 1;
                    if ((int)$status === 0) {
                        continue;
                    }

                    // Use BuilderBlocks to strip semantic prefixes
                    $cleanId = BuilderBlocks::getViewName($blockId, 'footer_');

                    // Convert snake_case ID to kebab-case filename
                    $viewName = str($cleanId)->replace('_', '-');
                    $view = 'blocks.footer.' . $viewName;

                    if (!theme_view_exists($view)) {
                        continue;
                    }

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

                    // Generate Unique ID for the block
                    $uniqueBlockId = $htmlId . '-col-' . $colIndex . '-block-' . count($resolvedBlocks) . '-unique';
                    $block['options']['uniqueId'] = $uniqueBlockId;

                    // Prepare Block Data
                    $method = match ($cleanId) {
                        'links'         => 'prepareLinksData',
                        'logo'          => 'prepareLogoData',
                        'about'         => 'prepareAboutData',
                        'contact'       => 'prepareContactData',
                        'copyright'     => 'prepareCopyrightData',
                        'html'          => 'prepareHtmlData',
                        'button'        => 'prepareButtonData',
                        'search'        => 'prepareSearchData',
                        'language'      => 'prepareLanguageData',
                        'social'        => 'prepareSocialData',
                        'newsletter'    => 'prepareNewsletterData',
                        'payment_icons' => 'preparePaymentIconsData',
                        'menu'          => 'prepareMenuData',
                        'countdown'     => 'prepareCountdownData',
                        'divider'       => 'prepareDividerData',
                        default         => null,
                    };

                    if ($method) {
                        $block['options'] = array_merge(
                            $block['options'],
                            $this->{$method}($uniqueBlockId, $block['options'])
                        );
                    }

                    // Generate Block Styles
                    $method = match ($cleanId) {
                        'links'         => 'buildLinksStyles',
                        'logo'          => 'buildLogoStyles',
                        'menu'          => 'buildMenuStyles',
                        'payment_icons' => 'buildPaymentIconsStyles',
                        'social'        => 'buildSocialStyles',
                        'divider'       => 'buildDividerStyles',
                        default         => null,
                    };

                    if ($method) {
                        $sectionCss .= $this->{$method}($uniqueBlockId, $block['options']);
                    }

                    // Prepare Title Data
                    $titleData = null;
                    if (!empty($block['options']['show_title'])) {
                        $tSize = $block['options']['title_size'] ?? 'h6';
                        $tAlign = $block['options']['title_align'] ?? 'start';
                        $tTransform = $block['options']['title_transform'] ?? '';
                        $tColor = $block['options']['title_color'] ?? '';
                        $tBorderBottom = $block['options']['show_border_bottom'] ?? false;

                        $titleId = $htmlId . '-col-' . $colIndex . '-block-' . count($resolvedBlocks) . '-title';
                        $tClasses = "footer-title fw-bold mb-2 $tSize text-$tAlign" . ($tTransform ? " text-$tTransform" : "");

                        // Title CSS
                        if (!empty($tColor) || $tBorderBottom) {
                            $sectionCss .= "#{$titleId}{";
                            if (!empty($tColor)) {
                                $sectionCss .= "color:{$tColor} !important;";
                            }
                            if ($tBorderBottom) {
                                $sectionCss .= "border-bottom:2px solid currentColor !important;padding-bottom:0.25rem !important;margin-bottom:0.5rem !important;max-width:fit-content;";
                            }
                            $sectionCss .= "}";
                        }

                        $titleData = [
                            'id' => $titleId,
                            'tag' => $tSize, // h6, h5
                            'classes' => $tClasses,
                            'color' => $tColor,
                            'border' => $tBorderBottom,
                            'text' => $block['title'] ?? ''
                        ];
                    }

                    $resolvedBlocks[] = [
                        'id' => $blockId,
                        'view' => $view,
                        'title' => $block['title'] ?? '',
                        'titleData' => $titleData,
                        'options' => $block['options'] ?? [],
                        'wrapperClass' => $visibilityClass
                    ];
                }

                $sectionData['columns'][] = [
                    'flexGrow' => $flexGrow,
                    'alignClass' => $alignClass,
                    'blocks' => $resolvedBlocks,
                ];
            }

            // Mobile Media Queries
            $params = [
                'colDirection' => $mobileColReverse ? 'column-reverse' : 'column',
                'contentAlign' => $isBottomSection ? 'center' : 'flex-start',
                'textAlign' => $isBottomSection ? 'center' : 'left'
            ];

            $sectionCss .= "@media (max-width: 991px){";
            $sectionCss .= "#{$htmlId} .footer-inner{flex-direction:{$params['colDirection']} !important;overflow:hidden !important;}";
            $sectionCss .= "#{$htmlId} .footer-col{flex:0 0 100% !important;flex-wrap:wrap !important;justify-content:{$params['contentAlign']} !important;text-align:{$params['textAlign']} !important;width:100% !important;max-width:100% !important;box-sizing:border-box;word-wrap:break-word;overflow-wrap:break-word;}";
            $sectionCss .= "#{$htmlId} .footer-col img{max-width:100% !important;height:auto !important;display:block;object-fit:contain;}";
            $sectionCss .= "#{$htmlId} .footer-col > *{max-width:100% !important;}";
            $sectionCss .= "}";

            $sectionData['css'] = minifyCss($sectionCss);
            $sections[] = $sectionData;
        }

        return $sections;
    }

    /**
     * Find section by ID in layout
     */
    protected function findSection(array $footerLayout, string $sectionId): ?array
    {
        foreach ($footerLayout as $section) {
            if (($section['id'] ?? '') === $sectionId) {
                return $section;
            }
        }
        return null;
    }

    /**
     * Compute alignment class for a column
     */
    protected function computeAlignment(int $colIndex, int $columnCount, string $middleAlign): string
    {
        // First column - left aligned
        if ($colIndex === 0) {
            return 'text-start';
        }

        // Last column - right aligned on large screens
        if ($colIndex === $columnCount - 1) {
            return 'text-lg-end';
        }

        // Middle columns in 3+ col layout
        if ($columnCount >= 3) {
            return match ($middleAlign) {
                'center' => 'text-center',
                'right' => 'text-end',
                default => 'text-start'
            };
        }

        return '';
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
     * Helper to add style with unit
     */
    protected function addStyleUnit(array &$styles, string $property, $value, string $unit = 'px'): void
    {
        if ($value !== null && $value !== '') {
            $styles[] = "{$property}: {$value}{$unit} !important";
        }
    }

    /*** Prepare Data for Links Block
     */
    protected function prepareLinksData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'links' => $options['links'] ?? [],
            'linkDisplay' => $options['link_display'] ?? 'vertical',
            'linkStyle' => $options['link_style'] ?? 'bullet',
            'target' => $options['link_target'] ?? '_self',
        ];
    }

    /**
     * Prepare Data for Social Block
     */
    protected function prepareSocialData(string $uniqueId, array $options): array
    {
        $socialLinks = settings('social_links') ?? [];

        $iconStyle = $options['icon_style'] ?? 'rounded';
        $iconColor = $options['icon_color'] ?? 'monochrome';
        $hoverEffect = $options['hover_effect'] ?? true;
        $showTooltip = $options['show_tooltip'] ?? false;

        $platforms = ['facebook', 'x', 'instagram', 'youtube', 'linkedin', 'pinterest'];
        $preparedSocials = [];

        foreach ($platforms as $platform) {
            if (!empty($socialLinks->$platform)) {
                $url = match ($platform) {
                    'youtube' => "https://youtube.com/@{$socialLinks->youtube}",
                    'linkedin' => "https://linkedin.com/company/{$socialLinks->linkedin}",
                    default => "https://{$platform}.com/" . ($platform === 'x' ? $socialLinks->x : $socialLinks->$platform)
                };

                $iconClass = match ($platform) {
                    'x' => 'bi-twitter-x',
                    'facebook' => 'bi-facebook',
                    'instagram' => 'bi-instagram',
                    'youtube' => 'bi-youtube',
                    'linkedin' => 'bi-linkedin',
                    'pinterest' => 'bi-pinterest',
                    default => "bi-{$platform}"
                };

                $preparedSocials[] = [
                    'platform' => $platform,
                    'url' => $url,
                    'icon' => $iconClass,
                    'label' => ucfirst($platform)
                ];
            }
        }

        // Determine Base Classes
        $classes = ['d-inline-flex', 'align-items-center', 'justify-content-center', 'text-decoration-none', 'social-icon'];

        if ($iconStyle === 'plain') {
            $classes[] = 'p-0 border-0 fs-5';
            if ($iconColor === 'monochrome') {
                $classes[] = 'text-white';
            }
        } else {
            $classes[] = 'btn btn-sm';
            $classes[] = match ($iconStyle) {
                'square' => 'rounded-0',
                'circle' => 'rounded-circle',
                default => 'rounded',
            };
            if ($iconColor === 'monochrome') {
                $classes[] = 'btn-outline-light';
            } else {
                $classes[] = 'border-0 text-white';
            }
        }

        if ($hoverEffect) {
            $classes[] = 'social-hover-effect';
        }

        return [
            'uniqueId' => $uniqueId,
            'socials' => $preparedSocials,
            'iconStyle' => $iconStyle,
            'iconColor' => $iconColor,
            'showTooltip' => $showTooltip,
            'linkClass' => implode(' ', $classes),
            'gapClass' => ($iconStyle === 'plain') ? 'gap-3' : 'gap-2'
        ];
    }

    /**
     * Prepare Data for Newsletter Block
     */
    protected function prepareNewsletterData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'heading' => $options['heading'] ?? translate('Subscribe to Newsletter'),
            'subHeading' => $options['sub_heading'] ?? translate('Get the latest updates and offers.'),
            'headingAlign' => $options['heading_align'] ?? 'left',
            'style' => $options['style'] ?? 'default',
            'placeholder' => $options['placeholder'] ?? translate('Enter your email'),
            'buttonText' => $options['button_text'] ?? translate('Subscribe'),
            'buttonIcon' => $options['button_icon'] ?? 'bi-send',
            'buttonDisplay' => $options['button_display'] ?? 'text_only',
            'buttonStyle' => $options['button_style'] ?? 'primary',
            'showName' => $options['show_name'] ?? false,
            'namePlaceholder' => $options['name_placeholder'] ?? translate('Your Name'),
        ];
    }

    /**
     * Prepare Data for Payment Icons Block
     */
    protected function preparePaymentIconsData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'heading' => $options['heading'] ?? '',
            'paymentImage' => $options['payment_image'] ?? null,
        ];
    }

    /**
     * Prepare Data for About Block
     */
    protected function prepareAboutData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'aboutText' => $options['about_text'] ?? '',
        ];
    }

    /**
     * Prepare Data for Contact Block
     */
    protected function prepareContactData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'address' => $options['address'] ?? '',
            'phone' => $options['phone'] ?? '',
            'email' => $options['email'] ?? '',
            'moreInfo' => $options['more_info'] ?? '',
        ];
    }

    /**
     * Prepare Data for Copyright Block
     */
    protected function prepareCopyrightData(string $uniqueId, array $options): array
    {
        $siteName = settings('general')->site_name ?? config('app.name');
        $copyrightText = $options['copyright_text'] ?? '';
        $year = date('Y');

        if (!empty($copyrightText)) {
            $copyrightText = str_replace(
                ['{year}', '{site_name}'],
                [$year, $siteName],
                $copyrightText
            );
        } else {
            $copyrightText = '&copy; ' . $year . ' ' . $siteName . '. ' . translate('All rights reserved.');
        }

        return [
            'uniqueId' => $uniqueId,
            'copyrightText' => $copyrightText,
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
     * Prepare Data for Button Block
     */
    protected function prepareButtonData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'text' => $options['btn_text'] ?? translate('Button'),
            'url' => $options['url'] ?? '#',
            'style' => $options['style'] ?? 'primary',
            'size' => $options['size'] ?? 'md',
            'icon' => $options['icon'] ?? '',
            'iconPosition' => $options['icon_position'] ?? 'left',
            'target' => ($options['new_tab'] ?? false) ? '_blank' : '_self',
            'fullWidth' => $options['full_width'] ?? false,
        ];
    }

    /**
     * Prepare Data for Search Block
     */
    protected function prepareSearchData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'placeholder' => $options['placeholder'] ?? translate('Search...'),
            'buttonText' => $options['button_text'] ?? '',
            'buttonIcon' => $options['button_icon'] ?? 'bi-search',
            'buttonStyle' => $options['search_btn_style'] ?? 'primary',
            'showButton' => $options['show_button'] ?? true,
            'style' => $options['style'] ?? 'inline',
            'size' => $options['size'] ?? 'default',
        ];
    }

    /**
     * Prepare Data for Language Block
     */
    protected function prepareLanguageData(string $uniqueId, array $options): array
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
            $wrapperClass = 'position-relative ' . ($style === 'dropdown' ? 'dropdown' : '');
            $btnClass = 'btn btn-link text-decoration-none p-0 text-reset ' . (($style === 'dropdown' && !$hideDropdownIcon) ? 'dropdown-toggle' : '');

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
                : 'd-flex align-items-center gap-1';

            // Button Attributes
            $btnAttrs = '';
            if ($style === 'dropdown') {
                $btnAttrs = 'data-bs-toggle="dropdown" aria-expanded="false"';
            } else {
                $btnAttrs = 'data-bs-toggle="modal" data-bs-target="' . $targetId . '"';
            }

            $triggers[] = [
                'mode' => $mode,
                'content' => $rt['content'],
                'label' => $label,
                'wrapperClass' => $wrapperClass,
                'wrapperAttrs' => $wrapperAttrs,
                'btnClass' => $btnClass,
                'btnAttrs' => $btnAttrs,
                'contentLayout' => $contentLayout,
                'showLabel' => $showLabel,
                'labelPosition' => $labelPosition,
            ];
        }

        return [
            'uniqueId' => $uniqueId,
            'triggerType' => $triggerType,
            'style' => $style, // Needed for conditional include in view
            'customIcon' => $customIcon,
            'languages' => $languages,
            'currencies' => $currencies,
            'currentLangCode' => $currentLangCode,
            'currentCurrCode' => $currentCurrCode,
            'triggers' => $triggers,
        ];
    }

    /**
     * Prepare Data for Logo Block
     */
    protected function prepareLogoData(string $uniqueId, array $options): array
    {
        $siteName = settings('general')->site_name ?? 'EzyMarket';
        $themeSettings = themeSettings('general');

        $logoStyle = $options['logo_style'] ?? 'logo_dark';
        $customClass = $options['custom_class'] ?? '';

        $logoUrl = match ($logoStyle) {
            'logo_light' => asset($themeSettings->logo_light ?? 'images/logo-light.png'),
            default => asset($themeSettings->logo_dark ?? 'images/logo.png'),
        };

        return [
            'uniqueId' => $uniqueId,
            'logoStyle' => $logoStyle,
            'customClass' => $customClass,
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
        ];
    }

    /**     * Prepare Data for Menu Block
     */
    protected function prepareMenuData(string $uniqueId, array $options): array
    {
        $menuItems = $this->getFooterMenus() ?? collect([]);
        $style = $options['menu_style'] ?? 'columns';

        // Calculate columns for 'columns' style
        $rootCount = $menuItems->count();
        $colClass = 'col-6 col-md-3';

        if ($style === 'columns') {
            if ($rootCount === 1) $colClass = 'col-12';
            elseif ($rootCount === 2) $colClass = 'col-6';
            elseif ($rootCount === 3) $colClass = 'col-12 col-md-4';
            elseif ($rootCount === 4) $colClass = 'col-6 col-md-3';
            elseif ($rootCount === 5) $colClass = 'col-6 col-md-2';
            elseif ($rootCount >= 6) $colClass = 'col-6 col-md-2';
        }

        return [
            'uniqueId' => $uniqueId,
            'style' => $style,
            'menuItems' => $menuItems,
            'rootCount' => $rootCount,
            'colClass' => $colClass,
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
        $labelTop = $options['show_label_top'] ?? false;
        $showDays = $options['show_days'] ?? true;
        $showHours = $options['show_hours'] ?? true;
        $showMinutes = $options['show_minutes'] ?? true;
        $showSeconds = $options['show_seconds'] ?? true;
        $style = $options['style'] ?? 'inline';
        $size = $options['size'] ?? 'md';
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

        return [
            'uniqueId' => $uniqueId,
            'targetDate' => $targetDate,
            'label' => $label,
            'labelIcon' => $labelIcon,
            'labelTop' => $labelTop,
            'showDays' => $showDays,
            'showHours' => $showHours,
            'showMinutes' => $showMinutes,
            'showSeconds' => $showSeconds,
            'style' => $style,
            'size' => $size,
            'boxClass' => $boxClass,
            'labelClass' => $labelClass,
        ];
    }

    /**
     * Prepare Data for Divider Block
     */
    protected function prepareDividerData(string $uniqueId, array $options): array
    {
        return [
            'uniqueId' => $uniqueId,
            'type' => $options['type'] ?? 'horizontal',
            'margin' => $options['margin'] ?? '3',
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
        $styles[] = "flex-wrap: wrap";

        // Container Width: Boxed (1080px)
        if (($options['container_width'] ?? '') === 'boxed') {
            $styles[] = "max-width: 1080px";
            $styles[] = "margin-left: auto";
            $styles[] = "margin-right: auto";
        }

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
            $styles[] = "border-top-width: " . ($options['border_top_width'] ?: '0') . "px";
            $styles[] = "border-right-width: " . ($options['border_right_width'] ?: '0') . "px";
            $styles[] = "border-bottom-width: " . ($options['border_bottom_width'] ?: '0') . "px";
            $styles[] = "border-left-width: " . ($options['border_left_width'] ?: '0') . "px";
        } elseif ($borderStyle === 'none') {
            $styles[] = "border: none !important";
        }

        // Border Radius
        $this->addStyleUnit($styles, 'border-radius', $options['border_radius'] ?? null);

        // Background
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

        return implode('; ', $styles);
    }

    /**
     * Generate CSS for Menu Block
     */
    protected function buildMenuStyles(string $uniqueId, array $options): string
    {
        $style = $options['menu_style'] ?? 'columns';
        $rootFontSize = $options['root_font_size'] ?? '';
        $rootColor = $options['root_color'] ?? '';
        $rootWeight = $options['root_weight'] ?? '';
        $rootTransform = $options['root_transform'] ?? '';
        $rootBorder = !empty($options['root_border_bottom']);

        $itemColor = $options['item_color'] ?? '';
        $itemHoverColor = $options['item_hover_color'] ?? '';
        $itemUnderline = !empty($options['item_underline']);

        $css = "";

        // Root Items
        $rootCss = "";
        if ($rootColor) $rootCss .= "color:{$rootColor} !important;";
        if ($rootFontSize) $rootCss .= "font-size:{$rootFontSize}px !important;";
        if ($rootWeight) {
            $rootCss .= "font-weight:{$rootWeight} !important;";
        } elseif ($style === 'columns') {
            $rootCss .= "font-weight:bold;";
        }
        if ($rootTransform) $rootCss .= "text-transform:{$rootTransform} !important;";
        if ($rootBorder) $rootCss .= "border-bottom:2px solid;padding-bottom:5px;display:inline-block;";

        if ($rootCss) {
            $css .= "#{$uniqueId} .root-menu-item{{$rootCss}}";
        }

        // Child Items
        if ($itemColor) {
            $css .= "#{$uniqueId} .child-menu-item{color:{$itemColor} !important;}";
        }

        // Hover
        if ($itemHoverColor || $itemUnderline) {
            $css .= "#{$uniqueId} .child-menu-item:hover{";
            if ($itemHoverColor) $css .= "color:{$itemHoverColor} !important;";
            if ($itemUnderline) $css .= "text-decoration:underline !important;";
            $css .= "}";
        } elseif (empty($itemHoverColor)) {
            $css .= "#{$uniqueId} .child-menu-item:hover{color:var(--bs-primary) !important;}";
        }

        return $css;
    }

    /**
     * Generate CSS for Payment Icons Block
     */
    protected function buildPaymentIconsStyles(string $uniqueId, array $options): string
    {
        $colorStyle = $options['color_style'] ?? 'original';

        $css = "";
        if ($colorStyle === 'monochrome') {
            $css .= "#{$uniqueId} .payment-img-wrapper{filter:grayscale(100%);opacity:0.9;}";
        }
        $css .= "#{$uniqueId} img{max-height:32px;}";

        return $css;
    }

    /**
     * Generate CSS for Links Block
     */
    protected function buildLinksStyles(string $uniqueId, array $options): string
    {
        $linkColor = $options['link_color'] ?? '';
        $hoverColor = $options['link_hover_color'] ?? '';
        $underline = !empty($options['link_underline']);

        $css = "";

        if ($linkColor || $hoverColor || $underline) {
            $css .= "#{$uniqueId} a{transition:all 0.2s ease;}";

            if ($linkColor) {
                $css .= "#{$uniqueId} a{color:{$linkColor} !important;}";
            }

            $css .= "#{$uniqueId} a:hover{";
            if ($hoverColor) {
                $css .= "color:{$hoverColor} !important;";
            }
            $css .= "text-decoration:" . ($underline ? 'underline' : 'none') . " !important;";
            $css .= "}";
        }

        return $css;
    }

    /**
     * Generate CSS for Logo Block
     */
    protected function buildLogoStyles(string $uniqueId, array $options): string
    {
        $logoWidth = $options['logo_width'] ?? null;
        $logoHeight = $options['logo_height'] ?? null;

        $styles = [];
        if (!empty($logoWidth)) {
            $styles[] = "width:{$logoWidth}px";
        }

        if (!empty($logoHeight)) {
            $styles[] = "max-height:{$logoHeight}px";
        } elseif (empty($logoWidth)) {
            $styles[] = "width:150px";
            // max-height auto is default
        }

        if (empty($styles)) {
            return "";
        }

        return "#{$uniqueId} img{" . implode(';', $styles) . ";}";
    }

    /*** Generate CSS for Footer Social Block
     */
    protected function buildSocialStyles(string $uniqueId, array $options): string
    {
        $iconStyle = $options['icon_style'] ?? 'rounded';
        $iconColor = $options['icon_color'] ?? 'monochrome';
        $hoverEffect = $options['hover_effect'] ?? true;

        $css = "";

        if ($iconStyle !== 'plain') {
            $css .= "#{$uniqueId} .social-icon{width:32px;height:32px;padding:0;}";
        }

        if ($hoverEffect) {
            $css .= "#{$uniqueId} .social-hover-effect{transition:transform 0.2s ease,opacity 0.2s ease;}";
            $css .= "#{$uniqueId} .social-hover-effect:hover{transform:translateY(-3px);opacity:0.9;}";
        }

        if ($iconColor === 'multicolor') {
            $brandColors = [
                'facebook' => '#1877F2',
                'x' => '#000000',
                'instagram' => '#E4405F',
                'youtube' => '#FF0000',
                'linkedin' => '#0A66C2',
                'pinterest' => '#BD081C',
            ];

            foreach ($brandColors as $platform => $color) {
                if ($iconStyle === 'plain') {
                    $css .= "#{$uniqueId} .social-link-{$platform}{color:{$color};}";
                } else {
                    $css .= "#{$uniqueId} .social-link-{$platform}{background-color:{$color};}";
                }
            }
        }

        return $css;
    }

    /**
     * Generate CSS for Divider Block
     */
    protected function buildDividerStyles(string $uniqueId, array $options): string
    {
        $type = $options['type'] ?? 'horizontal';
        $style = $options['style'] ?? 'solid';
        $color = $options['color'] ?? '#dee2e6';
        $width = $options['width'] ?? '100';
        $height = $options['height'] ?? '50';
        $thickness = $options['thickness'] ?? '1';

        $css = "";

        if ($type === 'vertical') {
            $css .= "#{$uniqueId} {";
            $css .= "height: {$height}px;";
            $css .= "width: 0;";
            $css .= "background: none;";
            $css .= "border-left: {$thickness}px {$style} {$color};";
            $css .= "opacity: 1;";
            $css .= "min-height: auto;";
            $css .= "}";
        } else {
            $css .= "#{$uniqueId} {";
            $css .= "border-top: {$thickness}px {$style} {$color};";
            $css .= "width: {$width}%;";
            $css .= "height: 0;";
            $css .= "background: none;";
            $css .= "opacity: 1;";
            $css .= "}";
        }

        return $css;
    }
}
