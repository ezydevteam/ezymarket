<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\User;

class ProductSoldNotification extends BaseNotification
{
    public $buyer;
    public $product;
    public $user;

    public function __construct(Product $product, User $buyer, User $seller)
    {
        $this->product = $product;
        $this->buyer = $buyer;
        $this->user = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'product_sold';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_sold',
            'title' => 'product Sold',
            'message' => "Your product '{$this->product->name}' has been sold to {$this->buyer->full_name}",
            'product_id' => $this->product->id,
            'product_title' => $this->product->name,
            'preview_image' => $this->buyer->avatar_url,
            'action_url' => $this->product->view_link,
            'timestamp' => now()->toISOString(),
            'icon' => 'bag-check',
            'color' => 'info'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'Seller_product_sold',
            'shortcodes' => [
                'Seller_username' => $this->user->full_name,
                'message' => "Your product '{$this->product->name}' has been sold to {$this->buyer->full_name}",
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'action_url' => $this->product->view_link,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
