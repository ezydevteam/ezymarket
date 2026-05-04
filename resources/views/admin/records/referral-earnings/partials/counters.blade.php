<div class="row g-4 mb-4">
    @foreach([
    'total' => ['title' => translate('Total Earnings'), 'icon' => 'bi-wallet2', 'color' => 'primary', 'link' =>
    route('admin.records.referral-earnings.index')],
    'active' => ['title' => translate('Active Earnings'), 'icon' => 'bi-check-circle', 'color' => 'success', 'link' =>
    route('admin.records.referral-earnings.index', ['status' => \App\Enums\ReferralEarningStatus::ACTIVE->value])],
    'refunded' => ['title' => translate('Refunded Earnings'), 'icon' => 'bi-arrow-return-left', 'color' => 'orange',
    'link' => route('admin.records.referral-earnings.index', ['status' =>
    \App\Enums\ReferralEarningStatus::REFUNDED->value])],
    'cancelled' => ['title' => translate('Cancelled Earnings'), 'icon' => 'bi-x-circle', 'color' => 'danger', 'link' =>
    route('admin.records.referral-earnings.index', ['status' => \App\Enums\ReferralEarningStatus::CANCELLED->value])]
    ] as $key => $item)
    <div class="col-12 col-sm-6 col-lg-3">
        <x-counter-card :title="$item['title']" :count="numberFormat($counters[$key]['total'])"
            :percent="$counters[$key]['percent']" :amount="getAmount((float) $counters[$key]['amount'])"
            :icon="$item['icon']" :color="$item['color']" :link="$item['link']" />
    </div>
    @endforeach
</div>
