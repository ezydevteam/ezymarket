@if (isPremiumAvailable() && $product->isPremium())
@php
$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp
<div class="widget-product-premium-card {{ $titlePadding }}">
    @include('widgets.partials.widget-title', ['title' => $widgetTitle ?? '', 'widgetSettings' => $widgetSettings ??
    []])

    <div class="card-body text-center {{ $cardStyle === 'none' ? 'p-0' : 'p-3' }}">
        @if (authUser()?->isPremiumMember())
        <div class="mb-4">
            <div class="mb-3 bg-primary-subtle rounded-circle d-inline-block"
                style="width: 50px; height: 50px; line-height: 50px;">
                <i class="bi bi-gem text-primary fs-3"></i>
            </div>
            <h3 class="text-primary text-uppercase mb-3">{{ translate('Premium Download') }}</h3>
            <p class="text-gray-700 mb-0">
                {{ translate('You are subscribed to a premium plan. You can download this product directly.') }}
            </p>
        </div>

        @if ($product->isMainFileExternal())
        <a href="{{ route('products.premium.download.external', hash_encode($product->id)) }}" target="_blank"
            class="btn btn-primary btn-md btn-modern rounded-pill w-100 {{ $product->seller->id == authUser()?->id ? 'disabled' : '' }}">
            <i class="bi bi-download me-2"></i>{{ translate('Download') }}
        </a>
        @else
        <form action="{{ route('products.premium.download', hash_encode($product->id)) }}" method="POST">
            @csrf
            <button
                class="btn btn-primary btn-md btn-modern rounded-pill w-100 {{ $product->seller->id == authUser()?->id ? 'disabled' : '' }}">
                <i class="bi bi-download me-2"></i>{{ translate('Download') }}
            </button>
        </form>
        @endif

        @if ($product->seller->id != authUser()?->id)
        <div class="text-center mt-3">
            <a href="{{ route('products.premium.license', encrypt($product->id)) }}"
                class="text-gray-700 small hover-primary-underline" target="_blank">
                {{ translate('License certificate') }}
            </a>
        </div>
        @endif
        @else
        <div class="mb-4">
            <div class="mb-3 bg-primary-subtle rounded-circle d-inline-block"
                style="width: 50px; height: 50px; line-height: 50px;">
                <i class="bi bi-gem text-primary fs-3"></i>
            </div>
            <h3 class="mb-3">{{ translate('Get unlimited downloads!') }}</h3>
            <p class="text-gray-700 mb-0">
                {{ translate('Subscribe to access unlimited downloads of themes, videos, graphics, plugins, and more
                premium assets for your creative needs.') }}
            </p>
        </div>
        <a href="{{ route('premium.plans') }}" class="btn btn-premium btn-md btn-modern rounded-pill w-100">
            {{ translate('Join Premium to Download') }}</a>

        @if (@$settings->premium->terms_link)
        <div class="text-center mt-3">
            <a href="{{ @$settings->premium->terms_link }}" class="text-gray-700 small hover-primary-underline"
                target="_blank">
                {{ translate('Learn more about membership') }}
            </a>
        </div>
        @endif
        @endif
    </div>
</div>
@endif
