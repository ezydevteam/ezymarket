@php
    // If the provided class is a full class string (contains spaces or starts with btn-), use it directly.
    // Otherwise, treat it as a shorthand for 'btn-[class]'.
    $isFullClassString = str_contains($btnClass, ' ') || str_contains($btnClass, 'btn-');
    $customStyles = $isFullClassString ? $btnClass : "btn-{$btnClass}";
    $roundedBtn = str_contains($btnClass, 'rounded-pill') ? 'rounded-pill' : '';
    $btnPadding = '';

    // Set active styles based on follow status and iconButton mode
    $activeStyles = $isFollowing
        ? ('btn-primary ' . $btnPadding)
        : ($iconButton ? ('btn-outline-primary ' . $btnPadding) : ($customStyles . ' ' . $btnPadding));
@endphp

<div class="d-inline-block">
@if (authUser())
    @if ($user->id != authUser()?->id)
        <button
            wire:click="followAction"
            type="button"
            class="btn btn-modern {{ $activeStyles }} {{ $roundedBtn }}"
            title="{{ $isFollowing ? 'Following' : 'Follow' }}"
            wire:loading.attr="disabled">

            <span wire:loading.remove wire:target="followAction">
                @if ($isFollowing)
                    <i class="bi bi-person-check"></i>
                    @if (!$iconButton)
                        <span class="follow-label ms-1">{{ translate('Following') }}</span>
                    @endif
                @else
                    <i class="bi bi-person-plus"></i>
                    @if (!$iconButton)
                        <span class="follow-label ms-1">{{ translate('Follow') }}</span>
                    @endif
                @endif
            </span>

            <span wire:loading wire:target="followAction">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

            </span>
        </button>
    @endif
@else
    <a href="{{ route('login') }}"
        class="btn btn-modern needs-login-modal {{ $activeStyles }} {{ $roundedBtn }}"
        role="button"
        title="Follow">
        <i class="bi bi-person-plus"></i>
        @if (!$iconButton)
            <span class="follow-label ms-1">{{ translate('Follow') }}</span>
        @endif
    </a>
@endif
</div>
