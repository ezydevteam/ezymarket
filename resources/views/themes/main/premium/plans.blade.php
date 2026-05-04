@extends('themes.main.layouts.single')
@section('header_title', translate('Premium Plans'))
@section('title', translate('Premium Plans'))
@section('breadcrumbs', Breadcrumbs::render('premium'))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'premium'))
@section('header_v3', true)
@section('main')
    <div class="membership-container my-4">
        @if ($countPremiumPlans > 0)
            @php
                $premiumPlanTabs = [
                    ['premiumPlans' => $weeklyPremiumPlans, 'id' => 'week-tab', 'target' => '#pills-week', 'label' => 'Weekly'],
                    ['premiumPlans' => $monthlyPremiumPlans, 'id' => 'month-tab', 'target' => '#pills-month', 'label' => 'Monthly'],
                    ['premiumPlans' => $yearlyPremiumPlans, 'id' => 'year-tab', 'target' => '#pills-year', 'label' => 'Yearly'],
                    ['premiumPlans' => $lifetimePremiumPlans, 'id' => 'lifetime-tab', 'target' => '#pills-lifetime', 'label' => 'Lifetime'],
                ];

                $availableTabs = collect($premiumPlanTabs)->filter(fn($tab) => $tab['premiumPlans']->count() > 0)->values();
                $activeTab = $availableTabs->first();
                $showSwitcher = $availableTabs->count() > 1;
            @endphp

            @if ($showSwitcher)
                <div class="d-flex justify-content-center mb-5" id="pills-tab" role="tablist">
                    <div class="membership-package-switcher">
                        <div class="membership-package-switcher-inner">
                            @foreach ($availableTabs as $tab)
                                <button
                                    class="membership-package-switcher-item {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $tab['id'] }}"
                                    data-bs-toggle="pill"
                                    data-bs-target="{{ $tab['target'] }}"
                                    role="tab"
                                    aria-controls="{{ ltrim($tab['target'], '#') }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <i class="fa-regular fa-calendar me-2"></i>{{ translate($tab['label']) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="tab-content membership-packages-grid" id="pills-tabContent">
                @foreach ($availableTabs as $tab)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                        id="{{ ltrim($tab['target'], '#') }}"
                        role="tabpanel"
                        aria-labelledby="{{ $tab['id'] }}"
                        tabindex="0">
                        <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 justify-content-center g-4">
                            @foreach ($tab['premiumPlans'] as $premiumPlan)
                                <div class="col">
                                    @include('themes.main.partials.premium-plans', ['plan' => $premiumPlan])
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card-v border p-5 text-center">
                <div class="mb-3">
                    <i class="fa-solid fa-box-open fa-3x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted mb-0">{{ translate('No premium plans available') }}</h5>
                <p class="text-muted small mt-2">{{ translate('Check back later for new plans') }}</p>
            </div>
        @endif

        <div class="mt-5 text-center">
            <a href="{{ route('products.index', ['subscription' => 'true']) }}" class="btn btn-outline-primary btn-lg px-5 shadow-sm">
                <i class="fa-solid fa-crown me-2"></i>
                {{ translate('Explore Premium Products') }}
            </a>
        </div>
    </div>
@endsection

