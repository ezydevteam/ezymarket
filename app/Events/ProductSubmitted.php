<?php

namespace App\Events;

use App\Models\Product\Product;
use Illuminate\Queue\SerializesModels;

/**
 * Product Submitted Event
 *
 * This event is fired when a new product is submitted for review by a seller.
 *
 * Event Flow:
 * 1. Seller creates and submits a new product
 * 2. Event is fired with the new Product model
 * 3. Listeners send notifications to admins/editors for review
 * 4. Product enters review queue
 *
 * @package App\Events
 * @see \App\Models\Product
 */
class ProductSubmitted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Product $product The newly submitted product
     */
    public function __construct(
        public Product $product
    ) {
    }
}

















