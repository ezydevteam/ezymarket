<?php

namespace App\Notifications;

use App\Models\Refund;
use App\Models\RefundReply;
use App\Models\User;

class RefundStatusNotification extends BaseNotification
{
    public $refund;
    public $refundReply;
    public $status;

    public function __construct(Refund $refund, RefundReply $refundReply, string $status, User $buyer)
    {
        $this->refund = $refund;
        $this->refundReply = $refundReply;
        $this->status = $status;
        $this->notifiableUser = $buyer;
    }

    public function getNotificationPreference(): string
    {
        return 'refund_status';
    }

    public function toArray($notifiable)
    {
        $statusText = $this->status === 'accepted' ? 'accepted' : 'declined';
        $color = $this->status === 'accepted' ? 'success' : 'error';
        $icon = $this->status === 'accepted' ? 'check' : 'x';

        $productName = $this->refund->purchase->product->name ?? 'an product';

        return [
            'type' => 'refund_status',
            'title' => 'Refund ' . ucfirst($statusText),
            'message' => "Your refund request for '{$productName}' has been {$statusText}",
            'refund_id' => $this->refund->id,
            'status' => $this->status,
            'preview_image' => $this->refund->purchase->product->thumbnail_url,
            'action_url' => route('user.refund.show', $this->refund->id),
            'timestamp' => now()->toISOString(),
            'icon' => $icon,
            'color' => $color
        ];
    }

    public function getEmailData()
    {
        $template = $this->status == 'accepted' ? 'refund_request_accepted' : 'refund_request_declined';
        $declineReason = $this->status == 'declined' ? $this->refundReply->body : '';

        return [
            'template' => $template,
            'shortcodes' => [
                'Seller_username' => $this->refund->SELLER->full_name,
                'user_username' => $this->notifiableUser->full_name,
                'refund_id' => $this->refund->id,
                'refund_product_name' => $this->refund->purchase->product->name,
                'refund_amount' => getAmount($this->refund->purchase->sale->price),
                'refund_decline_reason' => $declineReason,
                'refund_link' => route('user.refund.show', $this->refund->id),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
