<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case UNPAID = 'unpaid';
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::UNPAID => translate('Unpaid'),
            self::PENDING => translate('Pending'),
            self::PAID => translate('Paid'),
            self::CANCELLED => translate('Cancelled'),
        };
    }

    /**
     * Get the badge color for the status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::UNPAID => 'bg-secondary-subtle text-secondary',
            self::PENDING => 'bg-warning-subtle text-warning',
            self::PAID => 'bg-success-subtle text-success',
            self::CANCELLED => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the icon for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::UNPAID => 'bi-clock',
            self::PENDING => 'bi-hourglass-split',
            self::PAID => 'bi-check2-circle',
            self::CANCELLED => 'bi-x-circle',
        };
    }

    /**
     * Get formatted badge HTML.
     */
    public function badge(): string
    {
        return '<div class="badge rounded-pill px-3 py-2 ' . $this->badgeClass() . '"><i class="bi ' . $this->icon() . ' me-1"></i>' . $this->label() . '</div>';
    }

    /**
     * Get all status labels.
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::PAID->value => self::PAID->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    /**
     * Get all statuses including unpaid.
     */
    public static function allLabels(): array
    {
        return [
            self::UNPAID->value => self::UNPAID->label(),
            self::PENDING->value => self::PENDING->label(),
            self::PAID->value => self::PAID->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
