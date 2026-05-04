<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Blog\BlogCategory;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Blog Categories Widget
 *
 * Display blog categories list.
 */
class BlogCategoriesWidget extends BaseWidget
{
    protected string $slug = 'blog-categories';
    protected string $title = 'Blog Categories';
    protected string $description = 'Display blog categories list';
    protected string $icon = 'bi bi-bookmark';
    protected string $view = 'widgets.types.blog-categories';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'show_count' => true,
            'limit' => 10,
            'category_color' => '#000000',
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
                'name' => 'category_color',
                'type' => 'color',
                'label' => translate('Category Color'),
                'default' => '#000000',
            ],
            [
                'name' => 'show_count',
                'type' => 'checkbox',
                'label' => translate('Show Article Count'),
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

        $limit = (int) ($widgetSettings['limit'] ?? 10);

        $categories = BlogCategory::query()
            ->withCount('articles')
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
