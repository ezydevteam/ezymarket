<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Seller Card Widget
 *
 * Displays the seller/author information card with customizable styles.
 */
class ProductSellerCardWidget extends BaseWidget
{
    protected string $slug = 'product-seller-card';
    protected string $title = 'Product Seller Card';
    protected string $description = 'Display seller information with customizable style';
    protected string $icon = 'bi bi-person-badge';
    protected string $view = 'widgets.types.product-seller-card';

    /**
     * Allowed areas for this widget.
     */
    protected array $allowedAreas = ['single-product-sidebar'];

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => false,
            'show_avatar' => true,
            'description' => '',
            'show_avg_ratings' => true,
            'show_total_sales' => true,
            'show_level_badge' => true,
            'show_contact_button' => true,
            'show_follow_button' => true,
            'show_custom_services' => true,
            'style' => 'style-1',
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
                    'style-1' => translate('Default'),
                    'style-2' => translate('Centered'),
                    'style-3' => translate('Compact'),
                ],
                'default' => 'style-1',
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => false,
            ],
            [
                'name' => 'show_avatar',
                'type' => 'checkbox',
                'label' => translate('Show Avatar'),
                'default' => true,
            ],
            [
                'name' => 'show_avg_ratings',
                'type' => 'checkbox',
                'label' => translate('Show Average Ratings'),
                'default' => true,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'label' => translate('Seller info'),
                'placeholder' => translate('Optional seller information'),
                'default' => '',
            ],
            [
                'name' => 'show_total_sales',
                'type' => 'checkbox',
                'label' => translate('Show Total Sales'),
                'default' => true,
            ],
            [
                'name' => 'show_level_badge',
                'type' => 'checkbox',
                'label' => translate('Show Seller Badges'),
                'default' => true,
            ],
            [
                'name' => 'show_contact_button',
                'type' => 'checkbox',
                'label' => translate('Show Contact Button'),
                'default' => true,
            ],
            [
                'name' => 'show_follow_button',
                'type' => 'checkbox',
                'label' => translate('Show Follow Button'),
                'default' => true,
            ],
            [
                'name' => 'show_custom_services',
                'type' => 'checkbox',
                'label' => translate('Show Custom Services'),
                'default' => true,
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
        $product = request()->route('product') ?? View::shared('product');

        if (!$product) {
            return '';
        }

        $seller = $product->seller;
        $settings = settings();
        $productSettings = $settings->product ?? null;

        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'product' => $product,
            'seller' => $seller,
            'settings' => $settings,
            'productSettings' => $productSettings,
        ])->render();
    }
}
