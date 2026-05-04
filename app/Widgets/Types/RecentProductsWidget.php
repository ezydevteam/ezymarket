<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Product\Product;
use App\Models\Appearance\WidgetInstance;

/**
 * Recent Products Widget
 *
 * Display recent products.
 */
class RecentProductsWidget extends BaseWidget
{
    protected string $slug = 'recent-products';
    protected string $title = 'Recent Products';
    protected string $description = 'Display recent products';
    protected string $icon = 'bi bi-bag';
    protected string $view = 'widgets.types.recent-products';
    protected string $settingsView = 'admin.appearance.widgets.settings.recent-products';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'limit' => 5,
            'show_title' => true,
            'show_price' => true,
            'show_image' => true,
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'limit',
                'type' => 'number',
                'label' => translate('Number of Products'),
                'placeholder' => '5',
                'min' => 1,
                'max' => 20,
                'required' => true,
            ],
            [
                'name' => 'show_image',
                'type' => 'checkbox',
                'label' => translate('Show Product Image'),
                'default' => true,
            ],
            [
                'name' => 'show_price',
                'type' => 'checkbox',
                'label' => translate('Show Product Price'),
                'default' => true,
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => true,
            ],
        ];
    }

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string
    {
        $settings = $instance->settings ?? $this->getDefaultSettings();
        $limit = (int) ($settings['limit'] ?? 5);

        $products = Product::query()
            ->active()
            ->latest()
            ->take($limit)
            ->get();

        return view($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $settings,
            'title' => $instance->display_title,
            'products' => $products,
        ])->render();
    }
}
