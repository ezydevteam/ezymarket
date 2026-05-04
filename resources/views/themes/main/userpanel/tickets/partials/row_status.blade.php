<div class="text-center">
    <span class="badge {{ $ticket->status_badge_class }} rounded-pill px-3 py-2 fw-medium border-0">
        <i class="{{ $ticket->status_icon }} me-1"></i> {{ $ticket->status_name }}
    </span>
</div>
