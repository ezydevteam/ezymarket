<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Search Box Widget
 *
 * Displays a search form for products or site-wide search.
 */
class SearchBoxWidget extends BaseWidget
{
    protected string $slug = 'search-box';
    protected string $title = 'Search Box';
    protected string $description = 'Display a search form';
    protected string $icon = 'bi bi-search';
    protected string $view = 'widgets.types.search-box';

    /**
     * Allowed areas for this widget.
     */
    protected array $allowedAreas = ['sidebar', 'footer', 'header'];

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => false,
            'title_style' => 'default',
            'widget_card_style' => 'card-border',
            'placeholder' => '',
            'style' => 'style-1',
            'search_type' => 'products',
            'show_button' => true,
            'button_text' => '',
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => false,
            ],
            [
                'name' => 'style',
                'type' => 'select',
                'label' => translate('Display Style'),
                'options' => [
                    'style-1' => translate('Classic'),
                    'style-2' => translate('Rounded'),
                    'style-3' => translate('Minimal'),
                ],
                'default' => 'style-1',
            ],
            [
                'name' => 'search_type',
                'type' => 'select',
                'label' => translate('Search Type'),
                'options' => [
                    'products' => translate('Products'),
                    'blog' => translate('Blog Posts'),
                    'all' => translate('All Content'),
                ],
                'default' => 'products',
            ],
            [
                'name' => 'placeholder',
                'type' => 'text',
                'label' => translate('Placeholder Text'),
                'placeholder' => translate('Search...'),
                'default' => '',
            ],
            [
                'name' => 'show_button',
                'type' => 'checkbox',
                'label' => translate('Show Search Button'),
                'default' => true,
            ],
            [
                'name' => 'button_text',
                'type' => 'text',
                'label' => translate('Button Text'),
                'placeholder' => translate('Leave empty for icon only'),
                'default' => '',
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
        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        return View::make($this->view, [
            'widget' => $this,
            'widgetTitle' => $instance->display_title,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
        ])->render();
    }
}
