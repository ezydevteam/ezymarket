<?php

namespace App\Notifications;

use App\Models\Support\Ticket;
use App\Models\User;

class TicketStatusNotification extends BaseNotification
{
    public $ticket;

    public function __construct(Ticket $ticket, User $user)
    {
        $this->ticket = $ticket;
        $this->user  = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'support_ticket_status';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'support_ticket_status',
            'title' => 'Ticket Status Updated',
            'message' => "Your support ticket #{$this->ticket->id} has been '{$this->ticket->status_name}'.",
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'action_url' => route('user.ticket.show', $this->ticket->id),
            'timestamp' => now()->toISOString(),
            'icon' => 'x-circle',
            'color' => 'error'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'new_ticket_status',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'subject' => $this->ticket->subject,
                'ticket_id' => $this->ticket->id,
                'ticket_status' => $this->ticket->status_name,
                'action_url' => route('user.ticket.show', $this->ticket->id),
                'date_time' => dateFormat($this->ticket->created_at),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }
}

















