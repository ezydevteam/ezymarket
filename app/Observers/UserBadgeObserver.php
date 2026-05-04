<?php

namespace App\Observers;

use App\Models\UserBadge;
use App\Models\Badge;
use App\Facades\Notification;

class UserBadgeObserver
{
    private const SELLER_LEVEL_BADGE_ALIAS = "Seller_level";
    private const MEMBERSHIP_YEARS_BADGE_ALIAS = "membership_years";

    /**
     * Handle the UserBadge "created" event.
     * This fires when a badge is assigned to a user
     */
    public function created(UserBadge $userBadge): void
    {
        $userBadge->loadMissing(['user', 'badge']);

        Notification::sendBadgeChangeNotification(
            $userBadge->user,
            $userBadge->badge,
            'new'
        );
    }

    /**
     * Handle the UserBadge "deleted" event.
     * This fires when a badge is removed from a user
     */
    public function deleted(UserBadge $userBadge): void
    {
        $userBadge->loadMissing(['user', 'badge']);

        Notification::sendBadgeChangeNotification(
            $userBadge->user,
            $userBadge->badge,
            'removed'
        );
    }

    /**
     * Handle the UserBadge "updated" event.
     * Determines if it's an upgrade, downgrade, or just updated
     */
    public function updated(UserBadge $userBadge): void
    {
        if (!$userBadge->wasChanged('badge_id')) {
            return;
        }

        $userBadge->loadMissing(['user', 'badge']);

        $oldBadge = Badge::find($userBadge->getOriginal('badge_id'));
        $newBadge = $userBadge->badge;

        $changeType = $this->determineChangeType($oldBadge, $newBadge);

        Notification::sendBadgeChangeNotification(
            $userBadge->user,
            $newBadge,
            $changeType
        );
    }

    /**
     * Determine the type of badge change based on badge alias and ID
     */
    private function determineChangeType(?Badge $oldBadge, Badge $newBadge): string
    {
        if (!$oldBadge) {
            return 'new';
        }

        $upgradeDowngradeAliases = [
            self::SELLER_LEVEL_BADGE_ALIAS,
            self::MEMBERSHIP_YEARS_BADGE_ALIAS,
        ];

        // Check if the new badge supports upgrade/downgrade logic
        if (!in_array($newBadge->alias, $upgradeDowngradeAliases, true)) {
            return 'updated';
        }

        // Must be same alias to compare upgrade/downgrade
        if ($oldBadge->alias !== $newBadge->alias) {
            return 'updated';
        }

        // Compare badge IDs: higher ID = upgrade, lower ID = downgrade
        return match (true) {
            $newBadge->id > $oldBadge->id => 'upgrade',
            $newBadge->id < $oldBadge->id => 'downgrade',
            default => 'updated',
        };
    }
}
