<?php

namespace App\Notifications;

use App\Models\Sale;
use App\Models\User;

class SalesEarningsNotification extends BaseNotification
{
    public $earnings;

    public function __construct(Sale $earnings, User $seller)
    {
        $this->earnings = $earnings;
        $this->user = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'sales_earning';
    }

    public function toArray($notifiable)
    {
        $earning = getAmount($this->earnings->seller_earning);

        return [
            'type' => 'sales_earning',
            'title' => 'Sales Earnings',
            'message' => "You've earned {$earning} by selling '{$this->earnings->product->name}'",
            'payment_id' => $this->earnings->id,
            'product_title' => $this->earnings->product->name,
            'preview_image' => $this->earnings->product->thumbnail_url,
            'action_url' => route('user.dashboard'),
            'timestamp' => now()->toISOString(),
            'icon' => 'currency-dollar',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        $earning = getAmount($this->earnings->seller_earning);

        return [
            'template' => 'Seller_sales_earning',
            'shortcodes' => [
                'Seller_username' => $this->user->full_name,
                'message' => "You've earned {$earning} by selling '{$this->earnings->product->name}'",
                'product_name' => $this->earnings->product->name,
                'action_url' => route('user.dashboard'),
                'earnings_amount' => $earning,
            ]
        ];
    }
}
