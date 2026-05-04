<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin New Ticket Email
 *
 * Notifies administrators when a new support ticket is created
 * by a user.
 *
 * Notification Details:
 * - Template: admin_new_ticket
 * - Trigger: User creates a support ticket
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminNewTicketEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $ticket Ticket instance
     */
    public function __construct($admin, $ticket)
    {
        parent::__construct(
            notifiable: $admin,
            template: 'admin_new_ticket',
            data: [
                'username' => $ticket->user->full_name,
                'ticket_id' => $ticket->id,
                'subject' => $ticket->subject,
                'category' => $ticket->category->name,
                'link' => route('admin.tickets.show', $ticket->id),
                'date' => dateFormat($ticket->created_at),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.ticket.created'
        );
    }
}



















