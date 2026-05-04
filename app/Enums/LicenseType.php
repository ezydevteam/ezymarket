<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * License Type Enum
 *
 * Represents the types of licenses available for products.
 * Can be used across multiple models (Sale, Purchase, etc.).
 *
 * @package App\Enums
 */
enum LicenseType: int
{
    case REGULAR = 1;
    case EXTENDED = 2;

    /**
     * Get all license type options as key-value pairs.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::REGULAR->value => self::REGULAR->label(),
            self::EXTENDED->value => self::EXTENDED->label(),
        ];
    }

    /**
     * Get the human-readable label for the license type.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular License',
            self::EXTENDED => 'Extended License',
        };
    }

    /**
     * Get the short label for the license type.
     *
     * @return string
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::EXTENDED => 'Extended',
        };
    }

    /**
     * Get the badge CSS class for the license type.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::REGULAR => 'bg-primary-subtle text-primary',
            self::EXTENDED => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the icon class for the license type.
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::REGULAR => 'bi-certificate',
            self::EXTENDED => 'bi-star',
        };
    }

    /**
     * Get the description for the license type.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::REGULAR => 'Standard license for single end product',
            self::EXTENDED => 'Extended license for multiple end products',
        };
    }
}
