<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Sale Status Enum
 *
 * Represents the possible statuses for a sale transaction.
 *
 * @package App\Enums
 */
enum SaleStatus: string
{
    case ACTIVE = 'active';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    /**
     * Get all status options as key-value pairs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::REFUNDED->value => self::REFUNDED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    /**
     * Get the human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get the badge CSS class for the status.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success-subtle text-success',
            self::REFUNDED => 'bg-warning-subtle text-warning',
            self::CANCELLED => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the icon class for the status.
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'bi-check-circle',
            self::REFUNDED => 'bi-arrow-clockwise',
            self::CANCELLED => 'bi-x-circle',
        };
    }
}
