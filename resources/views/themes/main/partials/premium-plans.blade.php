@php $blockStyle = $blockStyle ?? 'default'; @endphp
<div class="plan {{ $plan->isFeatured() ? 'featured-plan' : '' }}">
    @if ($plan->isFeatured())
    <div class="plan-pro">
        {{ $plan->featured_badge }}
    </div>
    @endif
    <h5 class="plan-title">{{ $plan->name }}</h5>
    <div class="plan-price active mb-2">
        {{ $plan->price_label }}
    </div>
    <p class="plan-text">{{ $plan->description }}</p>
    @php
    $buttonHtml = '';
    ob_start();
    @endphp
    <form action="{{ route('premium.subscribe', $plan->id) }}" method="POST">
        @csrf
        @auth
        @if (authUser()->premium?->premiumPlan?->id == $plan->id)
        @if (authUser()->premium?->isAboutToExpire() && !authUser()->premium?->premiumPlan->isFree())
        <button
            class="btn {{ $plan->isFeatured() ? 'btn-warning' : 'btn-outline-warning' }} rounded-pill action-confirm w-100">
            {{ translate('Renew') }}
        </button>
        @elseif(authUser()->premium?->isExpired() && !authUser()->premium?->premiumPlan->isFree())
        <button
            class="btn {{ $plan->isFeatured() ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill action-confirm w-100">
            {{ translate('Renew') }}
        </button>
        @elseif(authUser()->premium?->isExpired() && authUser()->premium?->premiumPlan->isFree())
        <button class="btn btn-danger rounded-pill w-100" disabled>
            {{ translate('Expired') }}
        </button>
        @else
        <button class="btn {{ $plan->isFeatured() ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill w-100"
            disabled>
            {{ translate('Subscribed') }}
        </button>
        @endif
        @else
        <button
            class="btn {{ $plan->isFeatured() ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill action-confirm w-100">
            {{ $buttonText ?? translate('Start Now') }}
        </button>
        @endif
        @else
        <a href="{{ route('login') }}"
            class="btn {{ $plan->isFeatured() ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill w-100">
            {{ $buttonText ?? translate('Start Now') }}
        </a>
        @endauth
    </form>
    @php
    $buttonHtml = ob_get_clean();

    $featuresHtml = '';
    ob_start();
    @endphp
    <div class="plan-features {{ ($buttonPosition ?? 'before_features') == 'before_features' ? 'mt-4' : 'mb-3' }}">
        @if ($plan->custom_features)
        @foreach ($plan->custom_features as $customFeature)
        <div class="plan-feat">
            <div class="plan-feat-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
            <span>{{ $customFeature }}</span>
        </div>
        @endforeach
        @endif
        <div class="plan-feat fw-medium">
            <div class="plan-feat-icon">
                <i class="bi bi-cloud-download"></i>
            </div>
            @if ($plan->hasUnlimitedDownloads())
            <span>{{ translate('Unlimited downloads') }}</span>
            @else
            <span>{{ translate(':count downloads per day', ['count' => number_format($plan->downloads)]) }}</span>
            @endif
        </div>
    </div>
    @php
    $featuresHtml = ob_get_clean();
    @endphp

    @if(($buttonPosition ?? 'before_features') == 'before_features')
    {!! $buttonHtml !!}
    {!! $featuresHtml !!}
    @else
    {!! $featuresHtml !!}
    {!! $buttonHtml !!}
    @endif
</div>
