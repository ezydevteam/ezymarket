<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin New Ticket Reply Email
 *
 * Notifies administrators when a user replies to an existing
 * support ticket.
 *
 * Notification Details:
 * - Template: admin_new_ticket_reply
 * - Trigger: User adds a reply to a ticket
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminNewTicketReplyEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $ticketReply Ticket reply instance
     */
    public function __construct($admin, $ticketReply)
    {
        $ticket = $ticketReply->ticket;

        parent::__construct(
            notifiable: $admin,
            template: 'admin_new_ticket_reply',
            data: [
                'username' => $ticket->user->full_name,
                'ticket_id' => $ticket->id,
                'reply_message' => $ticketReply->body,
                'link' => route('admin.tickets.show', $ticket->id),
                'date' => dateFormat($ticket->created_at),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.ticket.reply'
        );
    }
}



















