<div class="row g-4 mb-4">
    @php
        $items = [
            'total' => [
                'title' => translate('Total Earnings'),
                'icon' => 'bi-wallet2',
                'color' => 'primary',
                'amount' => getAmount((float) $counters['total']['amount']),
                'link' => route('admin.records.premium-earnings.index')
            ],
            'premium_members' => [
                'title' => translate('Premium Members'),
                'icon' => 'bi-award',
                'color' => 'warning',
                'link' => route('admin.premium.members.index')
            ],
            'premium_products' => [
                'title' => translate('Premium Products'),
                'icon' => 'bi-box-seam',
                'color' => 'success',
                'amount' => getAmount((float) $counters['premium_products']['amount']),
                'link' => route('admin.products.index', ['premium' => 1])
            ],
            'premium_sellers' => [
                'title' => translate('Premium Sellers'),
                'icon' => 'bi-people',
                'color' => 'purple',
                'description' => translate(':count premium sales', ['count' => numberFormat($counters['premium_sellers']['sales'])]),
                'link' => route('admin.roles.users.index', ['seller' => 1])
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
                :description="$item['description'] ?? null"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
            />
        </div>
    @endforeach
</div>
