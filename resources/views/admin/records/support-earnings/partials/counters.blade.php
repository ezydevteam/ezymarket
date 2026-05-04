<div class="row g-3 mb-4">
    @php
        $counterItems = [
            [
                'title' => translate('Total Earnings'),
                'count' => numberFormat($counters['total']['total']),
                'percent' => $counters['total']['percent'],
                'icon' => 'bi-life-preserver',
                'color' => 'primary',
                'link' => route('admin.records.support-earnings.index'),
                'comparisonText' => translate('vs last week'),
                'subtext' => getAmount($counters['total']['amount'])
            ],
            [
                'title' => translate('Active'),
                'count' => numberFormat($counters['active']['total']),
                'percent' => $counters['active']['percent'],
                'icon' => 'bi-check-circle',
                'color' => 'success',
                'link' => route('admin.records.support-earnings.index', ['status' => \App\Enums\SupportEarningStatus::ACTIVE->value]),
                'comparisonText' => translate('vs last week'),
                'subtext' => getAmount($counters['active']['amount'])
            ],
            [
                'title' => translate('Refunded'),
                'count' => numberFormat($counters['refunded']['total']),
                'percent' => $counters['refunded']['percent'],
                'icon' => 'bi-arrow-counterclockwise',
                'color' => 'warning',
                'link' => route('admin.records.support-earnings.index', ['status' => \App\Enums\SupportEarningStatus::REFUNDED->value]),
                'comparisonText' => translate('vs last week'),
                'subtext' => getAmount($counters['refunded']['amount'])
            ],
            [
                'title' => translate('Cancelled'),
                'count' => numberFormat($counters['cancelled']['total']),
                'percent' => $counters['cancelled']['percent'],
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'link' => route('admin.records.support-earnings.index', ['status' => \App\Enums\SupportEarningStatus::CANCELLED->value]),
                'comparisonText' => translate('vs last week'),
                'subtext' => getAmount($counters['cancelled']['amount'])
            ],
        ];
    @endphp

    @foreach($counterItems as $item)
        <div class="col-12 col-sm-6 col-md-4 col-xxl-3">
            <x-counter-card 
                :title="$item['title']"
                :count="$item['count']"
                :percent="$item['percent']"
                :icon="$item['icon']"
                :color="$item['color']"
                :link="$item['link']"
                :comparisonText="$item['comparisonText']"
            >
                <x-slot name="footer">
                    <div class="mt-2 pt-2 border-top border-light-subtle">
                        <span class="text-muted small">{{ translate('Total Value:') }}</span>
                        <span class="fw-bold text-dark ms-1 small">{{ $item['subtext'] }}</span>
                    </div>
                </x-slot>
            </x-counter-card>
        </div>
    @endforeach
</div>
