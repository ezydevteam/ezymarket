<?php

namespace App\Events;

use App\Models\Financial\Payout;
use Illuminate\Queue\SerializesModels;

/**
 * Payout Submitted Event
 *
 * This event is fired when a seller submits a payout request.
 *
 * Event Flow:
 * 1. Seller requests payout of earnings
 * 2. Event is fired with the new Payout model
 * 3. Listeners send notifications to admins for approval
 * 4. Payout enters review queue
 *
 * @package App\Events
 * @see \App\Models\Payout
 */
class PayoutSubmitted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Payout $payout The submitted payout request
     */
    public function __construct(
        public Payout $payout
    ) {
    }
}
