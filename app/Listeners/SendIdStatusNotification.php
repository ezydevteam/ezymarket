<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserIdStatus;
use App\Models\IdVerification;
use App\Facades\Notification;

/**
 * Send notification when user's ID verification status changes.
 *
 * Handles both APPROVED and REJECTED statuses.
 *
 * Uses NotificationService which handles:
 * - Real-time broadcast notification (via BaseNotification)
 * - Database notification storage
 * - Email notification (via queued job SendEmailNotification)
 * - User notification preferences
 */
class SendIdStatusNotification
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
    public function handle(UserIdStatus $event): void
    {
        $user = $event->user;

        // Get the most recent ID verification record
        $idVerification = $event->verificationId
            ? IdVerification::find($event->verificationId)
            : $user->idVerifications()->latest()->first();

        // If we have a verification record, send notification
        if ($idVerification) {
            Notification::sendIdStatusNotification($idVerification);
        }
    }
}
