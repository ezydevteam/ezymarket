<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Free Card Widget
 *
 * Displays the free product download section.
 * This widget is locked to product sidebar and cannot be deleted.
 */
class ProductFreeCardWidget extends BaseWidget
{
    protected string $slug = 'product-free-card';
    protected string $title = 'Product Free Card';
    protected string $description = 'Display free product download section';
    protected string $icon = 'bi bi-gift';
    protected string $view = 'widgets.types.product-free-card';

    /**
     * Whether this widget can be deleted from the area.
     */
    protected bool $isDeletable = false;

    /**
     * Allowed areas for this widget.
     */
    protected array $allowedAreas = ['single-product-sidebar'];

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return ['show_title' => false];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [];
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
        $settings = settings();

        // Only show for free products
        if (!$product || !$product->isFree()) {
            return '';
        }

        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        $productSettings = $settings->product ?? null;

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'product' => $product,
            'settings' => $settings,
            'productSettings' => $productSettings,
        ])->render();
    }
}
