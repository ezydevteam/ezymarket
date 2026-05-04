@php
    $id = $data['uniqueId'];
    $tooltipAttr = $data['tooltipAttr'];
    $wrapperClass = $data['wrapperClass'];
    $icon = $data['icon'];
    $iconSize = $data['iconSize'];
    $showBadge = $data['showBadge'];
    $unreadMessages = $data['unreadMessages'];
    $formattedBadgeCount = $data['formattedBadgeCount'];
    $showLabel = $data['showLabel'];
    $labelClass = $data['labelClass'];
    $label = $data['label'];
    $isAuthenticated = $data['isAuthenticated'];
    $dataUnread = isset($data['unreadMessages']) ? $data['unreadMessages'] : 0;
@endphp

<div id="{{ $id }}" class="header-messages position-relative">
    <a href="javascript:void(0);"
        class="header-chat-icon"
        id="chatIcon"
        {!! $tooltipAttr !!}>
        <div class="{{ $wrapperClass }}">
            <div class="position-relative">
                <i class="bi {{ $icon }} {{ $iconSize }}"></i>
                @auth
                    @if($showBadge && $unreadMessages > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $formattedBadgeCount }}
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
        @themeInclude('blocks.header.partials.message-dropdown')
    @endauth

    @guest
        {{-- Guest Message Dropdown --}}
        <div class="chat-dropdown" id="chatDropdown">
            <div class="text-center text-gray px-3 py-5">
                <p><i class="bi {{ $icon }} fs-1 mb-3"></i></p>
                <p class="mb-2">{{ translate('Please log in to view your messages.') }}</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ translate('Log In') }}</a>
            </div>
        </div>
    @endguest
</div>
