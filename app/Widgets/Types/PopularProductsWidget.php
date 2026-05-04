<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Product\Product;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Popular Products Widget
 *
 * Display popular/best-selling products.
 */
class PopularProductsWidget extends BaseWidget
{
    protected string $slug = 'popular-products';
    protected string $title = 'Popular Products';
    protected string $description = 'Display popular/best-selling products';
    protected string $icon = 'bi bi-fire';
    protected string $view = 'widgets.types.popular-products';

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
            'show_sales' => true,
            'show_rating' => false,
            'show_badge' => true,
            'order_by' => 'sales', // sales, views, rating
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
                'name' => 'order_by',
                'type' => 'select',
                'label' => translate('Order By'),
                'options' => [
                    'sales' => translate('Best Selling'),
                    'views' => translate('Most Viewed'),
                    'rating' => translate('Top Rated'),
                ],
                'default' => 'sales',
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
                'name' => 'show_sales',
                'type' => 'checkbox',
                'label' => translate('Show Sales Count'),
                'default' => true,
            ],
            [
                'name' => 'show_rating',
                'type' => 'checkbox',
                'label' => translate('Show Product Rating'),
                'default' => false,
            ],
            [
                'name' => 'show_badge',
                'type' => 'checkbox',
                'label' => translate('Show Number Badge'),
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
        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        $limit = (int) ($widgetSettings['limit'] ?? 5);
        $orderBy = $widgetSettings['order_by'] ?? 'sales';

        $query = Product::query()->active();

        switch ($orderBy) {
            case 'views':
                $query->orderByDesc('total_views');
                break;
            case 'rating':
                $query->orderByDesc('avg_reviews');
                break;
            case 'sales':
            default:
                $query->orderByDesc('total_sales');
                break;
        }

        $products = $query->take($limit)->get();

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'title' => $instance->display_title,
            'products' => $products,
        ])->render();
    }
}
