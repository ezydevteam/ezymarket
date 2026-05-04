<?php

namespace App\Enums\Page;

enum PageHeaderStyle: string
{
    case DEFAULT = 'default';
    case STYLE_1 = 'style-1';
    case STYLE_2 = 'style-2';
    case STYLE_3 = 'style-3';
    case STYLE_4 = 'style-4';
    case NO_HEADER = 'no-header';

    /**
     * Get all header style values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get header style label for display.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::STYLE_1 => 'Split',
            self::STYLE_2 => 'Centered',
            self::STYLE_3 => 'Minimal',
            self::STYLE_4 => 'Gradient Hero',
            self::NO_HEADER => 'No Header'
        };
    }

    /**
     * Get all header styles with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($style) {
            return [$style->value => $style->label()];
        })->toArray();
    }
}
