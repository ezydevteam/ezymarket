<?php

namespace App\Notifications;

use App\Models\Financial\PayoutMethod;
use App\Models\User;

class PayoutMethodUpdateNotification extends BaseNotification
{
    public $payout;

    public function __construct(PayoutMethod $payoutMethod, User $seller)
    {
        $this->payout = $payoutMethod;
        $this->user = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'withdrawal_method_update';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payout_method_update',
            'title' => 'Payout Method Updated',
            'message' => "Your payout method has been updated to '{$this->payout->name}'",
            'user_id' => $this->user->id,
            'action_url' => route('user.settings.payout'),
            'timestamp' => now()->toISOString(),
            'icon' => 'bank',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'seller_payout_method_updated',
            'shortcodes' => [
                'Seller_username' => $this->user->full_name,
                'payout_method' =>  $this->payout->name,
                'status' =>  $this->payout->status_name,
                'link' => route('user.settings.payout'),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















