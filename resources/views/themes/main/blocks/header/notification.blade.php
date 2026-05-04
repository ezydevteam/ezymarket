@php
    $id = $data['uniqueId'];
    $tooltipAttr = $data['tooltipAttr'];
    $wrapperClass = $data['wrapperClass'];
    $icon = $data['icon'];
    $iconSize = $data['iconSize'];
    $showBadge = $data['showBadge'];
    $formattedBadgeCount = $data['formattedBadgeCount'];
    $showLabel = $data['showLabel'];
    $labelClass = $data['labelClass'];
    $label = $data['label'];
@endphp

<div id="{{ $id }}" class="header-notifications position-relative">
    <a href="javascript:void(0)"
        class="header-notification-icon"
        id="notificationBell"
        {!! $tooltipAttr !!}>

        <div class="{{ $wrapperClass }}">
             <div class="position-relative">
                <i class="bi {{ $icon }} {{ $iconSize }}"></i>

                @auth
                    @if($showBadge)
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                    </span>
                    @endif
                @endauth
             </div>

             @if($showLabel)
                <span class="{{ $labelClass }}">{{ $label }}</span>
             @endif
        </div>
    </a>

    @auth
        @themeInclude('blocks.header.partials.notification-dropdown')
    @endauth

    @guest
        {{-- Guest Notification Dropdown --}}
        <div class="notification-dropdown" id="notificationDropdown">
            <div class="text-center text-gray px-3 py-5">
                <p><i class="bi bi-bell fs-1 mb-2"></i></p>
                <p class="mb-2">{{ translate('Please log in to view your notifications.') }}</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ translate('Log In') }}</a>
            </div>
        </div>
    @endguest
</div>
