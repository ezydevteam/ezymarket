<?php

namespace App\Contracts;

use App\Models\Appearance\WidgetInstance;

/**
 * Widget Contract
 *
 * Interface that all widget types must implement.
 */
interface WidgetContract
{
    /**
     * Get the widget's unique identifier/slug.
     */
    public function getSlug(): string;

    /**
     * Get the widget's display title.
     */
    public function getTitle(): string;

    /**
     * Get the widget's description.
     */
    public function getDescription(): string;

    /**
     * Get the widget's icon (Bootstrap Icons class).
     */
    public function getIcon(): string;

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array;

    /**
     * Get the settings form fields configuration.
     * Returns an array of field definitions for the admin form.
     */
    public function getSettingsFields(): array;

    /**
     * Validate the widget settings.
     */
    public function validateSettings(array $settings): array;

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string;

    /**
     * Render the widget settings form (admin).
     */
    public function renderSettingsForm(WidgetInstance $instance): string;
}
