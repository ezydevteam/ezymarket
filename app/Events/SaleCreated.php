<?php

namespace App\Events;

use App\Models\Sale;
use App\Models\Financial\Transaction;
use App\Models\Support\SupportPackage;
use Illuminate\Queue\SerializesModels;

/**
 * Sale Created Event
 *
 * This event is fired when a new sale is successfully created.
 *
 * Event Flow:
 * 1. Buyer completes purchase of a product
 * 2. Event is fired with Sale, Transaction, and support details
 * 3. Listeners send notifications to buyer and seller
 * 4. Download access is granted
 * 5. Support details are recorded
 *
 * @package App\Events
 * @see \App\Models\Sale
 * @see \App\Models\Transaction
 */
class SaleCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Sale $sale The created sale
     * @param Transaction $transaction The associated transaction
     * @param object|null $support The support details for this sale (from transaction product)
     */
    public function __construct(
        public Sale $sale,
        public Transaction $transaction,
        public object|null $support = null
    ) {}
}
