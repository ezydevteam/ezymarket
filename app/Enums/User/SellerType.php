<?php

declare(strict_types=1);

namespace App\Enums\User;

/**
 * Seller Type Enum
 *
 * Represents the type of seller account (exclusive or non-exclusive).
 */
enum SellerType: string
{
    case NON_EXCLUSIVE = 'non_exclusive';
    case EXCLUSIVE = 'exclusive';

    /**
     * Get the human-readable label for the seller type.
     */
    public function label(): string
    {
        return match($this) {
            self::NON_EXCLUSIVE => 'Non-Exclusive',
            self::EXCLUSIVE => 'Exclusive',
        };
    }

    /**
     * Get the description for the seller type.
     */
    public function description(): string
    {
        return match($this) {
            self::NON_EXCLUSIVE => 'Can sell products on other platforms',
            self::EXCLUSIVE => 'Sells products exclusively on this platform',
        };
    }

    /**
     * Get the CSS class for the seller type badge.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::NON_EXCLUSIVE => 'bg-text-orange',
            self::EXCLUSIVE => 'bg-text-green',
        };
    }

    /**
     * Get the icon for the seller type.
     */
    public function icon(): string
    {
        return match($this) {
            self::NON_EXCLUSIVE => 'bi-bank',
            self::EXCLUSIVE => 'bi-star',
        };
    }

    /**
     * Get all available seller types as array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::NON_EXCLUSIVE->value => self::NON_EXCLUSIVE->label(),
            self::EXCLUSIVE->value => self::EXCLUSIVE->label(),
        ];
    }
}
