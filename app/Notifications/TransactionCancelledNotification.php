<?php

namespace App\Notifications;

use App\Models\Financial\Transaction;
use App\Models\User;

class TransactionCancelledNotification extends BaseNotification
{
    public $transaction;

    public function __construct(Transaction $transaction, User $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'transaction_cancelled';
    }

    public function toArray($notifiable)
    {
        $amount = getAmount($this->transaction->total);

        return [
            'type' => 'transaction_cancelled',
            'title' => 'Transaction Cancelled',
            'message' => "Transaction #{$this->transaction->id} for {$amount} has been cancelled",
            'transaction_id' => $this->transaction->id,
            'amount' => $amount,
            'payment_method' => $this->transaction->paymentGateway->name ?? 'N/A',
            'reason' => $this->transaction->reason,
            'action_url' => route('user.transaction.show', $this->transaction->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'x-circle',
            'color' => 'error'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'transaction_cancelled',
            'shortcodes' => [
                'username' => $this->user->full_name,
				'user_email' => $this->user->email,
				'transaction_id' => $this->transaction->id,
				'transaction_subtotal' => getAmount($this->transaction->amount),
				'payment_method' => $this->transaction->paymentGateway->name ?? 'N/A',
				'transaction_fees' => getAmount($this->transaction->fees),
				'transaction_total' => getAmount($this->transaction->total),
				'transaction_date' => dateFormat($this->transaction->created_at),
				'transaction_view_link' => route('user.transaction.show', $this->transaction->id),
				'reason' => $this->transaction->reason,
				'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















