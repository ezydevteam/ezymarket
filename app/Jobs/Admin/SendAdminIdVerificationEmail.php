<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin ID Verification Email
 *
 * Notifies administrators when a new identity verification is submitted
 * and requires review.
 *
 * Notification Details:
 * - Template: admin_kyc_pending
 * - Trigger: User submits ID verification documents
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminIdVerificationEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $idVerification ID verification instance
     */
    public function __construct($admin, $idVerification)
    {
        parent::__construct(
            notifiable: $admin,
            template: 'admin_kyc_pending',
            data: [
                'username' => $idVerification->user->full_name,
                'id_verification_id' => $idVerification->id,
                'review_link' => route('admin.id-verification.review', $idVerification->id),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.id_verification.pending'
        );
    }
}



















