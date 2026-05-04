<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;

/**
 * Custom HTML Widget
 *
 * Raw HTML widget for advanced users.
 */
class CustomHtmlWidget extends BaseWidget
{
    protected string $slug = 'custom-html';
    protected string $title = 'Custom HTML';
    protected string $description = 'Raw HTML widget for advanced users';
    protected string $icon = 'bi bi-code-slash';
    protected string $view = 'widgets.types.custom-html';
    protected string $settingsView = 'admin.appearance.widgets.settings.custom-html';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'html' => '',
            'show_title' => false,
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'html',
                'type' => 'code',
                'label' => translate('HTML Code'),
                'placeholder' => '<div>Your HTML here...</div>',
                'help' => translate('Any html tag mistyped may break the widget or whole layout.'),
                'required' => false,
                'rows' => 10,
                'language' => 'html',
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => false,
            ],
        ];
    }
}
