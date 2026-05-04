<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Details Card Widget
 *
 * Displays product attributes like version, category, tags, etc.
 */
class ProductDetailsCardWidget extends BaseWidget
{
    protected string $slug = 'product-details-card';
    protected string $title = 'Product Details Card';
    protected string $description = 'Display product attributes and details';
    protected string $icon = 'bi bi-card-list';
    protected string $view = 'widgets.types.product-details-card';

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
            'style' => 'style-1',
            'show_last_updated' => true,
            'show_published_date' => true,
            'show_version' => true,
            'show_category' => true,
            'show_options' => true,
            'show_tags' => true,
            'collapsed_by_default' => false,
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
                    'style-1' => translate('Modern'),
                    'style-2' => translate('Grid'),
                    'style-3' => translate('Minimal'),
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
                'name' => 'show_last_updated',
                'type' => 'checkbox',
                'label' => translate('Show Last Updated'),
                'default' => true,
            ],
            [
                'name' => 'show_published_date',
                'type' => 'checkbox',
                'label' => translate('Show Published Date'),
                'default' => true,
            ],
            [
                'name' => 'show_version',
                'type' => 'checkbox',
                'label' => translate('Show Version'),
                'default' => true,
            ],
            [
                'name' => 'show_category',
                'type' => 'checkbox',
                'label' => translate('Show Category'),
                'default' => true,
            ],
            [
                'name' => 'show_options',
                'type' => 'checkbox',
                'label' => translate('Show Product Options'),
                'default' => true,
            ],
            [
                'name' => 'show_tags',
                'type' => 'checkbox',
                'label' => translate('Show Tags'),
                'default' => true,
            ],
            [
                'name' => 'collapsed_by_default',
                'type' => 'checkbox',
                'label' => translate('Collapsed by Default'),
                'default' => false,
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
            'settings' => $settings,
        ])->render();
    }
}
