<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductHistory;
use App\Models\User;

class ProductUpdateStatusNotification extends BaseNotification
{
    public $status;
    public $reason;
    public $relationship;
    public $product;
    public $user;

    public function __construct(Product $product, User $user, string $status, ProductHistory $reason = null, string $relationship = null)
    {
        $this->product = $product;
        $this->user = $user;
        $this->status = $status;
        $this->reason = $reason;
        $this->relationship = $relationship;
    }

    protected function isProductSELLER()
    {
        return $this->user->id == $this->product->SELLER->id;
    }

    public function getNotificationPreference(): string
    {
        if ($this->isProductSELLER() && in_array($this->status, ['approved', 'rejected'])) {
            return 'product_update_' . $this->status;
        }

        return 'product_update';
    }

    public function toArray($notifiable)
    {
        $data = [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'preview_image' => $this->product->thumbnail_url,
            'timestamp' => now()->toISOString(),
        ];

        switch ($this->status) {
            case 'approved':
                if ($this->isProductSELLER()) {
                    $data['type'] = 'product_update_approved';
                    $data['title'] = 'Product Update Approved';
                    $data['message'] = "Your updated product '{$this->product->name}' has been approved";
                    $data['action_url'] = route('user.product.index', $this->product->id);
                    $data['icon'] = 'check';
                    $data['color'] = 'success';
                } else {
                    // For buyers, followers, and favorited users
                    $messages = [
                        'buyer' => "An product you purchased '{$this->product->name}' has been updated",
                        'follower' => "{$this->product->SELLER->full_name} has updated the product '{$this->product->name}'",
                        'favorited' => "An product you favorited '{$this->product->name}' has been updated",
                    ];

                    $titles = [
                        'buyer' => 'Purchased product Updated',
                        'follower' => 'Seller product Updated',
                        'favorited' => 'Favorited product Updated',
                    ];

                    $icons = [
                        'buyer' => 'bag-check',
                        'follower' => 'user-check',
                        'favorited' => 'heart',
                    ];

                    $data['type'] = 'product_update';
                    $data['title'] = $titles[$this->relationship] ?? 'product Updated';
                    $data['message'] = $messages[$this->relationship] ?? "product '{$this->product->name}' has been updated";
                    $data['Seller_name'] = $this->product->SELLER->full_name;
                    $data['version'] = $this->product->version;
                    $data['action_url'] = $this->product->view_link;
                    $data['icon'] = $icons[$this->relationship] ?? 'refresh';
                    $data['color'] = 'info';
                }
                break;

            case 'rejected':
                $data['type'] = 'product_update_rejected';
                $data['title'] = 'product Update Rejected';
                $data['message'] = "Your update for '{$this->product->name}' has been rejected";
                $data['reason'] = $this->reason;
                $data['action_url'] = route('user.product.index', $this->product->id);
                $data['icon'] = 'x';
                $data['color'] = 'error';
                break;
        }

        return $data;
    }

    public function getEmailData()
    {
        $shortCodes = [
            'product_name' => $this->product->name,
            'website_name' => @settings('general')->site_name,
        ];

        switch ($this->status) {
            case 'approved':
                if ($this->isProductSELLER()) {
                    $template = 'Seller_product_update_approved';
                    $shortCodes['Seller_username'] = $this->user->username;
                    $shortCodes['product_preview_image'] = '<img src="' . $this->product->getImageLink() . '" width="100%"/>';
                    $shortCodes['product_link'] = $this->product->view_link;
                } else {
                    // For buyers, followers, and favorited users
                    $messages = [
                        'buyer' => "An product you purchased '{$this->product->name}' has been updated",
                        'follower' => "{$this->product->SELLER->full_name} has updated the product '{$this->product->name}'",
                        'favorited' => "An product you favorited '{$this->product->name}' has been updated",
                    ];

                    $titles = [
                        'buyer' => 'Purchased product Updated',
                        'follower' => 'Seller product Updated',
                        'favorited' => 'Favorited product Updated',
                    ];

                    $template = 'product_updated';
                    $shortCodes['user_username'] = $this->user->full_name;
                    $shortCodes['Seller_username'] = $this->product->SELLER->full_name;
                    $shortCodes['subject'] = $titles[$this->relationship] ?? 'product Updated';
                    $shortCodes['message'] = $messages[$this->relationship] ?? "product '{$this->product->name}' has been updated";
                    $shortCodes['product_version'] = $this->product->version;
                    $shortCodes['action_url'] = $this->product->view_link;
                }
                break;

            case 'rejected':
                $template = 'Seller_product_update_rejected';
                $shortCodes['Seller_username'] = $this->user->username;
                $shortCodes['product_preview_image'] = '<img src="' . $this->product->getImageLink() . '" width="100%"/>';
                $shortCodes['product_link'] = $this->product->view_link;
                $shortCodes['rejection_reason'] = $this->reason->body;
                break;

            default:
                return null;
        }

        return [
            'template' => $template,
            'shortcodes' => $shortCodes
        ];
    }
}
