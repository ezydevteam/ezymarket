<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Purchase Status Enum
 *
 * Defines the possible status states for a purchase transaction in the system.
 *
 * @package App\Enums
 */
enum PurchaseStatus: string
{
    case ACTIVE = 'active';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    /**
     * Get all available options as an associative array
     *
     * @return array<int, string>
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
     * Get the human-readable label for the purchase status
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
     * Get the Bootstrap badge class for the purchase status
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success-subtle text-success',
            self::REFUNDED => 'bg-orange-subtle text-orange',
            self::CANCELLED => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the Bootstrap icon class for the purchase status
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
