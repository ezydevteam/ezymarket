<?php

namespace App\Enums;

enum FeedbackStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case RESOLVED = 'resolved';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => translate('Pending'),
            self::REVIEWED => translate('Reviewed'),
            self::RESOLVED => translate('Resolved'),
        };
    }

    /**
     * Get the badge CSS class for the status.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-text-orange',
            self::REVIEWED => 'bg-text-blue',
            self::RESOLVED => 'bg-text-green',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'bi-hourglass-split',
            self::REVIEWED => 'bi-eye',
            self::RESOLVED => 'bi-check-circle',
        };
    }

    /**
     * Get the full HTML badge for the status.
     */
    public function badge(): string
    {
        return '<span class="badge ' . $this->badgeClass() . '"><i class="bi ' . $this->icon() . ' me-1"></i>' . $this->label() . '</span>';
    }

    /**
     * Get all status labels as an associative array.
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::REVIEWED->value => self::REVIEWED->label(),
            self::RESOLVED->value => self::RESOLVED->label(),
        ];
    }

    /**
     * Get all cases as an array.
     */
    public static function values(): array
    {
        return [
            self::PENDING->value,
            self::REVIEWED->value,
            self::RESOLVED->value,
        ];
    }
}
