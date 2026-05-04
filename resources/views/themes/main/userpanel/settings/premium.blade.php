@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Premium'))

@section('content')
<div class="ajax-tabs">
    @themeInclude('userpanel.settings.includes.tabs-nav')
    <div class="ajax-tabs-content">
        {{-- Header Card --}}
        @if (authUser()->isPremiumMember())
        <div class="card-v px-4 py-3 shadow-sm rounded-4 mb-4">
            <div class="card-v-header border-0 p-0 mb-n1">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                <i class="bi bi-gem"></i>
                            </span>
                            {{ translate('Premium Membership') }}
                        </h5>
                    </div>
                    <div>
                        <a href="{{ route('products.index', ['premium' => 'true']) }}"
                            class="btn btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-rocket-takeoff me-1"></i>
                            {{ translate('Explore Premium Products') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Alerts Section --}}
        @if (authUser()->isPremiumMember())
        @if (authUser()->premium->isAboutToExpire())
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center">
            <div class="icon-circle icon-circle-sm bg-warning bg-opacity-25 text-warning me-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h6 class="alert-heading text-warning fw-bold mb-1">{{ translate('Membership Expiring Soon') }}</h6>
                <p class="mb-0 small opacity-75">{{ translate('Your premium access is nearing its end date. Renew now to
                    avoid interruption.') }}</p>
            </div>
        </div>
        @elseif(authUser()->premium->isExpired())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center">
            <div class="icon-circle icon-circle-sm bg-danger bg-opacity-25 text-danger me-3">
                <i class="bi bi-calendar-x-fill"></i>
            </div>
            <div>
                <h6 class="alert-heading text-danger fw-bold mb-1">{{ translate('Membership Expired') }}</h6>
                <p class="mb-0 small opacity-75">{{ translate('Your access to premium features has ended. Please renew
                    to regain access.') }}</p>
            </div>
        </div>
        @endif
        @endif

        {{-- Content Section --}}
        @if (authUser()->isPremiumMember())
        @php $subscription = authUser()->premium; @endphp
        <div class="card-v px-4 py-4 shadow-sm rounded-4">
            <div class="row align-items-center g-4">
                {{-- Subscription Info --}}
                <div class="col-lg-7">
                    <div
                        class="p-4 rounded-4 bg-primary-subtle border-primary border-opacity-25 position-relative overflow-hidden">
                        <div class="status-ribbon-wrapper">
                            <span
                                class="badge status-ribbon {{ $subscription->isExpired() ? 'bg-danger' : ($subscription->isAboutToExpire() ? 'bg-warning text-dark' : 'bg-success') }} bg-opacity-75 px-3 py-2 rounded-2 shadow-sm border border-white border-opacity-25">
                                <i class="bi bi-shield-check me-1"></i>
                                {{ $subscription->isExpired() ? translate('Expired') : translate('Active') }}
                            </span>
                        </div>

                        <p class="text-gray-600 text-uppercase fw-semibold fs-13 mb-1">{{ translate('Plan Name') }}</p>
                        <h2 class="text-primary fw-bold mb-2">
                            {{ $subscription->plan->name }}
                            <span class="text-dark fs-16 fw-normal ms-2">({{ $subscription->plan->interval_name
                                }})</span>
                        </h2>

                        <div class="d-flex flex-wrap gap-4 mt-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle icon-circle-sm bg-white text-muted me-2 shadow-sm">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div>
                                    <div class="text-gray-600 text-uppercase fw-semibold fs-13">{{
                                        $subscription->isExpired() ? translate('Expired On') : translate('Expiry Date')
                                        }}</div>
                                    <div
                                        class="fw-bold {{ $subscription->isAboutToExpire() ? 'text-warning' : ($subscription->isExpired() ? 'text-danger' : 'text-dark') }}">
                                        @if ($subscription->plan->isLifetime())
                                        ∞ {{ translate('Lifetime Access') }}
                                        @else
                                        {{ dateFormat($subscription->expiry_at) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="col-lg-5">
                    <div class="d-grid gap-3 ps-lg-4">
                        @if (($subscription->isAboutToExpire() || $subscription->isExpired()) &&
                        !$subscription->plan->isFree())
                        @if ($subscription->plan->isActive())
                        <form action="{{ route('premium.subscribe', $subscription->plan->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary btn-md w-100 rounded-3 action-confirm shadow-sm">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                {{ translate('Renew Membership') }}
                            </button>
                        </form>
                        @else
                        <a href="{{ route('premium.plans') }}" class="btn btn-primary btn-md w-100 rounded-3 shadow-sm">
                            <i class="bi bi-arrow-repeat me-2"></i>
                            {{ translate('Renew Membership') }}
                        </a>
                        @endif
                        @endif

                        <a href="{{ route('premium.plans') }}"
                            class="btn btn-outline-primary btn-md w-100 rounded-3 border-dashed">
                            <i class="bi bi-arrow-up-circle me-2"></i>
                            {{ translate('Upgrade My Plan') }}
                        </a>

                        <button data-action="{{ route('user.settings.premium.cancel') }}" data-method="POST"
                            data-text="{{ translate('Are you sure you want to cancel your premium membership?') }}"
                            class="btn btn-link text-danger w-100 text-decoration-none action-confirm py-2">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ translate('Cancel Subscription') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @else
        {{-- Empty State --}}
        <div class="card-v px-4 py-5 shadow-sm rounded-4 text-center">
            <div class="py-4">
                <div class="icon-circle icon-circle-xl bg-primary-subtle text-primary mx-auto mb-4">
                    <i class="bi bi-gem fs-1"></i>
                </div>
                <h2 class="fw-bold mb-3">{{ translate('Unlock Premium Experience') }}</h2>
                <div class="col-lg-6 mx-auto mb-4">
                    <p class="text-gray-600">
                        {{ translate('Upgrade your membership to access exclusive premium products, priority support,
                        and ultimate features designed to boost your productivity.') }}
                    </p>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('premium.plans') }}"
                        class="btn btn-primary btn-lg btn-modern px-5 py-3 rounded-3 shadow-sm">
                        <i class="bi bi-stars me-2"></i>
                        {{ translate('Subscribe Now') }}
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
