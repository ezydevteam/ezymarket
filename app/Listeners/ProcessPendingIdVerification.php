<?php

namespace App\Listeners;

use App\Events\IdVerificationPending;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminIdVerificationEmail;
use App\Models\Admin;

/**
 * Process pending ID verification requests
 *
 * This listener handles:
 * - Notifying all admins via email
 * - Sending admin dashboard notification
 */
class ProcessPendingIdVerification
{
    /**
     * Handle the ID verification pending event
     */
    public function handle(IdVerificationPending $event): void
    {
        $idVerification = $event->idVerification;

        // Validate ID verification exists
        if (!$idVerification || !$idVerification->id) {
            return;
        }

        // Send email notifications to all admins
        $this->notifyAdminsViaEmail($idVerification);

        // Send dashboard notification
        $this->sendDashboardNotification($idVerification);
    }

    /**
     * Send email notifications to all admins
     */
    protected function notifyAdminsViaEmail($idVerification): void
    {
        $admins = Admin::systemAccess()->active()->get();

        foreach ($admins as $admin) {
            dispatch(new SendAdminIdVerificationEmail($admin, $idVerification));
        }
    }

    /**
     * Send notification to admin dashboard
     */
    protected function sendDashboardNotification($idVerification): void
    {
        $title = translate('ID Verification Request [#:id]', ['id' => $idVerification->id]);
        $image = asset('images/notifications/kyc.png');
        $link = route('admin.id-verification.review', $idVerification->id);

        Notification::sendAdminNotification($title, $image, $link);
    }
}
