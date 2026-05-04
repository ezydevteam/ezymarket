<?php

namespace App\Enums;

enum BlogCommentStatus: string
{
    case PENDING = 'pending';
    case HOLD = 'hold';
    case PUBLISHED = 'published';

    /**
     * Get the label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::HOLD => 'On Hold',
            self::PUBLISHED => 'Published',
        };
    }

    /**
     * Get the badge class for the status
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'text-orange',
            self::HOLD => 'text-red',
            self::PUBLISHED => 'text-green',
        };
    }
    
    /**
     * Get the icon class for the status
     */
    public function iconClass(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::HOLD => 'bi-pause-circle',
            self::PUBLISHED => 'bi-check-circle',
        };
    }

    /**
     * Get the badge HTML for the status
     */
    public function badge(): string
    {
        return '<span class="badge bg-' . $this->badgeClass() . '"><i class="' . $this->iconClass() . ' me-1"></i> ' . $this->label() . '</span>';
    }

    /**
     * Get all status labels
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::HOLD->value => self::HOLD->label(),
            self::PUBLISHED->value => self::PUBLISHED->label(),
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
