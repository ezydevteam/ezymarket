<div class="d-flex align-items-center gap-3">
    <div class="bg-light p-2 rounded">
        <i class="bi bi-headset text-primary fs-5"></i>
    </div>
    <div class="min-w-0 text-start">
        <a href="{{ route('user.ticket.show', $ticket->id) }}" class="text-dark fw-medium d-block mb-1 hover-primary"
            title="{{ $ticket->subject }}">
            {{ truncateText($ticket->subject, 50) }}
        </a>
        <code class="small text-gray-700 bg-light px-2 py-0 rounded" title="{{ translate('Ticket ID') }}">
            #{{ $ticket->id }}
        </code>
    </div>
</div>
