<?php

namespace App\Events;

use App\Models\Financial\Transaction;
use Illuminate\Queue\SerializesModels;

/**
 * Transaction Paid Event
 *
 * This event is fired when a transaction is successfully paid.
 *
 * Event Flow:
 * 1. Payment gateway confirms successful payment
 * 2. Event is fired with the paid Transaction model
 * 3. Listeners process the sale (create sales records, send emails)
 * 4. Seller earnings are calculated
 * 5. Buyer receives access to purchased products
 *
 * @package App\Events
 * @see \App\Models\Transaction
 */
class TransactionPaid
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction The paid transaction
     */
    public function __construct(
        public Transaction $transaction
    ) {
    }
}


















