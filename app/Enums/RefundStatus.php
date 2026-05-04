<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Refund Status Enum
 *
 * Represents the possible statuses of a refund request.
 *
 * @package App\Enums
 */
enum RefundStatus: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case DECLINED = 3;
    case CANCELLED = 4;

    /**
     * Get all available options.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::ACCEPTED->value => self::ACCEPTED->label(),
            self::DECLINED->value => self::DECLINED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    /**
     * Get the label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => translate('Pending'),
            self::ACCEPTED => translate('Accepted'),
            self::DECLINED => translate('Declined'),
            self::CANCELLED => translate('Cancelled'),
        };
    }

    /**
     * Get the badge class for UI display.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning-subtle text-warning',
            self::ACCEPTED => 'bg-success-subtle text-success',
            self::DECLINED => 'bg-danger-subtle text-danger',
            self::CANCELLED => 'bg-secondary-subtle text-secondary',
        };
    }

    /**
     * Get the icon for the status.
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::ACCEPTED => 'bi-check-circle',
            self::DECLINED => 'bi-x-circle',
            self::CANCELLED => 'bi-slash-circle',
        };
    }
}
