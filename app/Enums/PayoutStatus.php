<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case PENDING = 'pending';
    case RETURNED = 'returned';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RECALLED = 'recalled';

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => translate('Pending'),
            self::RETURNED => translate('Returned'),
            self::APPROVED => translate('Approved'),
            self::COMPLETED => translate('Completed'),
            self::CANCELLED => translate('Cancelled'),
            self::RECALLED => translate('Recalled'),
        };
    }

    /**
     * Get the icon for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::RETURNED => 'bi-arrow-clockwise',
            self::APPROVED => 'bi-check2-circle',
            self::COMPLETED => 'bi-check2-square',
            self::CANCELLED => 'bi-x-circle',
            self::RECALLED => 'bi-arrow-right-circle',
        };
    }

    /**
     * Get the badge color for the status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning-subtle text-warning',
            self::RETURNED => 'bg-danger-subtle text-danger',
            self::APPROVED => 'bg-primary-subtle text-primary',
            self::COMPLETED => 'bg-success-subtle text-success',
            self::CANCELLED => 'bg-secondary-subtle text-secondary',
            self::RECALLED => 'bg-info-subtle text-info',
        };
    }

    /**
     * Get formatted badge HTML.
     */
    public function badge(): string
    {
        return '<div class="badge rounded-pill px-3 py-2 fw-normal ' . $this->badgeClass() . '"><i class="bi ' . $this->icon() . ' me-1"></i>' . $this->label() . '</div>';
    }

    /**
     * Get all status labels.
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::RETURNED->value => self::RETURNED->label(),
            self::APPROVED->value => self::APPROVED->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
            self::RECALLED->value => self::RECALLED->label(),
        ];
    }

    /**
     * Get all status cases as array.
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
