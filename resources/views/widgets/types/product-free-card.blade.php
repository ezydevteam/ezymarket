@if ($product->isFree())
@php
$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp
<div class="widget-product-free-card {{ $titlePadding }}">
    @include('widgets.partials.widget-title', ['title' => $widgetTitle ?? '', 'widgetSettings' => $widgetSettings ??
    []])

    <div class="card-body text-center {{ $cardStyle === 'none' ? 'p-0' : 'p-3' }}">
        <div class="mb-4">
            <div class="mb-3 bg-primary-subtle rounded-circle d-inline-block"
                style="width: 50px; height: 50px; line-height: 50px;">
                <i class="bi bi-gift text-primary fs-3"></i>
            </div>
            <h3 class="text-primary text-uppercase fw-semibold mb-3">{{ translate('Free Product!') }}</h3>
            <p class="text-gray-700 mb-0">
                {{ translate('The seller has offered this product for ')}} <strong>{{ translate('FREE, ')}}</strong>
                <span>{{ translate('you can now download it at no cost.')}}</span>
            </p>
        </div>

        @if ($product->isMainFileExternal())
        <a href="{{ route('products.free.download.external', hash_encode($product->id)) }}" target="_blank"
            class="form-needs-login-modal btn btn-primary btn-md btn-modern rounded-pill w-100">
            <i class="bi bi-download me-2"></i>{{ translate('Download Now') }}
        </a>
        @else
        <form action="{{ route('products.free.download', hash_encode($product->id)) }}" class="form-needs-login-modal"
            method="POST">
            @csrf
            <button class="btn btn-primary btn-md btn-modern fw-semibold rounded-pill w-100">
                <i class="bi bi-download me-2"></i>{{ translate('Download Now') }}
            </button>
        </form>
        @endif

        <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
            <a class="form-needs-login-modal text-gray-700 small hover-primary-underline"
                href="{{ route('products.free.license', encrypt($product->id)) }}" target="_blank">
                {{ translate('License certificate') }}
            </a>
            @if (@$settings->links->free_products_policy_link)
            <span class="text-muted">|</span>
            <a href="{{ @$settings->links->free_products_policy_link }}"
                class="small text-gray-700 hover-primary-underline" target="_blank">
                {{ translate('Free product policy') }}
            </a>
            @endif
        </div>
    </div>
</div>
@endif
