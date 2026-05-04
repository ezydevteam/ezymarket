<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\BadgeAlias;
use App\Models\{Badge, UserBadge};

/**
 * HasBadges Trait
 *
 * Provides badge management functionality for models.
 * Typically used by User model to handle badge assignments and checks.
 */
trait HasBadges
{
    /**
     * Add a badge to the user.
     *
     * @param Badge $badge The badge to add
     * @return bool True if badge was added, false if already exists
     */
    public function addBadge(Badge $badge): bool
    {
        if ($this->badges()->where('badge_id', $badge->id)->exists()) {
            return false;
        }

        $userBadge = $this->badges()->where('badge_alias', $badge->alias)->first();

        if (!$userBadge) {
            $userBadge = new UserBadge();
            $userBadge->sort_id = (UserBadge::count() + 1);
        }

        $userBadge->fill([
            'user_id' => $this->id,
            'badge_id' => $badge->id,
            'badge_alias' => $badge->alias,
        ])->save();

        return true;
    }

    /**
     * Add a country badge to the user.
     *
     * @param string|null $country The country code
     * @return void
     */
    public function addCountryBadge(?string $country = null): void
    {
        $badge = Badge::where('country', $country)->countryBadge()->first()
            ?? Badge::whereNull('country')->countryBadge()->first();

        if ($badge) {
            $this->addBadge($badge);
        }
    }

    /**
     * Add an exclusive seller badge if applicable.
     *
     * Adds the badge if the user is an exclusive seller,
     * removes it if they are not.
     *
     * @return void
     */
    public function addExclusiveSellerBadge(): void
    {
        if (!$this->isSeller()) {
            return;
        }

        $badge = Badge::exclusiveSellerBadge()->first();

        if (!$badge) {
            return;
        }

        if ($this->isExclusiveSeller()) {
            $this->addBadge($badge);
        } else {
            $this->removeBadge($badge);
        }
    }

    /**
     * Remove a badge from the user.
     *
     * @param Badge $badge The badge to remove
     * @return bool True if badge was removed, false if not found
     */
    public function removeBadge(Badge $badge): bool
    {
        $userBadge = $this->badges()->where('badge_id', $badge->id)->first();

        if ($userBadge) {
            $userBadge->delete();
            return true;
        }

        return false;
    }

    /**
     * Check if the user has a verified account badge.
     *
     * @return Badge|null The verified badge if exists, null otherwise
     */
    public function hasVerifiedBadge(): ?Badge
    {
        $userBadgeEntry = $this->badges->firstWhere('badge.alias', BadgeAlias::VERIFIED_ACCOUNT->value);
        return $userBadgeEntry?->badge;
    }

    /**
     * Check if the user has a level badge.
     *
     * @return Badge|null The level badge if exists, null otherwise
     */
    public function hasLevelBadge(): ?Badge
    {
        $userBadgeEntry = $this->badges->firstWhere('badge.alias', BadgeAlias::SELLER_LEVEL->value);
        return $userBadgeEntry?->badge;
    }

    /**
     * Get the featured seller badge if the user has it.
     *
     * @return Badge|null The featured seller badge if exists, null otherwise
     */
    public function getFeaturedSellerBadge(): ?Badge
    {
        $userBadgeEntry = $this->badges->firstWhere('badge.alias', BadgeAlias::FEATURED_SELLER->value);
        return $userBadgeEntry?->badge;
    }

    /**
     * Check if the user has a specific badge.
     *
     * @param int $badgeId The badge ID to check
     * @return bool True if user has the badge
     */
    public function hasBadge(int $badgeId): bool
    {
        return $this->badges()->where('badge_id', $badgeId)->exists();
    }

    /**
     * Check if the user has a badge by alias.
     *
     * @param string $alias The badge alias to check
     * @return bool True if user has the badge
     */
    public function hasBadgeByAlias(string $alias): bool
    {
        return $this->badges()->where('badge_alias', $alias)->exists();
    }

    /**
     * Get all badges for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBadges()
    {
        return $this->badges()->with('badge')->get();
    }

    /**
     * Sync user badges based on current status.
     *
     * Updates country badge and exclusive seller badge.
     *
     * @return void
     */
    public function syncBadges(): void
    {
        // Update country badge
        $country = @$this->address?->country;
        if ($country) {
            $this->addCountryBadge($country);
        }

        // Update exclusive seller badge
        if ($this->isSeller()) {
            $this->addExclusiveSellerBadge();
        }
    }

    /**
     * Remove all badges from the user.
     *
     * @return void
     */
    public function clearAllBadges(): void
    {
        $this->badges()->delete();
    }

    /**
     * Get the count of badges the user has.
     *
     * @return int The number of badges
     */
    public function badgeCount(): int
    {
        return $this->badges()->count();
    }
}
