<div class="row g-3 mb-4">
    @php
        $counterItems = [
            [
                'title' => translate('Total'),
                'count' => numberFormat($counters['total_payouts']),
                'percent' => $counters['total_payouts_percent'],
                'icon' => 'bi-wallet2',
                'color' => 'primary',
                'link' => route('admin.financial.payouts.index'),
                'comparisonText' => translate('total growth')
            ],
            [
                'title' => translate('Pending'),
                'count' => numberFormat($counters['pending_payouts']),
                'percent' => $counters['pending_payouts_percent'],
                'icon' => 'bi-clock-history',
                'color' => 'warning',
                'link' => route('admin.financial.payouts.index', ['status' => 'pending'])
            ],
            [
                'title' => translate('Approved'),
                'count' => numberFormat($counters['approved_payouts']),
                'percent' => $counters['approved_payouts_percent'],
                'icon' => 'bi-check-circle',
                'color' => 'success',
                'link' => route('admin.financial.payouts.index', ['status' => 'approved'])
            ],
            [
                'title' => translate('Completed'),
                'count' => numberFormat($counters['completed_payouts']),
                'percent' => $counters['completed_payouts_percent'],
                'icon' => 'bi-check2-square',
                'color' => 'info',
                'link' => route('admin.financial.payouts.index', ['status' => 'completed'])
            ],
            [
                'title' => translate('Returned'),
                'count' => numberFormat($counters['returned_payouts']),
                'percent' => $counters['returned_payouts_percent'],
                'icon' => 'bi-arrow-return-left',
                'color' => 'secondary',
                'link' => route('admin.financial.payouts.index', ['status' => 'returned'])
            ],
            [
                'title' => translate('Cancelled'),
                'count' => numberFormat($counters['cancelled_payouts']),
                'percent' => $counters['cancelled_payouts_percent'],
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'link' => route('admin.financial.payouts.index', ['status' => 'cancelled'])
            ],
        ];
    @endphp

    @foreach($counterItems as $item)
        <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
            <x-counter-card 
                :title="$item['title']"
                :count="$item['count']"
                :percent="$item['percent']"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
                :comparisonText="$item['comparisonText'] ?? translate('vs last week')"
            />
        </div>
    @endforeach
</div>
