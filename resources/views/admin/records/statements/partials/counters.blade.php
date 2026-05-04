<div class="row g-4 mb-4">
    @php
        $items = [
            'total' => [
                'title' => translate('Total Transactions'),
                'icon' => 'bi-arrow-left-right',
                'color' => 'primary',
                'amount' => getAmount((float) $counters['total']['amount']),
                'link' => route('admin.records.statements.index')
            ],
            'credit' => [
                'title' => translate('Total Credited'),
                'icon' => 'bi-plus-circle',
                'color' => 'success',
                'amount' => getAmount((float) $counters['credit']['amount']),
                'link' => route('admin.records.statements.index', ['type' => \App\Enums\StatementType::CREDIT->value])
            ],
            'debit' => [
                'title' => translate('Total Debited'),
                'icon' => 'bi-dash-circle',
                'color' => 'danger',
                'amount' => getAmount((float) $counters['debit']['amount']),
                'link' => route('admin.records.statements.index', ['type' => \App\Enums\StatementType::DEBIT->value])
            ],
            'net_revenue' => [
                'title' => translate('Net Revenue'),
                'icon' => 'bi-wallet2',
                'color' => 'purple',
                'amount' => getAmount((float) $counters['net_revenue']['amount']),
                'count' => $counters['net_revenue']['total'] . '%',
                'description' => translate('Revenue growth rate'),
                'link' => route('admin.records.statements.index')
            ],
        ];
    @endphp

    @foreach($items as $key => $item)
        <div class="col-12 col-sm-6 col-lg-3">
            <x-counter-card 
                :title="$item['title']"
                :count="$item['count'] ?? numberFormat($counters[$key]['total'])"
                :percent="$counters[$key]['percent'] ?? 0"
                :amount="$item['amount'] ?? null"
                :description="$item['description'] ?? null"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
            />
        </div>
    @endforeach
</div>
