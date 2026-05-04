<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;

/**
 * Text Widget
 *
 * Simple text/HTML content widget.
 */
class TextWidget extends BaseWidget
{
    protected string $slug = 'text';
    protected string $title = 'Text';
    protected string $description = 'Simple text/HTML content widget';
    protected string $icon = 'bi bi-file-text';
    protected string $view = 'widgets.types.text';
    protected string $settingsView = 'admin.appearance.widgets.settings.text';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'content' => '',
            'show_title' => true,
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'content',
                'type' => 'textarea',
                'label' => translate('Content'),
                'placeholder' => translate('Enter your text content here...'),
                'required' => false,
                'rows' => 5,
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => true,
            ],
        ];
    }
}
