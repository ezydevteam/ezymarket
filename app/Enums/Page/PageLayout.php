<?php

namespace App\Enums\Page;

enum PageLayout: string
{
    case DEFAULT = 'default';
    case FULL = 'full';
    case BOXED = 'boxed';
    case SIDEBAR = 'sidebar';

    /**
     * Get all layout values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get layout label for display.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::FULL => 'Full Width',
            self::BOXED => 'Boxed',
            self::SIDEBAR => 'With Sidebar',
        };
    }

    /**
     * Get layout description.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default layout',
            self::FULL => 'Content spans the full width of the page',
            self::BOXED => 'Content is contained in a centered box',
            self::SIDEBAR => 'Content with a right sidebar navigation',
        };
    }

    /**
     * Get all layouts with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($layout) {
            return [$layout->value => $layout->label()];
        })->toArray();
    }
}
