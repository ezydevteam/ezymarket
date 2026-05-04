<?php

namespace App\Enums\Menu;

enum MenuStyle: string
{
    case STANDARD = 'standard';
    case TWO_COL = '2col';
    case THREE_COL = '3col';
    case FOUR_COL = '4col';
    case FULL = 'full';

    /**
     * Get the label for the menu style.
     */
    public function label(): string
    {
        return match ($this) {
            self::STANDARD => translate('Standard Dropdown'),
            self::TWO_COL => translate('Two Columns'),
            self::THREE_COL => translate('Three Columns'),
            self::FOUR_COL => translate('Four Columns'),
            self::FULL => translate('Full Width'),
        };
    }

    /**
     * Get the number of columns.
     */
    public function columns(): int
    {
        return match ($this) {
            self::STANDARD => 1,
            self::TWO_COL => 2,
            self::THREE_COL => 3,
            self::FOUR_COL => 4,
            self::FULL => 0,
        };
    }

    /**
     * Get CSS class for the style.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::STANDARD => 'dropdown-standard',
            self::TWO_COL => 'mega-menu-2col',
            self::THREE_COL => 'mega-menu-3col',
            self::FOUR_COL => 'mega-menu-4col',
            self::FULL => 'mega-menu-full',
        };
    }

    /**
     * Get all menu styles as array for dropdowns.
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    /**
     * Get all menu style values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
