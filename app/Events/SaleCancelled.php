<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Queue\SerializesModels;

/**
 * Sale Cancelled Event
 *
 * This event is fired when a sale is cancelled by buyer, seller, or admin.
 *
 * Event Flow:
 * 1. Sale is cancelled (refund, dispute, or buyer/seller action)
 * 2. Event is fired with the  Sale model
 * 3. Listeners can send notifications to affected parties
 * 4. Refund processes can be triggered
 *
 * @package App\Events
 * @see \App\Models\Sale
 */
class SaleCancelled
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Sale $sale The cancelled sale
     */
    public function __construct(
        public Sale $sale
    ) {
    }
}


















