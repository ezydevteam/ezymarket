<?php

namespace App\Events;

use App\Models\Product\ProductUpdate;
use Illuminate\Queue\SerializesModels;

/**
 * Product Updated Event
 *
 * This event is fired when a product update is submitted for review.
 *
 * Event Flow:
 * 1. Seller submits an update to an existing product
 * 2. Event is fired with the ProductUpdate model
 * 3. Listeners send notifications to admins/editors for review
 * 4. Update enters review queue
 *
 * @package App\Events
 * @see \App\Models\ProductUpdate
 */
class ProductUpdated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param ProductUpdate $productUpdate The product update submitted for review
     */
    public function __construct(
        public ProductUpdate $productUpdate
    ) {
    }
}

















