<?php

namespace App\Notifications;

use App\Models\Refund;
use App\Models\RefundReply;
use App\Models\User;

class RefundReplyNotification extends BaseNotification
{

    public $refund;
    public $refundReply;
    public $recipient;

    public function __construct(Refund $refund, RefundReply $refundReply, User $recipient)
    {
        $this->refund = $refund;
        $this->refundReply = $refundReply;
        $this->recipient = $recipient;
        $this->notifiableUser = $recipient;
    }

    public function getNotificationPreference(): string
    {
        return 'refund_reply';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'refund_reply',
            'title' => 'New Refund Reply',
            'message' => "A new reply to the refund request for '{$this->refund->purchase->product->name}'",
            'refund_id' => $this->refund->id,
            'product_name' => $this->refund->purchase->product->name,
            'preview_image' => $this->refund->purchase->product->thumbnail_url,
            'action_url' => route('user.refund.show', $this->refund->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'chat-dots',
            'color' => 'info'
        ];
    }

    public function getEmailData()
    {
        if ($this->refund->SELLER->id == $this->recipient->id) {
            $template = 'Seller_refund_request_new_reply';
            $userName = $this->refundReply->user->full_name;
        } else {
            $template = 'refund_request_new_reply';
            $userName = $this->recipient->full_name;
        }

        return [
            'template' => $template,
            'shortcodes' => [
                'Seller_username' => $this->refund->SELLER->full_name,
                'user_username' => $userName,
                'refund_id' => $this->refund->id,
                'refund_product_name' => $this->refund->purchase->product->name,
                'refund_reply' => $this->refundReply->body,
                'refund_link' => route('user.refund.show', $this->refund->id),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
