<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserBecameSeller;
use App\Facades\Notification;

/**
 * Send notification when user becomes a seller.
 *
 * Uses NotificationService which handles:
 * - Real-time broadcast notification (via BaseNotification)
 * - Database notification storage
 * - Email notification (via queued job SendEmailNotification)
 */
class SendBecameSellerNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Note: No need for ShouldQueue here because:
     * - NotificationService->notify() calls $user->notify() (instant broadcast)
     * - Email is already queued via SendEmailNotification job
     */
    public function handle(UserBecameSeller $event): void
    {
        // Only send notification for new sellers, not exclusive upgrades
        if (!$event->isExclusiveUpgrade) {
            Notification::sendBecomeSellerNotification($event->user);
        }
    }
}
