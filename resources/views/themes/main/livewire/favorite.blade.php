@php
    // If the provided class is a full class string (contains spaces or starts with btn-), use it directly.
    // Otherwise, treat it as a shorthand for 'btn-[class]'.
    $isFullClassString = str_contains($btnClass, ' ') || str_contains($btnClass, 'btn-');
    $customStyles = $isFullClassString ? $btnClass : "btn-{$btnClass}";
    $roundedBtn = str_contains($btnClass, 'rounded-pill') ? 'rounded-pill' : '';

    // Set active styles based on favorite status
    $activeStyles = $isFavorite ? 'btn-primary' : $customStyles;
@endphp

<div class="d-inline-block">
    @if (authUser())
        <button wire:click="addToFavorite"
            type="button"
            class="btn {{ $activeStyles }} {{ $roundedBtn }} btn-sm favorite-btn"
            title="{{ $isFavorite ? translate('Remove from Favorites') : translate('Add to Favorites') }}"
            wire:loading.attr="disabled">
            <i class="bi {{ $isFavorite ? 'bi-heart-fill text-white' : 'bi-heart' }}"
               wire:loading.class="wire-loading"
               wire:target="addToFavorite"></i>
        </button>
    @else
        <a href="{{ route('login') }}"
            class="needs-login-modal btn btn-sm {{ $activeStyles }} {{ $roundedBtn }} favorite-btn"
            title="Favorite">
            <i class="bi bi-heart"></i>
        </a>
    @endif
</div>
