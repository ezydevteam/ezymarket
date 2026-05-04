<?php

namespace App\Events;

use App\Models\Financial\Transaction;
use Illuminate\Queue\SerializesModels;

/**
 * Transaction Pending Event
 *
 * This event is fired when a new transaction is created and awaiting payment.
 *
 * Event Flow:
 * 1. User initiates checkout and creates transaction
 * 2. Event is fired with the pending Transaction model
 * 3. Listeners can send pending payment notifications
 * 4. Transaction awaits payment gateway confirmation
 *
 * @package App\Events
 * @see \App\Models\Transaction
 */
class TransactionPending
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction The pending transaction
     */
    public function __construct(
        public Transaction $transaction
    ) {
    }
}


















