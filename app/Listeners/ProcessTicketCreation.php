<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminNewTicketEmail;
use App\Models\Admin;

class ProcessTicketCreation
{
    public function handle(TicketCreated $event)
    {
        $ticket = $event->ticket;

        $admins = Admin::systemAccess()->active()->get();
        foreach ($admins as $admin) {
            dispatch(new SendAdminNewTicketEmail($admin, $ticket));
        }

        $title = translate('New Ticket [#:id]', ['id' => $ticket->id]);
        $image = asset('images/notifications/ticket.png');
        $link = route('admin.tickets.show', $ticket->id);
        Notification::sendAdminNotification($title, $image, $link);
    }
}


















