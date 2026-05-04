<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductComment;
use App\Models\Product\ProductCommentReply;
use App\Models\User;

class ProductCommentReplyNotification extends BaseNotification
{
    public $comment;
    public $reply;
    public $replier;
    public $replyReceiver;
    public $product;

    public function __construct(Product $product, ProductComment $comment, ProductCommentReply $reply, User $replier, User $replyReceiver)
    {
        $this->product = $product;
        $this->comment = $comment;
        $this->reply = $reply;
        $this->replier = $replier;
        $this->replyReceiver = $replyReceiver;
        $this->notifiableUser = $replyReceiver;
    }

    public function getNotificationPreference(): string
    {
        return 'product_comment_reply';
    }

    protected function isProductSELLER()
    {
        return $this->replyReceiver->id == $this->product->SELLER->id;
    }

    public function toArray($notifiable)
    {
        $replyPreview = strlen($this->reply->body) > 100
            ? substr($this->reply->body, 0, 100) . '...'
            : $this->reply->body;

        if ($this->isproductSELLER()) {
            $title = 'New Reply on Your product';
            $message = "{$this->replier->full_name} replied to a comment on your product '{$this->product->name}'";
        } else {
            $title = 'New Comment Reply';
            $message = "{$this->replier->full_name} replied to your comment on '{$this->product->name}'";
        }

        return [
            'type' => 'product_comment_reply',
            'title' => $title,
            'message' => $message,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'comment_id' => $this->comment->id,
            'reply_id' => $this->reply->id,
            'reply_preview' => $replyPreview,
            'replier_id' => $this->replier->id,
            'replier_name' => $this->replier->full_name,
            'preview_image' => $this->replier->avatar_url,
            'action_url' => route('products.comment', [$this->product->slug, $this->product->id, $this->comment->id]),
            'timestamp' => now()->toISOString(),
            'icon' => 'reply',
            'color' => 'info',
            'is_product_SELLER' => $this->isproductSELLER()
        ];
    }

    public function getEmailData()
    {
        $isProductSELLER = $this->isProductSELLER();
        $templateAlias = $isProductSELLER ? 'Seller_product_comment_reply' : 'product_comment_reply';
        $userName = $isProductSELLER ? $this->replier->full_name : $this->replyReceiver->full_name;

        return [
            'template' => $templateAlias,
            'shortcodes' => [
                'Seller_username' => $this->product->SELLER->full_name,
                'user_username' => $userName,
                'comment_reply' => $this->reply->body,
                'product_name' => $this->product->name,
                'product_link' => $this->product->view_link,
                'comment_link' => route('products.comment', [$this->product->slug, $this->product->id, $this->comment->id]),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}
