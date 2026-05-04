@extends('themes.main.userpanel.layout')
@section('title', translate('Dashboard'))
@section('header_title', translate('Performance Overview'))
@section('description', translate('In-depth analytics and store performance insights'))
@section('header_actions')
<div class="d-flex align-items-center gap-2">
    @themeInclude('userpanel.partials.period-select')
    <a href="{{ route('user.export.dashboard', ['format' => 'pdf', 'period' => $currentPeriod]) }}"
       class="btn btn-outline-secondary">
        <i class="bi bi-file-earmark-pdf me-2"></i>{{ translate('Export PDF') }}
    </a>
</div>
@endsection

@section('content')
    <div id="userDashboard" class="product-dashboard">
        {{-- Metric Cards --}}
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="stats-metric-card text-purple p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="text-uppercase mb-0 text-truncate">{{ translate('Total Sales') }}</h5>
                        <div class="icon-circle icon-circle-md bg-purple-subtle text-purple">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ numberFormat($counters['total_sales'] ?? 0) }}</h2>
                    @if(isset($comparisonData['total_sales']) && $previousPeriodLabel)
                        @php $change = $comparisonData['total_sales']; @endphp
                        <div class="stats-comparison mt-1 d-flex align-items-center gap-1 fs-14 fw-semibold {{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                            <i class="bi {{ $change > 0 ? 'bi-arrow-up-short' : ($change < 0 ? 'bi-arrow-down-short' : 'bi-dash') }} fs-5"></i>
                            <span>{{ abs($change) }}%</span>
                            <span class="text-muted text-lowercase fw-normal ms-1 small">{{ translate('vs') }} {{ $previousPeriodLabel }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stats-metric-card text-primary p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="text-uppercase mb-0 text-truncate">{{ translate('Sales Earnings') }}</h5>
                        <div class="icon-circle icon-circle-md bg-primary-subtle text-primary">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ getAmount($counters['sales_earnings'] ?? 0) }}</h2>
                    @if(isset($comparisonData['sales_earnings']) && $previousPeriodLabel)
                        @php $change = $comparisonData['sales_earnings']; @endphp
                        <div class="stats-comparison mt-1 d-flex align-items-center gap-1 fs-14 fw-semibold {{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                            <i class="bi {{ $change > 0 ? 'bi-arrow-up-short' : ($change < 0 ? 'bi-arrow-down-short' : 'bi-dash') }} fs-5"></i>
                            <span>{{ abs($change) }}%</span>
                            <span class="text-muted text-lowercase fw-normal ms-1 small">{{ translate('vs') }} {{ $previousPeriodLabel }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stats-metric-card text-success p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="text-uppercase mb-0 text-truncate">{{ translate('Referral Earnings') }}</h5>
                        <div class="icon-circle icon-circle-md bg-success-subtle text-success">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ getAmount($counters['referrals_earnings'] ?? 0) }}</h2>
                    @if(isset($comparisonData['referrals_earnings']) && $previousPeriodLabel)
                        @php $change = $comparisonData['referrals_earnings']; @endphp
                        <div class="stats-comparison mt-1 d-flex align-items-center gap-1 fs-14 fw-semibold {{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                            <i class="bi {{ $change > 0 ? 'bi-arrow-up-short' : ($change < 0 ? 'bi-arrow-down-short' : 'bi-dash') }} fs-5"></i>
                            <span>{{ abs($change) }}%</span>
                            <span class="text-muted text-lowercase fw-normal ms-1 small">{{ translate('vs') }} {{ $previousPeriodLabel }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stats-metric-card text-secondary p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="text-uppercase mb-0 text-truncate">{{ translate('Total Views') }}</h5>
                        <div class="icon-circle icon-circle-md bg-secondary-subtle text-secondary">
                            <i class="bi bi-eye fs-4"></i>
                        </div>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ numberFormat($counters['total_views'] ?? 0) }}</h2>
                    @if(isset($comparisonData['total_views']) && $previousPeriodLabel)
                        @php $change = $comparisonData['total_views']; @endphp
                        <div class="stats-comparison mt-1 d-flex align-items-center gap-1 fs-14 fw-semibold {{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                            <i class="bi {{ $change > 0 ? 'bi-arrow-up-short' : ($change < 0 ? 'bi-arrow-down-short' : 'bi-dash') }} fs-5"></i>
                            <span>{{ abs($change) }}%</span>
                            <span class="text-muted text-lowercase fw-normal ms-1 small">{{ translate('vs') }} {{ $previousPeriodLabel }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @php
            $chartsConfig = [
                'sales' => $charts['sales'],
                'views' => $charts['views'],
                'license' => $licenseDistribution,
                'refunds' => $refundsDistribution,
                'geo' => ['data' => []],
            ];
            $chartsConfig['geo']['data'][] = [translate('Country'), translate('Sales')];
            foreach ($geoCountries as $geoCountry) {
                $chartsConfig['geo']['data'][] = [$geoCountry->country, (int) $geoCountry->total_count];
            }
        @endphp
        <div id="chart-data-provider" data-chart-data="{{ json_encode($chartsConfig) }}" class="d-none"></div>

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

            {{-- License Distribution --}}
            <div class="col-12 col-lg-4">
                <div class="userpanel-card p-4 h-100">
                    <h5 class="stats-section-title">{{ translate('License Distribution') }}</h5>
                    <div class="pie-chart-container">
                        <canvas id="license-pie-chart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Comparison Chart: Views vs Sales --}}
            <div class="col-12 col-lg-8">
                <div class="userpanel-card p-4 h-100">
                    <h5 class="stats-section-title">{{ translate('Views vs Sales Comparison') }}</h5>
                    <div class="stats-chart-wrapper">
                        <canvas id="comparison-chart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Refunds Distribution --}}
            <div class="col-12 col-lg-4">
                <div class="userpanel-card p-4 h-100">
                    <h5 class="stats-section-title">{{ translate('Active Sales vs Refunds') }}</h5>
                    <div class="pie-chart-container">
                        <canvas id="refunds-pie-chart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Geo Analysis --}}
            <div class="col-12 col-lg-7">
                <div class="userpanel-card p-4 h-100">
                    <h5 class="stats-section-title">{{ translate('Global Sales Distribution') }}</h5>
                    <div class="chart" id="countries-chart"></div>
                </div>
            </div>

            {{-- Top Selling Products --}}
            <div class="col-12 col-lg-5">
                <div class="userpanel-card h-100">
                    <div class="card-body">
                        <h5 class="stats-section-title mb-2">{{ translate('Top Selling Products') }}</h5>
                        <div class="px-3">
                            @forelse ($topSellingProducts as $topSellingProduct)
                                @php $product = $topSellingProduct->product; @endphp
                                <div class="stats-data-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="{{ $product->view_link }}" class="image-fluid image-md flex-shrink-0">
                                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                                        </a>
                                        <div class="overflow-hidden flex-grow-1">
                                            <a href="{{ $product->view_link }}" class="d-block text-dark fw-500">
                                                {{ $product->name }}
                                            </a>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-cart-check"></i>
                                                {{ numberFormat($topSellingProduct->total_sales ?? 0) }} {{ translate('sales') }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                                        {{ getAmount(($topSellingProduct->total_sales ?? 0) * $product->regular_price) }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center opacity-75">
                                    <div class="no-data-placeholder d-flex flex-column align-items-center justify-content-center w-100 min-h-200 h-100">
                                        <i class="bi bi-bar-chart-line fs-1 mb-2 d-block"></i>
                                        <p class="text-muted mb-0">{{ translate('No data available') }}</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Traffic Sources --}}
            <div class="col-12 col-md-6">
                <div class="userpanel-card h-100">
                    <div class="card-body">
                        <h5 class="stats-section-title">{{ translate('Top Traffic Sources') }}</h5>
                        <div class="px-3">
                            @forelse($referrals as $referral)
                                <div class="stats-data-row d-flex justify-content-between align-items-center px-2">
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold text-dark">{{ $referral->referrer ?: translate('Direct / Unknown') }}</span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold flex-grow-0">
                                        {{ numberFormat($referral->total_views ?? 0) }} {{ translate('views') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center opacity-75">
                                    <div class="no-data-placeholder d-flex flex-column align-items-center justify-content-center w-100 min-h-200 h-100">
                                        <i class="bi bi-bar-chart-line fs-1 mb-2 d-block"></i>
                                        <p class="text-muted mb-0">{{ translate('No data available') }}</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Countries List --}}
            <div class="col-12 col-md-6">
                <div class="userpanel-card h-100">
                    <div class="card-body">
                        <h5 class="stats-section-title mb-2">{{ translate('Top Purchasing Countries') }}</h5>
                        <div class="px-3">
                            @forelse ($topPurchasingCountries as $country)
                                <div class="stats-data-row d-flex justify-content-between align-items-center px-2">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-flag-wrapper me-3">
                                            <img src="{{ countryFlag($country->country) }}" alt="{{ countries($country->country) }}">
                                        </div>
                                        <span class="fw-semibold text-dark">{{ countries($country->country) }}</span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                        {{ getAmount($country->total_seller_earning ?? 0) }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center opacity-75">
                                    <div class="no-data-placeholder d-flex flex-column align-items-center justify-content-center w-100 min-h-200 h-100">
                                        <i class="bi bi-bar-chart-line fs-1 mb-2 d-block"></i>
                                        <p class="text-muted mb-0">{{ translate('No data available') }}</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
