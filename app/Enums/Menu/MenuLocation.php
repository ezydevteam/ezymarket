<?php

namespace App\Enums\Menu;

enum MenuLocation: string
{
    case TOP = 'top';
    case BOTTOM = 'bottom';
    case FOOTER = 'footer';
    case MOBILE = 'mobile';

    /**
     * Get the label for the location.
     */
    public function label(): string
    {
        return match ($this) {
            self::TOP => translate('Top Navigation'),
            self::BOTTOM => translate('Bottom Navigation'),
            self::FOOTER => translate('Footer Menu'),
            self::MOBILE => translate('Mobile Menu'),
        };
    }

    /**
     * Get the icon for the location.
     */
    public function icon(): string
    {
        return match ($this) {
            self::TOP => 'bi-arrow-up-square',
            self::BOTTOM => 'bi-arrow-down-square',
            self::FOOTER => 'bi-layout-text-window-reverse',
            self::MOBILE => 'bi-phone',
        };
    }

    /**
     * Get all locations as array for dropdowns.
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    /**
     * Get all location values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
