<?php

namespace App\Notifications;

use App\Models\Refund;
use App\Models\RefundReply;
use App\Models\User; 

class RefundRequestNotification extends BaseNotification
{
    
    public $refund;
    public $refundReply;

    public function __construct(Refund $refund, RefundReply $refundReply, User $seller)
    {
        $this->refund = $refund;
        $this->refundReply = $refundReply;
        $this->notifiableUser = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'refund_request';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'refund_request',
            'title' => 'New Refund Request',
            'message' => "{$this->refundReply->user->full_name} has requested a refund for '{$this->refund->purchase->product->name}'",
            'refund_id' => $this->refund->id,
            'product_name' => $this->refund->purchase->product->name,
            'requester_name' => $this->refundReply->user->full_name,
            'preview_image' => $this->refundReply->user->avatar_url,
            'action_url' => route('user.refund.show', $this->refund->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'currency-dollar',
            'color' => 'warning'
        ];
    }
    
    public function getEmailData()
    {
        return [
            'template' => 'Seller_new_refund_request',
            'shortcodes' => [
                'Seller_username' => $this->notifiableUser->full_name,
				'user_username' => $this->refundReply->user->full_name,
				'refund_id' => $this->refund->id,
				'refund_product_name' => $this->refund->purchase->product->name,
				'refund_reason' => $this->refundReply->body,
				'refund_link' => route('user.refund.show', $this->refund->id),
				'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















