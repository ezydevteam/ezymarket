<?php

namespace App\Enums;

enum ReferralEarningStatus: string
{
    case ACTIVE = 'active';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';
    /**
     * Get the label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => translate('Active'),
            self::REFUNDED => translate('Refunded'),
            self::CANCELLED => translate('Cancelled'),
        };
    }

    /**
     * Get the badge class for the status
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
     * Get the badge icon for the status
     */
    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'bi-check-circle',
            self::REFUNDED => 'bi-arrow-clockwise',
            self::CANCELLED => 'bi-x-circle',
        };
    }


    /**
     * Get all status labels
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::REFUNDED->value => self::REFUNDED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    /**
     * Get all status values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
