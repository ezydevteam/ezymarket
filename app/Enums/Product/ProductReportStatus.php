<?php

namespace App\Enums\Product;

enum ProductReportStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case RESOLVED = 'resolved';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => translate('Pending'),
            self::REVIEWED => translate('Reviewed'),
            self::RESOLVED => translate('Resolved'),
            self::CANCELLED => translate('Cancelled'),
        };
    }

    /**
     * Get the badge class for the status
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-text-orange',
            self::REVIEWED => 'bg-text-blue',
            self::RESOLVED => 'bg-text-green',
            self::CANCELLED => 'bg-text-red',
        };
    }

    /**
     * Get the badge icon for the status
     */
    public function badgeIcon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::REVIEWED => 'bi-check-circle',
            self::RESOLVED => 'bi-check2-circle',
            self::CANCELLED => 'bi-x-circle',
        };
    }

    /**
     * Get the badge HTML for the status
     */
    public function badge(): string
    {
        return '<span class="badge ' . $this->badgeClass() . '"><i class="' . $this->badgeIcon() . ' me-1"></i>' . $this->label() . '</span>';
    }

    /**
     * Get all status labels as an array
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::REVIEWED->value => self::REVIEWED->label(),
            self::RESOLVED->value => self::RESOLVED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
