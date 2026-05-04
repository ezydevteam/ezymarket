<?php

namespace App\Listeners;

use App\Events\TicketReplyCreated;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminNewTicketReplyEmail;
use App\Models\Admin;

class ProcessTicketReplyCreation
{
    public function handle(TicketReplyCreated $event)
    {
        $ticketReply = $event->ticketReply;
        $ticket = $ticketReply->ticket;

        $admins = Admin::systemAccess()->active()->get();
        foreach ($admins as $admin) {
            dispatch(new SendAdminNewTicketReplyEmail($admin, $ticketReply));
        }

        $title = translate('New Ticket Reply [#:id]', ['id' => $ticket->id]);
        $image = asset('images/notifications/reply.png');
        $link = route('admin.tickets.show', $ticket->id);
        Notification::sendAdminNotification($title, $image, $link);
    }
}


















