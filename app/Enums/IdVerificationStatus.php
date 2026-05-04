<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * ID Verification Status Enum
 *
 * Defines all possible statuses for user identity verification requests.
 * Used throughout the system for identity verification workflow.
 *
 * @package App\Enums
 */
enum IdVerificationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => translate('Pending'),
            self::APPROVED => translate('Approved'),
            self::REJECTED => translate('Rejected'),
        };
    }

    /**
     * Get the badge class for UI display.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning-subtle text-warning',
            self::APPROVED => 'bg-success-subtle text-success',
            self::REJECTED => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the icon class for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::APPROVED => 'bi-patch-check-fill',
            self::REJECTED => 'bi-x-circle',
        };
    }

    /**
     * Get all statuses as an array of values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all statuses as an associative array (value => label).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Try to create an instance from a string value.
     */
    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
