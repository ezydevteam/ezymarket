<?php

namespace App\Enums;

enum SupportEarningStatus: string
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
            self::REFUNDED => 'bg-warning-subtle text-warning',
            self::CANCELLED => 'bg-danger-subtle text-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'bi-check-circle',
            self::REFUNDED => 'bi-arrow-clockwise',
            self::CANCELLED => 'bi-x-circle',
        };
    }

    /**
     * Get the badge HTML for the status
     */
    public function badge(): string
    {
        return '<span class="status-badge ' . $this->badgeClass() . '"><i class="bi ' . $this->icon() . ' me-1"></i> ' . $this->label() . '</span>';
    }

    /**
     * Get all status labels as an array
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::REFUNDED->value => self::REFUNDED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
