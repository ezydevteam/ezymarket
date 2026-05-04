<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductComment;
use App\Models\Product\ProductCommentReply;
use App\Models\User;

class ProductCommentNotification extends BaseNotification
{
    public $comment;
    public $commentReply;
    public $commenter;
    public $seller;
    public $product;

    public function __construct(Product $product, ProductComment $comment, ProductCommentReply $commentReply, User $commenter, User $seller)
    {
        $this->notifiableUser = $seller;
        $this->product = $product;
        $this->comment = $comment;
        $this->commentReply = $commentReply;
        $this->commenter = $commenter;
    }

    public function getNotificationPreference(): string
    {
        return 'product_comment';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_comment',
            'title' => 'New Comment',
            'message' => "{$this->commenter->full_name} has commented on your product '{$this->product->name}'",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'comment_id' => $this->comment->id,
            'commenter_id' => $this->commenter->id,
            'commenter_name' => $this->commenter->full_name,
            'preview_image' => $this->commenter->avatar_url,
            'action_url' => route('products.comment', [$this->product->slug, $this->product->id, $this->comment->id]),
            'timestamp' => now()->toISOString(),
            'icon' => 'chat-text',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'Seller_product_comment',
            'shortcodes' => [
                'Seller_username' => $this->notifiableUser->full_name,
                'user_username' => $this->commenter->full_name,
                'comment' => $this->commentReply->body,
                'product_name' => $this->product->name,
                'product_link' => $this->product->view_link,
                'comment_link' => route('products.comment', [$this->product->slug, $this->product->id, $this->comment->id]),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
