@php
    $favoritesUrl = $data['favoritesUrl'];
    $uniqueId = $data['uniqueId'];
    $btnClass = $data['btnClass'];
    $attrs = $data['attrs'];
    $tooltipAttr = $data['tooltipAttr'];
    $icon = $data['icon'];
    $iconSize = $data['iconSize'];
    $showCount = $data['showCount'];
    $favoritesProductsCount = $data['favoritesProductsCount'] ?? 0;
    $showLabel = $data['showLabel'];
    $labelWrapperClass = $data['labelWrapperClass'];
    $favoritesLabel = $data['favoritesLabel'];
@endphp

<a href="{{ $favoritesUrl }}"
    id="{{ $uniqueId }}"
    class="header-favorites-icon align-items-center {{ $btnClass }}"
    {!! $attrs !!}>

    <div class="position-relative" {!! $tooltipAttr !!}>
        <i class="bi {{ $icon }} {{ $iconSize }}"></i>
        @if($showCount)
            <span class="favorites-counter position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger{{ $favoritesProductsCount == 0 ? ' d-none' : '' }}">
                {{ $favoritesProductsCount }}
                <span class="visually-hidden">favorites</span>
            </span>
        @endif
    </div>

    @if($showLabel)
        <div class="{{ $labelWrapperClass }}">
            <span>{{ $favoritesLabel }}</span>
        </div>
    @endif
</a>
