<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductReview;
use App\Models\User;

class ProductReviewNotification extends BaseNotification
{
    public $review;
    public $product;
    public $notifiableUser;

    public function __construct(ProductReview $review, Product $product, User $seller)
    {
        $this->review = $review;
        $this->product = $product;
        $this->notifiableUser = $seller;
    }

    public function getNotificationPreference(): string
    {
        return 'product_review';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_review',
            'title' => 'New Review Received',
            'message' => "Your product '{$this->product->name}' received a {$this->review->stars}-star review",
            'review_id' => $this->review->id,
            'product_id' => $this->product->id,
            'product_title' => $this->product->name,
            'rating' => $this->review->stars,
            'preview_image' => $this->product->thumbnail_url,
            'action_url' => route('products.review', [$this->product->slug, $this->product->id, $this->review->id]),
            'timestamp' => now()->toISOString(),
            'icon' => 'star-fill',
            'color' => 'warning'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'Seller_product_review',
            'shortcodes' => [
                'Seller_username' => $this->notifiableUser->full_name,
                'user_username' => $this->review->user->full_name,
                'stars' => $this->review->stars,
                'subject' => $this->review->subject,
                'review' => $this->review->body,
                'product_name' => $this->product->name,
                'product_link' => $this->product->view_link,
                'review_link' => route('products.review', [$this->product->slug, $this->product->id, $this->review->id]),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
