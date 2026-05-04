<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin Transaction Pending Email
 *
 * Notifies administrators when a transaction requires manual review
 * (e.g., bank transfer, offline payment).
 *
 * Notification Details:
 * - Template: admin_transaction_pending
 * - Trigger: User creates a manual/offline payment transaction
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminTrxPendingEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $transaction Transaction instance
     */
    public function __construct($admin, $transaction)
    {
        parent::__construct(
            notifiable: $admin,
            template: 'admin_transaction_pending',
            data: [
                'username' => $transaction->user->full_name,
                'transaction_id' => $transaction->id,
                'transaction_subtotal' => getAmount($transaction->amount),
                'payment_method' => $transaction->paymentGateway->name,
                'transaction_fees' => getAmount($transaction->fees),
                'transaction_total' => getAmount($transaction->total),
                'transaction_date' => dateFormat($transaction->created_at),
                'review_link' => route('admin.financial.transactions.index', ['trx' => $transaction->id]),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.transaction.pending'
        );
    }
}


















