<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Premium Plan Interval Enum
 *
 * Represents premium plan billing intervals.
 */
enum PremiumPlanInterval: int
{
    case WEEK = 1;
    case MONTH = 2;
    case YEAR = 3;
    case LIFETIME = 4;

    /**
     * Get all interval options as an array.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::WEEK->value => translate('Weekly'),
            self::MONTH->value => translate('Monthly'),
            self::YEAR->value => translate('Yearly'),
            self::LIFETIME->value => translate('Lifetime'),
        ];
    }

    /**
     * Get the label for the interval.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::WEEK => translate('Weekly'),
            self::MONTH => translate('Monthly'),
            self::YEAR => translate('Yearly'),
            self::LIFETIME => translate('Lifetime'),
        };
    }

    /**
     * Get the number of days for the interval.
     *
     * @return int|null
     */
    public function days(): ?int
    {
        return match ($this) {
            self::WEEK => 7,
            self::MONTH => 30,
            self::YEAR => 365,
            self::LIFETIME => null,
        };
    }

    /**
     * Get the badge class for the interval.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::WEEK => 'bg-info',
            self::MONTH => 'bg-primary',
            self::YEAR => 'bg-success',
            self::LIFETIME => 'bg-warning',
        };
    }
}
