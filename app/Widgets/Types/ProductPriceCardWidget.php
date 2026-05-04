<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Price Card Widget
 *
 * Displays the product pricing section with regular/extended options.
 * This widget is locked to product sidebar and cannot be deleted.
 */
class ProductPriceCardWidget extends BaseWidget
{
    protected string $slug = 'product-price-card';
    protected string $title = 'Product Price Card';
    protected string $description = 'Display product pricing with purchase options';
    protected string $icon = 'bi bi-credit-card';
    protected string $view = 'widgets.types.product-price-card';

    /**
     * Whether this widget can be deleted from the area.
     */
    protected bool $isDeletable = false;

    /**
     * Allowed areas for this widget.
     */
    protected array $allowedAreas = ['product-page-sidebar', 'single-product-sidebar'];

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => false,
            'style' => 'style-1',
            'regular_tab_label' => '',
            'extended_tab_label' => '',
            'show_extended_price' => true,
            'show_extra_features' => true,
            'add_to_cart_btn_style' => 'btn-primary',
            'add_to_cart_btn_icon' => '',
            'show_buy_now_button' => true,
            'buy_now_btn_style' => 'btn-outline-primary',
            'buy_now_btn_icon' => '',
            'product_info' => '',
            'show_support_policy_link' => true,
            'support_policy_slug' => '',
            'show_license_terms_link' => true,
            'license_terms_slug' => '',
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'style',
                'type' => 'select',
                'label' => translate('Display Style'),
                'options' => [
                    'style-1' => translate('Classic tabs'),
                    'style-2' => translate('Dropdown list'),
                    'style-3' => translate('Modern tabs'),
                ],
                'default' => 'style-1',
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Widget Title'),
                'default' => false,
            ],
            [
                'name' => 'regular_tab_label',
                'type' => 'text',
                'label' => translate('Regular Price Tab Label'),
                'placeholder' => translate('Regular'),
                'default' => '',
            ],
            [
                'name' => 'extended_tab_label',
                'type' => 'text',
                'label' => translate('Extended Price Tab Label'),
                'placeholder' => translate('Extended'),
                'default' => '',
            ],
            [
                'name' => 'show_extended_price',
                'type' => 'checkbox',
                'label' => translate('Extended Price Tab'),
                'default' => true,
            ],
            [
                'name' => 'show_extra_features',
                'type' => 'checkbox',
                'label' => translate('Extra Features'),
                'default' => true,
            ],
            [
                'name' => 'show_buy_now_button',
                'type' => 'checkbox',
                'label' => translate('Buy Now Button'),
                'default' => true,
            ],
            [
                'name' => 'add_to_cart_btn_style',
                'type' => 'select',
                'label' => translate('Add to Cart Button Style'),
                'options' => [
                    'btn-primary' => 'Primary',
                    'btn-secondary' => 'Secondary',
                    'btn-success' => 'Success',
                    'btn-danger' => 'Danger',
                    'btn-warning' => 'Warning',
                    'btn-info' => 'Info',
                    'btn-dark' => 'Dark',
                    'btn-light' => 'Light',
                    'btn-outline-primary' => 'Outline Primary',
                    'btn-outline-dark' => 'Outline Dark',
                ],
                'default' => 'btn-primary',
            ],
            [
                'name' => 'add_to_cart_btn_icon',
                'type' => 'icon',
                'label' => translate('Add to Cart Button Icon (Bootstrap Class)'),
                'placeholder' => 'bi-cart-plus',
                'default' => '',
            ],
            [
                'name' => 'buy_now_btn_style',
                'type' => 'select',
                'label' => translate('Buy Now Button Style'),
                'options' => [
                    'btn-primary' => 'Primary',
                    'btn-secondary' => 'Secondary',
                    'btn-success' => 'Success',
                    'btn-danger' => 'Danger',
                    'btn-warning' => 'Warning',
                    'btn-info' => 'Info',
                    'btn-dark' => 'Dark',
                    'btn-light' => 'Light',
                    'btn-outline-primary' => 'Outline Primary',
                    'btn-outline-dark' => 'Outline Dark',
                ],
                'default' => 'btn-outline-primary',
            ],
            [
                'name' => 'buy_now_btn_icon',
                'type' => 'icon',
                'label' => translate('Buy Now Button Icon (Bootstrap Class)'),
                'placeholder' => 'bi-lightning-charge',
                'default' => '',
            ],
            [
                'name' => 'product_info',
                'type' => 'textarea',
                'label' => translate('Product Info (comma separated)'),
                'placeholder' => translate('Quality checked by :site, Instant delivery, etc.', ['site' => settings('general')->site_name ?? 'ezymarket']),
                'default' => '',
            ],
            [
                'name' => 'show_support_policy_link',
                'type' => 'checkbox',
                'label' => translate('Support Policy Link'),
                'default' => true,
            ],
            [
                'name' => 'support_policy_slug',
                'type' => 'text',
                'label' => translate('Support Policy Page Slug'),
                'placeholder' => translate('product-support-policy'),
                'default' => 'product-support-policy',
            ],
            [
                'name' => 'show_license_terms_link',
                'type' => 'checkbox',
                'label' => translate('License Terms Link'),
                'default' => true,
            ],
            [
                'name' => 'license_terms_slug',
                'type' => 'text',
                'label' => translate('License Terms Page Slug'),
                'placeholder' => translate('license-terms'),
                'default' => 'license-terms',
            ],
        ];
    }

    /**
     * Get allowed areas for this widget.
     */
    public function getAllowedAreas(): array
    {
        return $this->allowedAreas;
    }

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string
    {
        // This widget requires a product context
        $product = request()->route('product') ?? View::shared('product');

        if (!$product || !$product->isPurchasingEnabled()) {
            return '';
        }

        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        $settings = settings();
        $productSettings = $settings->product ?? null;

        // Check if the current user has purchased this product
        $activePurchase = null;
        if (authUser()) {
            $activePurchase = \App\Models\Purchase::where('user_id', authUser()->id)
                ->where('product_id', $product->id)
                ->active()
                ->first();
        }

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'product' => $product,
            'settings' => $settings,
            'productSettings' => $productSettings,
            'activePurchase' => $activePurchase,
        ])->render();
    }
}
