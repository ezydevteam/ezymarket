@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('section', translate('My Products'))
@section('title', $product->name)

@section('content')
    @php
        $chartsConfig = [
        'sales' => $charts['sales'],
        'views' => $charts['views'],
        'license' => $licenseDistribution,
        'geo' => ['data' => []],
        ];
        $chartsConfig['geo']['data'][] = [translate('Country'), translate('Sales')];
        foreach ($geoCountries as $geoCountry) {
        $chartsConfig['geo']['data'][] = [$geoCountry->country, (int) $geoCountry->total_count];
        }
    @endphp
    <div id="chart-data-provider" data-chart-data="{{ json_encode($chartsConfig) }}" class="d-none"></div>
    <div id="productStatistics" class="ajax-tabs">
        @themeInclude('userpanel.products.includes.tabs-nav')
        <div class="ajax-tabs-content p-2">
            <div class="row align-items-center g-3 mb-4">
                <div class="col">
                    <h4 class="mb-0 fw-bold">{{ translate('Product Statistics') }}</h4>
                    <p class="text-muted small mb-0">{{ translate('Insights and detailed analytics for') }} {{
                        $product->name }}</p>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-sync-statistics"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('user.product.statistics.recalculate', $product->id) }}">
                        <i class="bi bi-arrow-clockwise me-2"></i>{{ translate('Sync Statistics') }}
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('user.product.export.statistics', ['id' => $product->id, 'format' => 'pdf', 'period' => $currentPeriod]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-file-earmark-pdf me-2"></i>{{ translate('Export PDF') }}
                    </a>
                </div>
                <div class="col-auto">
                    @themeInclude('userpanel.partials.period-select', ['entity' => $product])
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="stats-metric-card text-danger p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="fw-light text-uppercase mb-0">{{ translate('Total Sales') }}</h5>
                            <div class="icon-circle icon-circle-md bg-danger-subtle text-danger">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ numberFormat($counters['total_sales'] ?? 0) }}</h2>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stats-metric-card text-primary p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="fw-light text-uppercase mb-0">{{ translate('Sales Amount') }}</h5>
                            <div class="icon-circle icon-circle-md bg-primary-subtle text-primary">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ getAmount($counters['total_sales_amount'] ?? 0) }}</h2>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stats-metric-card text-success p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="fw-light text-uppercase mb-0">{{ translate('Approx. Revenue') }}</h5>
                            <div class="icon-circle icon-circle-md bg-success-subtle text-success">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ getAmount($counters['total_earnings'] ?? 0) }}</h2>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stats-metric-card text-secondary p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="fw-light text-uppercase mb-0">{{ translate('Total Views') }}</h5>
                            <div class="icon-circle icon-circle-md bg-secondary-subtle text-secondary">
                                <i class="bi bi-eye fs-4"></i>
                            </div>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ numberFormat($counters['total_views'] ?? 0) }}</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Main Sales Chart --}}
                <div class="col-12 col-lg-8">
                    <div class="userpanel-card p-4 h-100">
                        <h5 class="stats-section-title">{{ translate('Sales Performance') }}</h5>
                        <div class="stats-chart-wrapper">
                            <canvas id="sales-chart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- License Distribution Pie Chart --}}
                <div class="col-12 col-lg-4">
                    <div class="userpanel-card p-4 h-100">
                        <h5 class="stats-section-title">{{ translate('License Distribution') }}</h5>
                        <div class="pie-chart-container">
                            <canvas id="license-pie-chart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Views Statistics --}}
                <div class="col-12 col-lg-7">
                    <div class="userpanel-card p-4 h-100">
                        <h5 class="stats-section-title">{{ translate('Views Trend') }}</h5>
                        <div class="stats-chart-wrapper">
                            <canvas id="views-chart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Top Referrals --}}
                <div class="col-12 col-lg-5">
                    <div class="userpanel-card p-4 h-100">
                        <h5 class="stats-section-title">{{ translate('Sales by Location') }}</h5>
                        <div class="chart" id="countries-chart"></div>
                    </div>
                </div>

                {{-- Top Countries List --}}
                <div class="col-12 col-md-6">
                    <div class="userpanel-card h-100">
                        <div class="card-body">
                            <h5 class="stats-section-title">{{ translate('Top Purchasing Countries') }}</h5>
                            <div class="mt-3 px-3">
                                @forelse ($topPurchasingCountries as $topPurchasingCountry)
                                <div class="stats-data-row d-flex justify-content-between align-items-center px-2">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-flag-wrapper me-3">
                                            <img src="{{ countryFlag($topPurchasingCountry->country) }}"
                                                alt="{{ countries($topPurchasingCountry->country) }}">
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark">{{ countries($topPurchasingCountry->country)
                                                }}</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                        {{ getAmount($topPurchasingCountry->total_seller_earning) ?? 0 }}
                                    </span>
                                </div>
                                @empty
                                <div class="text-center py-5 opacity-75">
                                    <i class="bi bi-bar-chart-line fs-1 text-muted mb-3"></i>
                                    <p class="text-muted mb-0">{{ translate('No data available') }}</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Referral Sources List --}}
                <div class="col-12 col-md-6">
                    <div class="userpanel-card h-100">
                        <div class="card-body">
                            <h5 class="stats-section-title">{{ translate('Top Referral Sources') }}</h5>
                            <div class="mt-3 px-3">
                                @forelse($referrals as $referral)
                                <div class="stats-data-row d-flex justify-content-between align-items-center px-2">
                                    <div class="text-truncate" style="max-width: 70%;">
                                        <span class="fw-semibold text-dark">{{ $referral->referrer ?: translate('Direct /
                                            Unknown') }}</span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                                        {{ numberFormat($referral->total_views ?? 0) }} {{ translate('views') }}
                                    </span>
                                </div>
                                @empty
                                <div class="text-center py-5 opacity-75">
                                    <i class="bi bi-bar-chart-line fs-1 text-muted mb-3"></i>
                                    <p class="text-muted mb-0">{{ translate('No data available') }}</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

