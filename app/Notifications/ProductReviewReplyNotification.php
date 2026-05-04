<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductReview;
use App\Models\Product\ProductReviewReply;
use App\Models\User;

class ProductReviewReplyNotification extends BaseNotification
{
    public $review;
    public $reply;
    public $seller;
    public $product;
    public $notifiableUser;

    public function __construct(ProductReview $review, ProductReviewReply $reply, Product $product, User $seller, User $reviewer)
    {
        $this->review = $review;
        $this->reply = $reply;
        $this->product = $product;
        $this->seller = $seller;
        $this->notifiableUser = $reviewer;
    }

    public function getNotificationPreference(): string
    {
        return 'product_review_reply';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_review_reply',
            'title' => 'Reply to Your Review',
            'message' => "{$this->seller->full_name} replied to your review for '{$this->product->name}'",
            'review_id' => $this->review->id,
            'reply_id' => $this->reply->id,
            'product_id' => $this->product->id,
            'product_title' => $this->product->name,
            'Seller_name' => $this->seller->full_name,
            'preview_image' => $this->seller->avatar_url,
            'action_url' => route('products.review', [$this->product->slug, $this->product->id, $this->review->id]),
            'timestamp' => now()->toISOString(),
            'icon' => 'chat-dots',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'product_review_reply',
            'shortcodes' => [
                'Seller_username' => $this->seller->full_name,
                'user_username' => $this->review->user->full_name,
                'message' => "{$this->seller->full_name} replied to your review for '{$this->product->name}'",
                'review_reply' => $this->reply->body,
                'product_name' => $this->product->name,
                'view_link' => route('products.review', [$this->product->slug, $this->product->id, $this->review->id]),
                'review_link' => $this->review->view_link,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
