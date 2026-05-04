<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\User;

class ProductFavoriteNotification extends BaseNotification
{
    public $product;
    public $user;
    public $notifiableUser;

    public function __construct(Product $product, User $user, User $seller)
    {
        $this->product = $product;
        $this->user = $user;
        $this->notifiableUser = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'product_favorite';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_favorite',
            'title' => 'product Added to Favorites',
            'message' => "{$this->user->full_name} added your product to favorites",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'user_id' => $this->user->id,
            'user_name' => $this->user->full_name,
            'user_username' => $this->user->username,
            'preview_image' => $this->user->avatar_url,
            'action_url' => url('/products/' . $this->product->slug . '/' . $this->product->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'heart',
            'color' => 'error'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'Seller_product_favorited',
            'shortcodes' => [
                'Seller_username' => $this->notifiableUser->full_name,
                'user_username' => $this->user->full_name,
                'user_link' => $this->user->profile_link,
                'message' => "{$this->user->full_name} added your product to favorites",
                'product_name' => $this->product->name,
                'product_link' => $this->product->view_link,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
