<div class="row g-4 mb-4">
    @php
        $items = [
            'total' => [
                'title' => translate('Total Tickets'),
                'icon' => 'bi-ticket-perforated',
                'color' => 'primary',
                'count' => numberFormat($counters['total_tickets']),
                'percent' => $counters['total_percent'],
                'link' => route('admin.tickets.index')
            ],
            'opened' => [
                'title' => translate('Opened Tickets'),
                'icon' => 'bi-folder2-open',
                'color' => 'success',
                'count' => numberFormat($counters['opened_tickets']),
                'percent' => $counters['opened_percent'],
                'link' => route('admin.tickets.index', ['status' => \App\Enums\TicketStatus::OPENED->value])
            ],
            'closed' => [
                'title' => translate('Closed Tickets'),
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'count' => numberFormat($counters['closed_tickets']),
                'percent' => $counters['closed_percent'],
                'link' => route('admin.tickets.index', ['status' => \App\Enums\TicketStatus::CLOSED->value])
            ],
            'trash' => [
                'title' => translate('Archived Tickets'),
                'icon' => 'bi-archive',
                'color' => 'warning',
                'count' => numberFormat(\App\Models\Support\Ticket::onlyTrashed()->count()),
                'description' => translate('Tickets in trash'),
                'link' => route('admin.tickets.trash.index')
            ],
        ];
    @endphp

    @foreach($items as $key => $item)
        <div class="col-12 col-sm-6 col-lg-3">
            <x-counter-card 
                :title="$item['title']"
                :count="$item['count']"
                :percent="$item['percent'] ?? 0"
                :description="$item['description'] ?? null"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
            />
        </div>
    @endforeach
</div>
