<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;

/**
 * Image Widget
 *
 * Display an image with optional link.
 */
class ImageWidget extends BaseWidget
{
    protected string $slug = 'image';
    protected string $title = 'Image';
    protected string $description = 'Display an image with optional link';
    protected string $icon = 'bi bi-image';
    protected string $view = 'widgets.types.image';
    protected string $settingsView = 'admin.appearance.widgets.settings.image';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'image_url' => '',
            'alt_text' => '',
            'link_url' => '',
            'open_new_tab' => false,
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
                'name' => 'image_url',
                'type' => 'image',
                'label' => translate('Image'),
                'placeholder' => translate('Select or enter image URL'),
                'required' => true,
            ],
            [
                'name' => 'alt_text',
                'type' => 'text',
                'label' => translate('Alt Text'),
                'placeholder' => translate('Image description for accessibility'),
                'required' => false,
            ],
            [
                'name' => 'link_url',
                'type' => 'url',
                'label' => translate('Image URL'),
                'placeholder' => 'https://example.com/image',
                'required' => false,
            ],
            [
                'name' => 'open_new_tab',
                'type' => 'checkbox',
                'label' => translate('Open in New Tab'),
                'default' => false,
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
