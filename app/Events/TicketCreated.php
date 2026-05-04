<?php

namespace App\Events;

use App\Models\Support\Ticket;
use Illuminate\Queue\SerializesModels;

/**
 * Ticket Created Event
 *
 * This event is fired when a new support ticket is created.
 *
 * Event Flow:
 * 1. User creates a new support ticket
 * 2. Event is fired with the new Ticket model
 * 3. Listeners send notifications to admins
 * 4. Ticket enters support queue
 *
 * @package App\Events
 * @see \App\Models\Ticket
 */
class TicketCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Ticket $ticket The newly created ticket
     */
    public function __construct(
        public Ticket $ticket
    ) {
    }
}


















