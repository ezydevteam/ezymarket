<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin Payout Email
 *
 * Notifies administrators when a seller submits a payout request
 * that requires approval and processing.
 *
 * Notification Details:
 * - Template: admin_payout_pending
 * - Trigger: Seller requests a payout
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminPayoutEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $payout Payout request instance
     */
    public function __construct($admin, $payout)
    {
        parent::__construct(
            notifiable: $admin,
            template: 'admin_payout_pending',
            data: [
                'seller_username' => $payout->seller->full_name,
                'request_id' => $payout->id,
                'amount' => getAmount($payout->amount),
                'method' => $payout->method,
                'account' => $payout->account,
                'status' => $payout->status_name,
                'date' => dateFormat($payout->created_at),
                'review_link' => route('admin.financial.payouts.index', ['payout' => $payout->id]),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.payout.pending'
        );
    }
}
