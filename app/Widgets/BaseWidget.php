<?php

namespace App\Widgets;

use App\Contracts\WidgetContract;
use App\Models\Appearance\WidgetInstance;
use Illuminate\Support\Facades\View;

/**
 * Base Widget
 *
 * Abstract base class that all widget types should extend.
 * Provides common functionality and sensible defaults.
 */
abstract class BaseWidget implements WidgetContract
{
    /**
     * Widget slug (unique identifier).
     */
    protected string $slug = '';

    /**
     * Widget display title.
     */
    protected string $title = '';

    /**
     * Widget description.
     */
    protected string $description = '';

    /**
     * Widget icon (Bootstrap Icons class).
     */
    protected string $icon = 'bi bi-puzzle';

    /**
     * View path for frontend rendering.
     */
    protected string $view = '';

    /**
     * View path for settings form.
     */
    protected string $settingsView = '';

    /**
     * Whether this widget can be deleted from widget areas.
     * Set to false for system/core widgets that should only be disabled.
     */
    protected bool $isDeletable = true;

    /**
     * Get the widget's unique identifier/slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Get the widget's display title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the widget's description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the widget's icon.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Check if this widget can be deleted from widget areas.
     * Non-deletable widgets can only be disabled.
     */
    public function isDeletable(): bool
    {
        return $this->isDeletable;
    }

    /**
     * Get the default settings for this widget.
     * Override in child classes.
     */
    public function getDefaultSettings(): array
    {
        return [];
    }

    /**
     * Get the settings form fields configuration.
     * Override in child classes.
     *
     * Example return format:
     * [
     *     [
     *         'name' => 'content',
     *         'type' => 'textarea',
     *         'label' => 'Content',
     *         'placeholder' => 'Enter content...',
     *         'required' => true,
     *     ],
     * ]
     */
    public function getSettingsFields(): array
    {
        return [];
    }

    /**
     * Validate the widget settings.
     * Override in child classes for custom validation.
     */
    public function validateSettings(array $settings): array
    {
        $defaults = $this->getDefaultSettings();

        return array_merge($defaults, $settings);
    }

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string
    {
        if (empty($this->view) || !View::exists($this->view)) {
            return $this->renderFallback($instance);
        }

        // Ensure settings is always an array
        $settings = $instance->settings;
        if (is_object($settings)) {
            $settings = (array) $settings;
        }
        $settings = $settings ?? $this->getDefaultSettings();

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'settings' => $settings,
            'title' => $instance->display_title,
        ])->render();
    }

    /**
     * Render the widget settings form.
     */
    public function renderSettingsForm(WidgetInstance $instance): string
    {
        $viewPath = $this->settingsView ?: 'admin.appearance.widgets.settings';

        if (!View::exists($viewPath)) {
            $viewPath = 'admin.appearance.widgets.settings';
        }

        // Ensure settings is always an array with defaults merged
        $widgetSettings = $instance->settings;
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }
        // Merge with defaults (instance settings take precedence)
        $widgetSettings = array_merge($this->getDefaultSettings(), $widgetSettings ?? []);

        return View::make($viewPath, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'fields' => $this->getSettingsFields(),
        ])->render();
    }

    /**
     * Fallback rendering when view is not found.
     */
    protected function renderFallback(WidgetInstance $instance): string
    {
        return sprintf(
            '<div class="widget widget-%s">%s</div>',
            e($this->slug),
            e($instance->display_title)
        );
    }

    /**
     * Get a setting value from instance with default fallback.
     */
    protected function getSetting(WidgetInstance $instance, string $key, mixed $default = null): mixed
    {
        return $instance->getSetting($key, $default ?? data_get($this->getDefaultSettings(), $key));
    }
}
