<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Product\Product;
use App\Models\Product\ProductDiscount;
use Carbon\Carbon;

class ProductDiscountNotification extends BaseNotification
{
    public $discount;
    public $product;
    public $relationship;
    public $notifiableUser;

    public function __construct(Product $product, ProductDiscount $discount, User $user, string $relationship = null)
    {
        $this->product = $product;
        $this->discount = $discount;
        $this->relationship = $relationship;
        $this->notifiableUser = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'product_discount';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_discount',
            'title' => 'Special Discount!',
            'message' => "The product '{$this->product->name}' you {$this->getRelationshipText()} is now {$this->getDiscountMessage()}! Offer ends on "
                . $this->getEndingAt()->format('M d, Y'),
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'preview_image' => $this->product->thumbnail_url,
            'starting_date' => $this->getStartingAt()->format('M d, Y'),
            'ending_date' => $this->getEndingAt()->format('M d, Y'),
            'relationship' => $this->relationship,
            'discount_percentage' => $this->discount->extended_percentage,
            'action_url' => $this->product->view_link,
            'timestamp' => now()->toISOString(),
            'icon' => 'percent',
            'color' => 'success',
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'product_discount',
            'shortcodes' => [
                'Seller_username' => $this->product->SELLER->full_name,
                'user_username' => $this->notifiableUser->full_name,
                'message' => "The product '{$this->product->name}' you {$this->getRelationshipText()} is now {$this->getDiscountMessage()}! Offer ends on "
                    . $this->getEndingAt()->format('M d, Y'),
                'starting_date' => $this->getStartingAt()->format('M d, Y'),
                'ending_date' => $this->getEndingAt()->format('M d, Y'),
                'discount_percentage' => $this->discount->extended_percentage,
                'product_name' => $this->product->name,
                'product_link' => $this->product->view_link,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }

    /**
     * Get relationship text for messages
     */
    private function getRelationshipText(): string
    {
        $relationship = $this->relationship;

        $relationshipTexts = [
            'buyer' => 'purchased',
            'follower' => 'follow the seller of',
            'favorited' => 'favorited'
        ];

        return $relationshipTexts[$relationship] ?? 'are interested in';
    }

    /**
     * Helper: get discount message
     */
    private function getDiscountMessage(): string
    {
        return $this->discount->withExtended()
            ? "Up to {$this->discount->extended_percentage}% OFF"
            : "{$this->discount->regular_percentage}% OFF";
    }

    /**
     * Helper: ensure ending_at is Carbon instance
     */
    private function getEndingAt(): Carbon
    {
        return $this->discount->ending_at instanceof Carbon
            ? $this->discount->ending_at
            : Carbon::parse($this->discount->ending_at);
    }

    /**
     * Helper: ensure starting_at is Carbon instance
     */
    private function getStartingAt(): Carbon
    {
        return $this->discount->starting_at instanceof Carbon
            ? $this->discount->starting_at
            : Carbon::parse($this->discount->starting_at);
    }
}
