<?php

namespace App\Events;

use App\Models\Product\Product;
use Illuminate\Queue\SerializesModels;

/**
 * Product Resubmitted Event
 *
 * This event is fired when a product that was previously rejected or required
 * changes has been resubmitted for review by the seller.
 *
 * Event Flow:
 * 1. Seller updates and resubmits product after review feedback
 * 2. Event is fired with the resubmitted Product model
 * 3. Listeners can send notifications to admins/editors
 * 4. Review process can be initiated
 *
 * @package App\Events
 * @see \App\Models\Product
 */
class ProductResubmitted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Product $product The product that has been resubmitted
     */
    public function __construct(
        public Product $product
    ) {
    }
}

















