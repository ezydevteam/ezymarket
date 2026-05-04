<?php

declare(strict_types=1);

namespace App\Enums\User;

/**
 * User Status Enum
 *
 * Represents the possible status values for a user account.
 */
enum UserStatus: string
{
    case SUSPENDED = 'suspended';
    case ACTIVE = 'active';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUSPENDED => 'Suspended',
            self::ACTIVE => 'Active',
        };
    }

    /**
     * Get the CSS class for the status badge.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUSPENDED => 'bg-text-red',
            self::ACTIVE => 'bg-text-green',
        };
    }

    /**
     * Get the icon for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::SUSPENDED => 'bi-x-circle',
            self::ACTIVE => 'bi-check-circle',
        };
    }

    /**
     * Get all available statuses as array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::SUSPENDED->value => self::SUSPENDED->label(),
            self::ACTIVE->value => self::ACTIVE->label(),
        ];
    }
}
