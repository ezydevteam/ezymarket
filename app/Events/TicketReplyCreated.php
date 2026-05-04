<?php

namespace App\Events;

use App\Models\Support\TicketReply;
use Illuminate\Queue\SerializesModels;

/**
 * Ticket Reply Created Event
 *
 * This event is fired when a reply is added to a support ticket.
 *
 * Event Flow:
 * 1. User or admin adds a reply to a ticket
 * 2. Event is fired with the new TicketReply model
 * 3. Listeners send notifications to ticket participants
 * 4. Ticket status may be updated
 *
 * @package App\Events
 * @see \App\Models\TicketReply
 */
class TicketReplyCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param TicketReply $ticketReply The newly created ticket reply
     */
    public function __construct(
        public TicketReply $ticketReply
    ) {
    }
}


















