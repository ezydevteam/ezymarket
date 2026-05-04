<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Gallery Widget
 *
 * Display a gallery of images.
 */
class GalleryWidget extends BaseWidget
{
    protected string $slug = 'gallery';
    protected string $title = 'Image Gallery';
    protected string $description = 'Display a gallery of images';
    protected string $icon = 'bi bi-images';
    protected string $view = 'widgets.types.gallery';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'columns' => 3,
            'images' => [], // Array of image paths
            'image_texts' => '', // Comma-separated text for each image
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'columns',
                'type' => 'select',
                'label' => translate('Columns'),
                'options' => [
                    '2' => translate('2 Columns'),
                    '3' => translate('3 Columns'),
                    '4' => translate('4 Columns'),
                ],
                'default' => '3',
            ],
            [
                'name' => 'images',
                'type' => 'gallery',
                'label' => translate('Gallery Images'),
                'required' => false,
            ],
            [
                'name' => 'image_texts',
                'type' => 'textarea',
                'label' => translate('Image Text'),
                'placeholder' => translate('Image 1 Text, Image 2 Text, ...'),
                'help' => translate('Comma-separated text for each image in the gallery. Used as ALT text and tooltips.'),
                'required' => false,
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

        // Get images array
        $storedImages = $widgetSettings['images'] ?? [];
        if (is_string($storedImages)) {
            $storedImages = json_decode($storedImages, true) ?? [];
        }

        // Convert to URLs
        $images = [];
        foreach ($storedImages as $imagePath) {
            if (!empty($imagePath)) {
                $imageUrl = storageUrl($imagePath);
                if ($imageUrl) {
                    $images[] = $imageUrl;
                }
            }
        }

        // Get captions
        $imageTexts = (string) ($widgetSettings['image_texts'] ?? '');
        $captions = !empty($imageTexts) ? array_map('trim', explode(',', $imageTexts)) : [];

        $columns = (int) ($widgetSettings['columns'] ?? 3);

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'images' => $images,
            'captions' => $captions,
            'columns' => $columns,
        ])->render();
    }
}
