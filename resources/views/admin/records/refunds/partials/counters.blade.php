<div class="row g-4 mb-4">
    @php
        $items = [
            'total' => [
                'title' => translate('Total Refunds'),
                'icon' => 'bi-arrow-return-left',
                'color' => 'primary',
                'amount' => getAmount((float) $counters['total']['amount']),
                'link' => route('admin.records.refunds.index')
            ],
            'pending' => [
                'title' => translate('Pending'),
                'icon' => 'bi-clock-history',
                'color' => 'warning',
                'amount' => getAmount((float) $counters['pending']['amount']),
                'link' => route('admin.records.refunds.index', ['status' => \App\Enums\RefundStatus::PENDING->value])
            ],
            'accepted' => [
                'title' => translate('Accepted'),
                'icon' => 'bi-check-circle',
                'color' => 'success',
                'amount' => getAmount((float) $counters['accepted']['amount']),
                'link' => route('admin.records.refunds.index', ['status' => \App\Enums\RefundStatus::ACCEPTED->value])
            ],
            'declined' => [
                'title' => translate('Declined'),
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'amount' => getAmount((float) $counters['declined']['amount']),
                'link' => route('admin.records.refunds.index', ['status' => \App\Enums\RefundStatus::DECLINED->value])
            ],
        ];
    @endphp

    @foreach($items as $key => $item)
        <div class="col-12 col-sm-6 col-lg-3">
            <x-counter-card 
                :title="$item['title']"
                :count="numberFormat($counters[$key]['total'])"
                :percent="$counters[$key]['percent']"
                :amount="$item['amount'] ?? null"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
            />
        </div>
    @endforeach
</div>
