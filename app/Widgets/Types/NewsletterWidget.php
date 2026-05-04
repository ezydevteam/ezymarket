<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Newsletter Widget
 *
 * Displays a newsletter subscription form using Livewire.
 */
class NewsletterWidget extends BaseWidget
{
    protected string $slug = 'newsletter';
    protected string $title = 'Newsletter';
    protected string $description = 'Display a newsletter subscription form';
    protected string $icon = 'bi bi-envelope';
    protected string $view = 'widgets.types.newsletter';

    /**
     * Allowed areas for this widget.
     */
    protected array $allowedAreas = ['sidebar', 'footer'];

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'description' => '',
            'style' => 'style-1',
            'show_icon' => true,
            'button_text' => '',
            'placeholder' => '',
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
                'default' => true,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'label' => translate('Description'),
                'placeholder' => translate('Get the latest updates and offers.'),
                'default' => '',
            ],
            [
                'name' => 'style',
                'type' => 'select',
                'label' => translate('Style'),
                'options' => [
                    'style-1' => translate('Classic'),
                    'style-2' => translate('Boxed'),
                    'style-3' => translate('Stacked'),
                ],
                'default' => 'style-1',
            ],
            [
                'name' => 'show_icon',
                'type' => 'checkbox',
                'label' => translate('Show Icon'),
                'default' => true,
            ],
            [
                'name' => 'placeholder',
                'type' => 'text',
                'label' => translate('Email Placeholder'),
                'placeholder' => translate('Enter your email'),
                'default' => '',
            ],
            [
                'name' => 'button_type',
                'type' => 'select',
                'label' => translate('Button Type'),
                'options' => [
                    'text_only' => translate('Text Only'),
                    'icon_only' => translate('Icon Only'),
                    'both' => translate('Text and Icon'),
                ],
                'default' => 'text_only',
            ],
            [
                'name' => 'button_text',
                'type' => 'text',
                'label' => translate('Button Text'),
                'placeholder' => translate('Subscribe'),
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
