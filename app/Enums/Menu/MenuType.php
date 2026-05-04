<?php

namespace App\Enums\Menu;

enum MenuType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
    case HEADING = 'heading';
    case MEGA = 'mega';

    /**
     * Get the label for the menu type.
     */
    public function label(): string
    {
        return match ($this) {
            self::INTERNAL => translate('Internal Link'),
            self::EXTERNAL => translate('External Link'),
            self::HEADING => translate('Heading'),
            self::MEGA => translate('Mega Menu'),
        };
    }

    /**
     * Get the icon for the menu type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::INTERNAL => 'bi-link',
            self::EXTERNAL => 'bi-box-arrow-up-right',
            self::HEADING => 'bi-type-h2',
            self::MEGA => 'bi-grid-3x3-gap',
        };
    }

    /**
     * Check if this type is clickable.
     */
    public function isClickable(): bool
    {
        return !in_array($this, [self::HEADING, self::MEGA]);
    }

    /**
     * Check if this type supports mega menu styling.
     */
    public function isMega(): bool
    {
        return $this === self::MEGA;
    }

    /**
     * Get all menu types as array for dropdowns.
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    /**
     * Get all menu type values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
