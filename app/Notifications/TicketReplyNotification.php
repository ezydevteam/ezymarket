<?php

namespace App\Notifications;

use App\Models\Support\{Ticket, TicketReply};
use App\Models\User;

class TicketReplyNotification extends BaseNotification
{
    public $ticket;
    public $ticketReply;

    public function __construct(Ticket $ticket, TicketReply $ticketReply, User $user)
    {
        $this->ticket = $ticket;
        $this->ticketReply = $ticketReply;
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'support_ticket_response';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'support_ticket_response',
            'title' => 'New Reply to Your Ticket',
            'message' => "A new response has been added to your support ticket #{$this->ticket->id}",
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'action_url' => route('user.ticket.show', $this->ticket->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'chat-square-dots',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'new_ticket_reply',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'ticket_id' => $this->ticket->id,
                'reply_message' => $this->ticketReply->body,
                'link' => route('user.ticket.show', $this->ticket->id),
                'date' => dateFormat($this->ticket->created_at),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















