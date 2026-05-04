<?php

namespace App\Notifications;

use App\Models\Financial\Payout;
use App\Models\User;

class PayoutSubmittedNotification extends BaseNotification
{
    public Payout $payout;

    public function __construct(Payout $payout, User $seller)
    {
        $this->payout = $payout;
        $this->user = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'payout_submitted';
    }

    public function toArray($notifiable): array
    {
        $amount = getAmount($this->payout->amount);

        return [
            'type' => 'payout_submitted',
            'title' => 'Payout Request Submitted',
            'message' => "Your payout request of {$amount} has been submitted and is pending approval",
            'payout_id' => $this->payout->id,
            'amount' => $amount,
            'action_url' => route('user.payout.index'),
            'timestamp' => now()->toISOString(),
            'icon' => 'clock',
            'color' => 'info'
        ];
    }

    public function getEmailData(): array
    {
        return [
            'template' => 'seller_payout_submitted',
            'shortcodes' => [
               	'seller_username' => $this->user->full_name,
				'payout_id' => $this->payout->id,
				'payout_amount' => getAmount($this->payout->amount),
				'payout_method' => $this->payout->payoutMethod->name ?? $this->payout->method ?? 'N/A',
				'payout_account' => $this->payout->account,
				'payout_status' => $this->payout->status_name,
				'date_time' => dateFormat($this->payout->created_at),
				'action_url' => route('user.payout.index'),
				'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
