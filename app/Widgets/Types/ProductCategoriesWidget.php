<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Product\ProductCategory;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Categories Widget
 *
 * Display product categories list.
 */
class ProductCategoriesWidget extends BaseWidget
{
    protected string $slug = 'product-categories';
    protected string $title = 'Product Categories';
    protected string $description = 'Display product categories list';
    protected string $icon = 'bi bi-grid';
    protected string $view = 'widgets.types.product-categories';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'show_count' => true,
            'limit' => 10,
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
                'label' => translate('Number of Categories'),
                'placeholder' => '10',
                'min' => 1,
                'max' => 50,
                'required' => false,
            ],
            [
                'name' => 'show_count',
                'type' => 'checkbox',
                'label' => translate('Show Product Count'),
                'default' => true,
            ],
            [
                'name' => 'category_color',
                'type' => 'color',
                'label' => translate('Category Color'),
                'default' => '#000000',
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
        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        $limit = (int) ($widgetSettings['limit'] ?? 10);

        $categories = ProductCategory::query()
            ->withCount('products')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'categories' => $categories,
        ])->render();
    }
}
