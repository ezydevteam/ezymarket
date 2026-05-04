<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Settings;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Social Followers Widget
 *
 * Display social media links with follower counts.
 */
class SocialFollowersWidget extends BaseWidget
{
    protected string $slug = 'social-followers';
    protected string $title = 'Social Followers';
    protected string $description = 'Display social media links with follower counts';
    protected string $icon = 'bi bi-people';
    protected string $view = 'widgets.types.social-followers';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'style' => 'list',
            'facebook_count' => '',
            'x_count' => '',
            'youtube_count' => '',
            'linkedin_count' => '',
            'instagram_count' => '',
            'pinterest_count' => '',
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'style',
                'type' => 'select',
                'label' => translate('Display Style'),
                'options' => [
                    'list' => translate('List'),
                    'grid' => translate('Grid'),
                ],
                'default' => 'list',
            ],
            [
                'name' => 'facebook_count',
                'type' => 'text',
                'label' => translate('Facebook Followers'),
                'placeholder' => translate('e.g., 10K'),
                'required' => false,
            ],
            [
                'name' => 'x_count',
                'type' => 'text',
                'label' => translate('X (Twitter) Followers'),
                'placeholder' => translate('e.g., 5K'),
                'required' => false,
            ],
            [
                'name' => 'youtube_count',
                'type' => 'text',
                'label' => translate('YouTube Subscribers'),
                'placeholder' => translate('e.g., 100K'),
                'required' => false,
            ],
            [
                'name' => 'linkedin_count',
                'type' => 'text',
                'label' => translate('LinkedIn Followers'),
                'placeholder' => translate('e.g., 2K'),
                'required' => false,
            ],
            [
                'name' => 'instagram_count',
                'type' => 'text',
                'label' => translate('Instagram Followers'),
                'placeholder' => translate('e.g., 50K'),
                'required' => false,
            ],
            [
                'name' => 'pinterest_count',
                'type' => 'text',
                'label' => translate('Pinterest Followers'),
                'placeholder' => translate('e.g., 8K'),
                'required' => false,
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

        // Get social links from settings
        $socialLinks = Settings::selectSettings('social_links');
        if (is_object($socialLinks)) {
            $socialLinks = (array) $socialLinks;
        }

        // Build social data array with links and counts
        $socials = [
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'bi bi-facebook',
                'color' => '#1877f2',
                'url' => $socialLinks['facebook'] ?? null,
                'count' => $widgetSettings['facebook_count'] ?? null,
            ],
            'x' => [
                'name' => 'X',
                'icon' => 'bi bi-twitter-x',
                'color' => '#000000',
                'url' => $socialLinks['x'] ?? null,
                'count' => $widgetSettings['x_count'] ?? null,
            ],
            'youtube' => [
                'name' => 'YouTube',
                'icon' => 'bi bi-youtube',
                'color' => '#ff0000',
                'url' => $socialLinks['youtube'] ?? null,
                'count' => $widgetSettings['youtube_count'] ?? null,
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'bi bi-linkedin',
                'color' => '#0a66c2',
                'url' => $socialLinks['linkedin'] ?? null,
                'count' => $widgetSettings['linkedin_count'] ?? null,
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => 'bi bi-instagram',
                'color' => '#e4405f',
                'url' => $socialLinks['instagram'] ?? null,
                'count' => $widgetSettings['instagram_count'] ?? null,
            ],
            'pinterest' => [
                'name' => 'Pinterest',
                'icon' => 'bi bi-pinterest',
                'color' => '#bd081c',
                'url' => $socialLinks['pinterest'] ?? null,
                'count' => $widgetSettings['pinterest_count'] ?? null,
            ],
        ];

        // Filter out socials without URLs
        $socials = array_filter($socials, fn($social) => !empty($social['url']) && $social['url'] !== '/');

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'widgetTitle' => $instance->display_title,
            'socials' => $socials,
        ])->render();
    }
}
