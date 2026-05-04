<?php

namespace App\Notifications;

use App\Models\Financial\Payout;
use App\Models\User;

class PayoutStatusNotification extends BaseNotification
{
    public Payout $payout;

    public function __construct(Payout $payout, User $seller)
    {
        $this->payout = $payout;
        $this->user = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'payout_status';
    }

    public function toArray($notifiable): array
    {
        $amount = getAmount($this->payout->amount);
        $status = $this->payout->status_name;
        //$statusDetails = $this->payout->getStatusDetails();

        return [
            'type' => 'payout_status',
            'title' => "Payout {$status}",
            'message' => "Your payout request of {$amount} has been {$status}",
            'payout_id' => $this->payout->id,
            'amount' => $amount,
            'action_url' => route('user.payout.index'),
            'timestamp' => now()->toISOString(),
            'icon' => 'bi bi-cash',
            'color' => 'success'
        ];
    }

    public function getEmailData(): array
    {
        return [
            'template' => 'seller_payout_status_updated',
            'shortcodes' => [
                'seller_username' => $this->user->full_name,
				'payout_id' => $this->payout->id,
				'payout_amount' => getAmount($this->payout->amount),
				'payout_method' => $this->payout->payoutMethod->name ?? $this->payout->method ?? 'N/A',
				'payout_account' => $this->payout->account,
				'payout_date' => dateFormat($this->payout->created_at),
				'payout_status' => $this->payout->status_name,
				'action_url' => route('user.payout.index'),
				'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
