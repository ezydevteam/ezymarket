<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Ticket Status Enum
 *
 * Represents the various states of a support ticket.
 */
enum TicketStatus: int
{
    case OPENED = 1;
    case CLOSED = 2;
    case CANCELLED = 3;

    /**
     * Get all status options as an array.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::OPENED->value => translate('Opened'),
            self::CLOSED->value => translate('Closed'),
            self::CANCELLED->value => translate('Cancelled'),
        ];
    }

    /**
     * Get the label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::OPENED => translate('Opened'),
            self::CLOSED => translate('Closed'),
            self::CANCELLED => translate('Cancelled'),
        };
    }

    /**
     * Get the badge class for the status.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::OPENED => 'bg-success-subtle text-success',
            self::CLOSED => 'bg-danger-subtle text-danger',
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
        return match($this) {
            self::OPENED => 'bi-envelope-paper',
            self::CLOSED => 'bi-x-circle',
            self::CANCELLED => 'bi-slash-circle',
        };
    }
}
