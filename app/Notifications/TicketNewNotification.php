<?php

namespace App\Notifications;

use App\Models\Support\Ticket;
use App\Models\User;

class TicketNewNotification extends BaseNotification
{
    public $ticket;

    public function __construct(Ticket $ticket, User $user)
    {
        $this->ticket = $ticket;
        $this->user  = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'new_support_ticket';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_support_ticket',
            'title' => 'New Ticket Created',
            'message' => "Your new support ticket #{$this->ticket->id} has been created successfully",
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'action_url' => route('user.ticket.show', $this->ticket->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'check-circle',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'new_ticket',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'ticket_id' => $this->ticket->id,
                'subject' => $this->ticket->subject,
                'category' => $this->ticket->category->name,
                'link' => route('user.ticket.show', $this->ticket->id),
                'date' => dateFormat($this->ticket->created_at),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















