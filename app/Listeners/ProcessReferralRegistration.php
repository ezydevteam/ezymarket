<?php

namespace App\Listeners;

use App\Enums\BadgeAlias;
use App\Events\Registered;
use App\Models\{Badge, Referral, User};
use Illuminate\Support\Facades\Cookie;

/**
 * Process referral registration when a new user signs up
 *
 * This listener handles:
 * - Checking if referral system is enabled
 * - Creating referral record from cookie
 * - Awarding referrer badge to the referring user
 * - Cleaning up referral cookie
 */
class ProcessReferralRegistration
{
    /**
     * Handle the user registered event
     */
    public function handle(Registered $event): void
    {
        $newUser = $event->user;

        // Check if referral system is enabled
        if (!@settings('referral')->status) {
            return;
        }

        // Check if user has referral cookie
        if (!request()->hasCookie('_ref')) {
            return;
        }

        // Process referral
        $this->processReferral($newUser);
    }

    /**
     * Process the referral by creating record and awarding badge
     */
    protected function processReferral(User $newUser): void
    {
        $referrerUsername = request()->cookie('_ref');
        $referrer = User::where('username', $referrerUsername)->first();

        // Validate referrer exists
        if (!$referrer) {
            return;
        }

        // Create referral record
        $this->createReferralRecord($referrer, $newUser);

        // Remove referral cookie
        Cookie::queue(Cookie::forget('_ref'));

        // Award referrer badge
        $this->awardReferrerBadge($referrer);
    }

    /**
     * Create referral record linking referrer and new user
     */
    protected function createReferralRecord(User $referrer, User $newUser): void
    {
        Referral::create([
            'seller_id' => $referrer->id,
            'user_id' => $newUser->id,
        ]);
    }

    /**
     * Award referrer badge to the referring user
     */
    protected function awardReferrerBadge(User $referrer): void
    {
        $referrerBadge = Badge::where('alias', BadgeAlias::REFERRER)->first();

        if ($referrerBadge) {
            $referrer->addBadge($referrerBadge);
        }
    }
}


















