<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductHistory;
use App\Models\User;

class ProductSubmissionStatusNotification extends BaseNotification
{
    public $status;
    public $reason;
    public $product;
    public $user;

    public function __construct(Product $product, User $user, string $status, ProductHistory $reason = null)
    {
        $this->product = $product;
        $this->user = $user;
        $this->status = $status;
        $this->reason = $reason;
    }

    protected function isProductSELLER()
    {
        return $this->user->id == $this->product->SELLER->id;
    }

    public function getNotificationPreference(): string
    {
        if ($this->isProductSELLER() && $this->status === 'approved') {
            return 'product_approved';
        }

        return 'product_rejection';
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
                    $data['type'] = 'product_approved';
                    $data['title'] = 'Product Approved';
                    $data['message'] = "Congratulations! Your submitted product '{$this->product->name}' has been approved";
                    $data['action_url'] = url('/products/' . $this->product->slug . '/' . $this->product->id);
                    $data['icon'] = 'check-circle';
                    $data['color'] = 'success';
                } else {
                    $data['type'] = 'new_product';
                    $data['title'] = 'New product Added';
                    $data['message'] = "Your following seller {$this->product->SELLER->full_name} added a new product '{$this->product->name}'";
                    $data['action_url'] = url('/products/' . $this->product->slug . '/' . $this->product->id);
                    $data['icon'] = 'check-circle';
                    $data['color'] = 'success';
                }
                break;

            case 'needs_revision':
                $data['type'] = 'product_revision';
                $data['title'] = 'Product Revision Required';
                $data['message'] = "Your product '{$this->product->name}' requires some changes. You can resubmit it after addressing the requested revisions.";
                $data['action_url'] = route('user.product.edit', $this->product->id);
                $data['reason'] = $this->reason;
                $data['icon'] = 'exclamation';
                $data['color'] = 'warning';
                break;

            case 'rejected':
                $data['type'] = 'product_rejection';
                $data['title'] = 'Product Rejected';
                $data['message'] = "Your product '{$this->product->name}' has been rejected";
                $data['action_url'] = route('user.product.index');
                $data['reason'] = $this->reason;
                $data['icon'] = 'x-circle';
                $data['color'] = 'error';
                break;
        }

        return $data;
    }

    public function getEmailData()
    {
        $shortCodes = [
            'Seller_username' => $this->isProductSELLER() ? $this->user->full_name : $this->product->SELLER->full_name,
            'product_name' => $this->product->name,
            'product_preview_image' => '<img src="' . $this->product->getImageLink() . '" width="100%"/>',
            'website_name' => @settings('general')->site_name,
        ];

        switch ($this->status) {
            case 'approved':
                if ($this->isProductSELLER()) {
                    $template = 'Seller_product_approved';
                    $shortCodes['product_link'] = $this->product->view_link;
                } else {
                    $template = 'follower_new_product';
                    $shortCodes['follower_username'] = $this->user->full_name;
                    $shortCodes['product_link'] = $this->product->view_link;
                }
                break;

            case 'needs_revision':
                $template = 'Seller_product_revision_required';
                $shortCodes['product_id'] = $this->product->id;
                $shortCodes['product_edit_link'] = route('user.product.edit', $this->product->id);
                $shortCodes['rejection_reason'] = $this->reason->body;
                break;

            case 'rejected':
                $template = 'Seller_product_rejected';
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
