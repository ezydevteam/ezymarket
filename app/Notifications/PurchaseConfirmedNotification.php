<?php

namespace App\Notifications;

use App\Models\Purchase;
use App\Models\User;

class PurchaseConfirmedNotification extends BaseNotification
{
    public $purchase;

    public function __construct(Purchase $purchase, User $buyer)
    {
        $this->purchase = $purchase;
        $this->user = $buyer;
    }

    public function getNotificationPreference(): string
    {
        return 'purchase_confirmation';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'purchase_confirmation',
            'title' => 'Purchase Confirmed',
            'message' => "Your purchase of '{$this->purchase->product->name}' has been confirmed",
            'product_id' => $this->purchase->product_id,
            'product_title' => $this->purchase->product->name,
            'preview_image' => $this->purchase->product->thumbnail_url,
            'action_url' => route('user.purchase.index'),
            'timestamp' => now()->toISOString(),
            'icon' => 'check-circle',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'purchase_confirmation',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'product_name' => $this->purchase->product->name,
                'product_preview_image' => '<img src="' . $this->purchase->product->getImageLink() . '" width="100%"/>',
                'product_link' => $this->purchase->product->view_link,
                'purchase_code' => $this->purchase->code,
                'download_link' => route('user.purchase.index'),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
