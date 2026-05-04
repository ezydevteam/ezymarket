<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Premium Status Enum
 *
 * Defines the possible states of a premium membership in the system.
 *
 * @package App\Enums
 */
enum PremiumStatus: int
{
    /**
     * Premium membership has expired and is no longer active
     */
    case EXPIRED = 0;

    /**
     * Premium membership is currently active and valid
     */
    case ACTIVE = 1;

    /**
     * Premium membership is approaching expiration (within renewal window)
     */
    case EXPIRING = 2;

    /**
     * Premium membership is temporarily on hold (admin action)
     */
    case ON_HOLD = 3;

    /**
     * Get all available status options
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::EXPIRED->value => translate('Expired'),
            self::ACTIVE->value => translate('Active'),
            self::EXPIRING->value => translate('Expiring'),
            self::ON_HOLD->value => translate('On Hold'),
        ];
    }

    /**
     * Get the translated label for the status
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => translate('Expired'),
            self::ACTIVE => translate('Active'),
            self::EXPIRING => translate('Expiring'),
            self::ON_HOLD => translate('On Hold'),
        };
    }

    /**
     * Get the badge class for the status
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::EXPIRED => 'bg-text-red',
            self::ACTIVE => 'bg-text-green',
            self::EXPIRING => 'bg-text-dark',
            self::ON_HOLD => 'bg-text-orange',
        };
    }

    /**
     * Get the icon for the status
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::EXPIRED => 'bi-x-circle',
            self::ACTIVE => 'bi-check-circle',
            self::EXPIRING => 'bi-exclamation-circle',
            self::ON_HOLD => 'bi-pause-circle',
        };
    }
}
