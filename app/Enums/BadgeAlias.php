<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Badge Alias Enum
 *
 * Defines all available badge types and their unique identifiers.
 * Used for badge identification and management throughout the system.
 *
 * @package App\Enums
 */
enum BadgeAlias: string
{
    case VERIFIED_ACCOUNT = 'verified_account';
    case COUNTRY = 'country';
    case SELLER_LEVEL = 'seller_level';
    case MEMBERSHIP_YEARS = 'membership_years';
    case EXCLUSIVE_SELLER = 'exclusive_seller';
    case TREND_MASTER = 'trend_master';
    case FEATURED_SELLER = 'featured_seller';
    case FEATURED_PRODUCT = 'featured_product';
    case REFERRER = 'referrer';
    case DISCOUNT_MASTER = 'discount_master';
    case PREMIUMER = 'premiumer';
    case PREMIUM_MEMBERSHIP = 'premium_membership';
    case TEAM_MEMBER = 'team_member';
    case COPYRIGHT_REPORTER = 'copyright_reporter';
    case BUG_REPORTER = 'bug_reporter';

    /**
     * Get a human-readable label for the badge alias.
     */
    public function label(): string
    {
        return match ($this) {
            self::VERIFIED_ACCOUNT => 'Verified Account',
            self::COUNTRY => 'Country Badge',
            self::SELLER_LEVEL => 'Seller Level Badge',
            self::MEMBERSHIP_YEARS => 'Membership Years',
            self::EXCLUSIVE_SELLER => 'Exclusive Seller',
            self::TREND_MASTER => 'Trend Master',
            self::FEATURED_SELLER => 'Featured Seller',
            self::FEATURED_PRODUCT => 'Featured Product',
            self::REFERRER => 'Referrer',
            self::DISCOUNT_MASTER => 'Discount Master',
            self::PREMIUMER => 'Premiumer',
            self::PREMIUM_MEMBERSHIP => 'Premium Membership',
            self::TEAM_MEMBER => 'Team Member',
            self::COPYRIGHT_REPORTER => 'Copyright Reporter',
            self::BUG_REPORTER => 'Bug Reporter',
        };
    }

    /**
     * Get the description for the badge alias.
     */
    public function description(): string
    {
        return match ($this) {
            self::VERIFIED_ACCOUNT => 'Awarded to users with verified accounts',
            self::COUNTRY => 'Badge representing user\'s country',
            self::SELLER_LEVEL => 'Badge based on seller level achievement',
            self::MEMBERSHIP_YEARS => 'Badge for years of membership',
            self::EXCLUSIVE_SELLER => 'Awarded to exclusive sellers',
            self::TREND_MASTER => 'Awarded to sellers with trending products',
            self::FEATURED_SELLER => 'Badge for featured sellers',
            self::FEATURED_PRODUCT => 'Badge for featured products',
            self::REFERRER => 'Awarded to successful referrers',
            self::DISCOUNT_MASTER => 'Awarded to sellers with discount campaigns',
            self::PREMIUMER => 'Badge for premium users',
            self::PREMIUM_MEMBERSHIP => 'Badge for premium membership holders',
            self::TEAM_MEMBER => 'Awarded to official team members',
            self::COPYRIGHT_REPORTER => 'Awarded to users who report copyright violations',
            self::BUG_REPORTER => 'Awarded to users who report bugs and issues',
        };
    }

    /**
     * Get all badge aliases as an array of values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all badge aliases as an associative array (value => label).
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
