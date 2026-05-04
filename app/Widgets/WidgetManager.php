<?php

namespace App\Widgets;

use App\Contracts\WidgetContract;
use App\Enums\Widget\WidgetArea;
use App\Models\Appearance\{Widget, WidgetInstance};
use Illuminate\Support\Facades\View;
use Illuminate\Support\Collection;

/**
 * Widget Manager
 *
 * Central service for managing and rendering widgets.
 */
class WidgetManager
{
    /**
     * Registered widget classes.
     */
    protected array $widgets = [];

    /**
     * Register a widget class.
     */
    public function register(string $class): self
    {
        if (!class_exists($class) || !is_subclass_of($class, WidgetContract::class)) {
            throw new \InvalidArgumentException("Widget class must implement WidgetContract: {$class}");
        }

        $widget = app($class);
        $this->widgets[$widget->getSlug()] = $class;

        return $this;
    }

    /**
     * Register multiple widget classes.
     */
    public function registerMany(array $classes): self
    {
        foreach ($classes as $class) {
            $this->register($class);
        }

        return $this;
    }

    /**
     * Get all registered widget classes.
     */
    public function getRegistered(): array
    {
        return $this->widgets;
    }

    /**
     * Get a widget instance by slug.
     */
    public function get(string $slug): ?WidgetContract
    {
        if (!isset($this->widgets[$slug])) {
            return null;
        }

        return app($this->widgets[$slug]);
    }

    /**
     * Get all available widgets from database.
     */
    public function getAvailable(): Collection
    {
        return Widget::active()->byTitle()->get();
    }

    /**
     * Get all widget areas from enum.
     */
    public function getAreas(): array
    {
        return WidgetArea::cases();
    }

    /**
     * Render all widgets in a specific area (alias for renderArea).
     */
    public function area(string $slug, array $wrapperOptions = []): string
    {
        return $this->renderArea($slug, $wrapperOptions);
    }

    /**
     * Render all widgets in a specific area.
     */
    public function renderArea(string $slug, array $wrapperOptions = []): string
    {
        $areaEnum = WidgetArea::fromValue($slug);

        if (!$areaEnum) {
            return '';
        }

        $instances = WidgetInstance::getForArea($areaEnum);

        $output = '';
        foreach ($instances as $instance) {
            $output .= $this->renderInstance($instance, $wrapperOptions);
        }

        return $this->wrapArea($areaEnum, $output, $wrapperOptions);
    }

    /**
     * Render a single widget instance.
     */
    public function renderInstance(WidgetInstance $instance, array $options = []): string
    {
        if (!$instance->is_active || !$instance->widget) {
            return '';
        }

        $widgetClass = $instance->widget->getWidgetInstance();

        if (!$widgetClass) {
            return '';
        }

        $content = $widgetClass->render($instance);

        return $this->wrapWidget($instance, $content, $options);
    }

    /**
     * Render a widget instance by ID.
     */
    public function render(int $instanceId): string
    {
        $instance = WidgetInstance::with('widget')->find($instanceId);

        if (!$instance) {
            return '';
        }

        return $this->renderInstance($instance);
    }

    /**
     * Wrap the area output with container.
     */
    protected function wrapArea(WidgetArea $area, string $content, array $options): string
    {
        $wrapperView = $options['area_wrapper'] ?? 'widgets.area-wrapper';

        if (!View::exists($wrapperView)) {
            return sprintf(
                '<div class="widget-area widget-area-%s" data-area="%s">%s</div>',
                e($area->value),
                e($area->value),
                $content
            );
        }

        return View::make($wrapperView, [
            'area' => $area,
            'content' => $content,
            'options' => $options,
        ])->render();
    }

    /**
     * Wrap individual widget output.
     */
    protected function wrapWidget(WidgetInstance $instance, string $content, array $options): string
    {
        $wrapperView = $options['widget_wrapper'] ?? 'widgets.widget-wrapper';

        if (!View::exists($wrapperView)) {
            return sprintf(
                '<div class="widget widget-%s" data-widget-id="%d">%s</div>',
                e($instance->widget->slug ?? 'unknown'),
                $instance->id,
                $content
            );
        }

        return View::make($wrapperView, [
            'instance' => $instance,
            'content' => $content,
            'options' => $options,
        ])->render();
    }

    /**
     * Sync widget types from registered classes to database.
     */
    public function syncToDatabase(): void
    {
        foreach ($this->widgets as $slug => $class) {
            $widget = app($class);

            Widget::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $widget->getTitle(),
                    'class' => $class,
                    'description' => $widget->getDescription(),
                    'icon' => $widget->getIcon(),
                    'is_active' => true,
                ]
            );
        }
    }
}
