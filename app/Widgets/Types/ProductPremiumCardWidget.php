<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Product Premium Card Widget
 *
 * Displays the premium/subscription download section for premium products.
 * This widget is locked to product sidebar and cannot be deleted.
 */
class ProductPremiumCardWidget extends BaseWidget
{
    protected string $slug = 'product-premium-card';
    protected string $title = 'Product Premium Card';
    protected string $description = 'Display premium subscription download section';
    protected string $icon = 'bi bi-gem';
    protected string $view = 'widgets.types.product-premium-card';

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

        // Only show for premium products with subscription enabled
        if (!$product || !isPremiumAvailable() || !$product->isPremium()) {
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
