@php
    $cartUrl = $data['cartUrl'];
    $uniqueId = $data['uniqueId'];
    $btnClass = $data['btnClass'];
    $loginClass = $data['loginClass'];
    $attrs = $data['attrs'];
    $tooltipAttr = $data['tooltipAttr'];
    $icon = $data['icon'];
    $iconSize = $data['iconSize'];
    $showCount = $data['showCount'];
    $cartProductsCount = $data['cartProductsCount'];
    $showLabel = $data['showLabel'];
    $labelWrapperClass = $data['labelWrapperClass'];
    $cartLabel = $data['cartLabel'];
    $showTotal = $data['showTotal'];
    $amount = $data['amount'];
    $viewMode = $data['viewMode'];
    $requireLogin = $data['requireLogin'];
    $isLogged = $data['isLogged'];
    $offcanvasId = $data['offcanvasId'] ?? 'offcanvasCart';
@endphp

<a href="{{ $cartUrl }}"
    id="{{ $uniqueId }}"
    class="header-cart-icon align-items-center {{ $btnClass }} {{ $loginClass }}"
    {!! $attrs !!}>

    <div class="position-relative" {!! $tooltipAttr !!}>
        <i class="bi {{ $icon }} {{ $iconSize }}"></i>
        @if($showCount)
            <span class="cart-counter position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger{{ $cartProductsCount == 0 ? ' d-none' : '' }}">
                {{ $cartProductsCount }}
                <span class="visually-hidden">items</span>
            </span>
        @endif
    </div>

    @if($showLabel)
        <div class="{{ $labelWrapperClass }}">
            <span>{{ $cartLabel }}</span>
            @if($showTotal)
                <span class="fw-semibold fs-6">{{ $amount }}</span>
            @endif
        </div>
    @elseif($showTotal)
        <span class="ms-2 fw-semibold fs-6">{{ $amount }}</span>
    @endif
</a>

@if($viewMode === 'offcanvas' && (!$requireLogin || $isLogged))
    @push('footer_content')
        @themeInclude('blocks.header.partials.cart-offcanvas', ['offcanvasId' => $offcanvasId])
    @endpush
@endif
