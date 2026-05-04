<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Queue\SerializesModels;

/**
 * Sale Refunded Event
 *
 * This event is fired when a sale is refunded to the buyer.
 *
 * Event Flow:
 * 1. Refund is processed for a sale
 * 2. Event is fired with the refunded Sale model
 * 3. Listeners send refund confirmation emails
 * 4. Seller balance is adjusted
 * 5. Download access may be revoked
 *
 * @package App\Events
 * @see \App\Models\Sale
 */
class SaleRefunded
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Sale $sale The refunded sale
     */
    public function __construct(
        public Sale $sale
    ) {
    }
}


















