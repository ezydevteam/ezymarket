<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Blog\BlogArticle;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Blog Posts Widget
 *
 * Consolidated widget to display recent or popular blog articles.
 */
class BlogPostsWidget extends BaseWidget
{
    protected string $slug = 'blog-posts';
    protected string $title = 'Blog Posts';
    protected string $description = 'Display recent or popular blog articles';
    protected string $icon = 'bi bi-newspaper';
    protected string $view = 'widgets.types.blog-posts';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'post_type' => 'recent',
            'limit' => 5,
            'show_image' => true,
            'show_meta' => true,
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
                'name' => 'post_type',
                'type' => 'select',
                'label' => translate('Post Type'),
                'options' => [
                    'recent' => translate('Recent Posts'),
                    'popular' => translate('Popular Posts'),
                ],
                'default' => 'recent',
            ],
            [
                'name' => 'limit',
                'type' => 'number',
                'label' => translate('Number of Posts'),
                'placeholder' => '5',
                'min' => 1,
                'max' => 20,
                'required' => false,
            ],
            [
                'name' => 'show_image',
                'type' => 'checkbox',
                'label' => translate('Show Thumbnail'),
                'default' => true,
            ],
            [
                'name' => 'show_meta',
                'type' => 'checkbox',
                'label' => translate('Show Metadata (Date/Views)'),
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

        $type = $widgetSettings['post_type'] ?? 'recent';
        $limit = (int) ($widgetSettings['limit'] ?? 5);

        $query = BlogArticle::query();

        if ($type === 'popular') {
            $query->orderByDesc('total_views');
        } else {
            $query->latest();
        }

        $articles = $query->limit($limit)->get();

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'articles' => $articles,
            'type' => $type,
        ])->render();
    }
}
