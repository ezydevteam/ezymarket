<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Meta Card Widget
 *
 * Displays product meta info like reviews, sales, share, favorite buttons.
 */
class ProductMetaCardWidget extends BaseWidget
{
    protected string $slug = 'product-meta-card';
    protected string $title = 'Product Meta Card';
    protected string $description = 'Display reviews, sales, share and favorite buttons';
    protected string $icon = 'bi bi-info-square';
    protected string $view = 'widgets.types.product-meta-card';

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
            'show_reviews' => true,
            'show_sales' => true,
            'show_downloads' => true,
            'show_favorite' => true,
            'show_share' => true,
            'show_report' => true,
            'title_style' => 'default',
            'widget_card_style' => 'none',
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'show_reviews',
                'type' => 'checkbox',
                'label' => translate('Show Reviews'),
                'default' => true,
            ],
            [
                'name' => 'show_sales',
                'type' => 'checkbox',
                'label' => translate('Show Sales Count'),
                'default' => true,
            ],
            [
                'name' => 'show_downloads',
                'type' => 'checkbox',
                'label' => translate('Show Downloads (Free Products)'),
                'default' => true,
            ],
            [
                'name' => 'show_favorite',
                'type' => 'checkbox',
                'label' => translate('Show Favorite Button'),
                'default' => true,
            ],
            [
                'name' => 'show_share',
                'type' => 'checkbox',
                'label' => translate('Show Share Button'),
                'default' => true,
            ],
            [
                'name' => 'show_report',
                'type' => 'checkbox',
                'label' => translate('Show More Button'),
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

        $settings = settings();
        $productSettings = $settings->product;

        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'product' => $product,
            'settings' => $settings,
            'productSettings' => $productSettings,
        ])->render();
    }
}
