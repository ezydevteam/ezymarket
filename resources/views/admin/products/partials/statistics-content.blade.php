@php
    $chartsConfig = [
        'sales' => $charts['sales'],
        'views' => $charts['views'],
        'license' => $licenseDistribution,
        'geo' => ['data' => []],
    ];
    $chartsConfig['geo']['data'][] = [translate('Country'), translate('Sales')];
    foreach ($geoCountries as $geoCountry) {
        $chartsConfig['geo']['data'][] = [$geoCountry->country, (int) $geoCountry->total_sales];
    }
@endphp

<div id="chart-data-provider" data-chart-data="{{ json_encode($chartsConfig) }}" class="d-none"></div>
<div class="d-flex align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-0 fw-bold">{{ translate('Product Statistics') }}</h4>
            <p class="text-muted small mb-0">{{ translate('Insights and detailed analytics for') }} {{ $product->name }}</p>
        </div>
    <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" class="btn btn-outline-primary btn-sync-statistics"
            data-id="{{ $product->id }}"
            data-url="{{ route('admin.products.statistics.recalculate', $product->id) }}">
            <i class="bi bi-arrow-clockwise me-2"></i>{{ translate('Sync Statistics') }}
        </button>
        <a href="{{ route('admin.products.statistics.export', ['id' => $product->id, 'format' => 'pdf', 'period' => $currentPeriod]) }}"
            class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf me-2"></i>{{ translate('Export PDF') }}
        </a>
        <div class="flex-nowrap">
            @include('admin.products.partials.period-select', ['product' => $product])
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-3">
        <x-counter-card
            :title="translate('Total Sales')"
            :count="numberFormat($counters['total_sales'] ?? 0)"
            icon="bi-cart-check"
            color="danger"
        />
    </div>
    <div class="col-md-6 col-lg-3">
        <x-counter-card
            :title="translate('Sales Amount')"
            :count="getAmount($counters['total_sales_amount'] ?? 0)"
            icon="bi-currency-dollar"
            color="primary"
        />
    </div>
    <div class="col-md-6 col-lg-3">
        <x-counter-card
            :title="translate('Net Revenue')"
            :count="getAmount($counters['total_earnings'] ?? 0)"
            icon="bi-wallet2"
            color="success"
        />
    </div>
    <div class="col-md-6 col-lg-3">
        <x-counter-card
            :title="translate('Total Views')"
            :count="numberFormat($counters['total_views'] ?? 0)"
            icon="bi-eye"
            color="secondary"
        />
    </div>
</div>

<div class="row g-4">
    {{-- Main Sales Chart --}}
    <div class="col-12 col-lg-8">
        <div class="admin-card p-4 h-100">
            <h5 class="stats-section-title">{{ translate('Sales Performance') }}</h5>
            <div class="stats-chart-wrapper" style="height: 350px;">
                <canvas class="chart-line" data-chart="sales" data-color="#6366f1"></canvas>
            </div>
        </div>
    </div>

    {{-- License Distribution Pie Chart --}}
    <div class="col-12 col-lg-4">
        <div class="admin-card p-4 h-100">
            <h5 class="stats-section-title">{{ translate('License Distribution') }}</h5>
            <div class="pie-chart-container" style="height: 350px;">
                <canvas class="chart-doughnut" data-chart="license" data-cutout="70%"></canvas>
            </div>
        </div>
    </div>

    {{-- Views Statistics --}}
    <div class="col-12 col-lg-7">
        <div class="admin-card p-4 h-100">
            <h5 class="stats-section-title">{{ translate('Views Trend') }}</h5>
            <div class="stats-chart-wrapper" style="height: 350px;">
                <canvas class="chart-line" data-chart="views" data-color="#10b981"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Countries List --}}
    <div class="col-12 col-lg-5">
        <div class="admin-card h-100">
            <div class="card-body p-4">
                <h5 class="stats-section-title">{{ translate('Top Purchasing Countries') }}</h5>
                <div class="mt-3">
                    @forelse ($topPurchasingCountries as $topPurchasingCountry)
                    <div class="stats-data-row d-flex justify-content-between align-items-center px-2 py-3 border-bottom border-light">
                        <div class="d-flex align-items-center">
                            <div class="stats-flag-wrapper me-3">
                                <img src="{{ countryFlag($topPurchasingCountry->country) }}"
                                    alt="{{ countries($topPurchasingCountry->country) }}" width="24">
                            </div>
                            <div>
                                <span class="fw-semibold text-dark">{{ countries($topPurchasingCountry->country) }}</span>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                            {{ getAmount($topPurchasingCountry->total_seller_earning) ?? 0 }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-globe fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">{{ translate('No sales data available') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- World Map --}}
    <div class="col-12 col-lg-7">
        <div class="admin-card p-4 h-100">
            <h5 class="stats-section-title">{{ translate('Sales by Location') }}</h5>
            <div class="chart-wrapper" style="height: 350px;">
                <div class="chart-geo" data-chart="geo"></div>
            </div>
        </div>
    </div>

    {{-- Referral Sources List --}}
    <div class="col-12 col-lg-5">
        <div class="admin-card h-100">
            <div class="card-body p-4">
                <h5 class="stats-section-title">{{ translate('Top Referral Sources') }}</h5>
                <div class="mt-3">
                    @forelse($referrals as $referral)
                    <div class="stats-data-row d-flex justify-content-between align-items-center px-2 py-3 border-bottom border-light">
                        <div class="text-truncate" style="max-width: 70%;">
                            <span class="fw-semibold text-dark">{{ $referral->referrer ?: translate('Direct / Unknown') }}</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                            {{ numberFormat($referral->total_views ?? 0) }} {{ translate('views') }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-globe fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">{{ translate('No referral data available') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
