<?php

namespace App\Enums\Menu;

enum MenuBadge: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case SUCCESS = 'success';
    case DANGER = 'danger';
    case WARNING = 'warning';
    case INFO = 'info';
    case LIGHT = 'light';
    case DARK = 'dark';
    case BODY = 'body';
    case MUTED = 'muted';
    case WHITE = 'white';
    case BLACK = 'black';

    /**
     * Get the label for the badge color.
     */
    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => translate('Primary (Blue)'),
            self::SECONDARY => translate('Secondary (Gray)'),
            self::SUCCESS => translate('Success (Green)'),
            self::DANGER => translate('Danger (Red)'),
            self::WARNING => translate('Warning (Yellow)'),
            self::INFO => translate('Info (Cyan)'),
            self::LIGHT => translate('Light (Light Gray)'),
            self::DARK => translate('Dark (Black)'),
            self::BODY => translate('Body Text'),
            self::MUTED => translate('Muted (Lighter Gray)'),
            self::WHITE => translate('White'),
            self::BLACK => translate('Black'),
        };
    }

    /**
     * Get the CSS class for the badge.
     */
    public function cssClass(): string
    {
        return 'badge bg-' . $this->value;
    }

    /**
     * Get all badge colors as array for dropdowns.
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    /**
     * Get all badge color values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
