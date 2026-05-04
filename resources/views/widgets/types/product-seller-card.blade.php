{{-- Product Seller Card Widget --}}
@php
$showWidgetTitle = $widgetSettings['show_title'] ?? true;
$widgetTitle = $widgetTitle ?? $widgetSettings['title'] ?? '';
$showAvatar = $widgetSettings['show_avatar'] ?? true;
$widgetDescription = $widgetSettings['description'] ?? '';
$showAvgRatings = $widgetSettings['show_avg_ratings'] ?? true;
$showAvgSales = $widgetSettings['show_total_sales'] ?? true;
$showBadges = $widgetSettings['show_level_badge'] ?? true;
$style = $widgetSettings['style'] ?? 'style-1';
$verifiedBadge = $seller->hasVerifiedBadge();
$sellerLevelBadge = $seller->hasLevelBadge();
$badges = $seller->getAllBadges();

$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp

<div class="widget-product-seller-card {{ $titlePadding }} {{ $style }}">
    @include('widgets.partials.widget-title', ['title' => $widgetTitle ?? '', 'widgetSettings' => $widgetSettings ??
    []])

    <div class="card-body {{ $cardStyle === 'none' ? 'p-0' : 'p-3' }}">
        @if ($style === 'style-1')
        {{-- Style 1: Classic horizontal layout --}}
        <div class="d-flex flex-wrap flex-sm-nowrap align-items-start gap-3">
            @if ($showAvatar)
            <div class="flex-shrink-0">
                <a href="{{ $seller->profile_link }}" class="user-avatar user-avatar-lg rounded-2">
                    <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}">
                </a>
            </div>
            @endif

            <div class="flex-grow-1 d-flex flex-wrap flex-md-nowrap align-items-start justify-content-between gap-2">
                <div>
                    <h5 class="mb-1 fw-medium">
                        <a href="{{ $seller->profile_link }}" class="text-dark hover-primary">
                            {{ $seller->username }}
                            @if ($verifiedBadge)
                            <span class="verified-badge" data-bs-toggle="tooltip"
                                data-bs-title="{{ translate('Verified seller') }}">
                                <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}">
                            </span>
                            @endif
                        </a>
                    </h5>
                    <div class="d-flex align-items-center flex-wrap gap-2 small">
                        @if ($showAvgRatings && @$productSettings->reviews_status && $seller->avg_reviews > 0 &&
                        $seller->total_reviews > 0)
                        @themeInclude('partials.rating-stars', ['args' => $seller, 'counter_only' => true])
                        @endif
                        @if ($showAvgSales && $seller->total_sales > 0)
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cart-check text-primary me-1"></i>
                            <span class="fw-medium text-dark">{{ numberFormat($seller->total_sales) }}</span>
                            <span class="text-gray-700 ms-1">{{ translate('Sales') }}</span>
                        </div>
                        @endif
                    </div>
                    @if ($showBadges && $badges->isNotEmpty())
                    <div class="seller-badges d-flex align-items-center flex-wrap gap-1 mt-2">
                        @foreach($badges as $userBadge)
                        <img src="{{ $userBadge->badge->image_url }}" alt="{{ $userBadge->badge->name }}"
                            title="{{ $userBadge->badge->title ?? $userBadge->badge->name }}" width="20" height="20"
                            data-bs-toggle="tooltip">
                        @endforeach
                    </div>
                    @endif
                    @if ($widgetDescription)
                    <p class="small text-gray-700 mt-2 mb-0">{{ $widgetDescription }}</p>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (($widgetSettings['show_follow_button'] ?? true) && !$seller->isSuspended())
                    <livewire:follow :user="$seller" :iconButton="true" />
                    @endif
                    @if (($widgetSettings['show_contact_button'] ?? true) && authUser()?->id != $seller->id &&
                    settings('chatbox')->status)
                    <button class="btn btn-sm btn-outline-secondary btn-modern needs-login-modal message-user-btn"
                        data-user-id="{{ $seller->id }}" title="{{ translate('Contact seller') }}">
                        <i class="bi bi-send"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @elseif ($style === 'style-2')
        {{-- Style 2: Centered card layout --}}
        <div class="text-center modern-centered">
            @if ($showAvatar)
            <div class="product-seller-avatar mb-2">
                <a href="{{ $seller->profile_link }}"
                    class="user-avatar user-avatar-xl rounded border border-primary border-2 p-1 d-inline-block">
                    <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}">
                </a>
            </div>
            @endif

            <h5 class="mb-2 fw-bold">
                <a href="{{ $seller->profile_link }}"
                    class="text-dark hover-primary d-flex align-items-center justify-content-center gap-1">
                    {{ $seller->username }}
                    @if ($verifiedBadge)
                    <span class="verified-badge" data-bs-toggle="tooltip"
                        data-bs-title="{{ translate('Verified seller') }}">
                        <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}">
                    </span>
                    @endif
                </a>
            </h5>

            @if ($showBadges && $badges->isNotEmpty())
            <div class="seller-badges d-flex align-items-center justify-content-center flex-wrap gap-1 mb-3">
                @foreach($badges as $userBadge)
                <img src="{{ $userBadge->badge->image_url }}" alt="{{ $userBadge->badge->name }}"
                    title="{{ $userBadge->badge->title ?? $userBadge->badge->name }}" width="20" height="20"
                    data-bs-toggle="tooltip">
                @endforeach
            </div>
            @endif

            <div
                class="d-flex align-items-center justify-content-center gap-3 py-2 px-3 bg-light rounded-pill d-inline-flex mb-3 small">
                @if ($showAvgRatings && @$productSettings->reviews_status && $seller->avg_reviews > 0 &&
                $seller->total_reviews > 0)
                @themeInclude('partials.rating-stars', ['args' => $seller, 'counter_only' => true])
                @endif
                @if ($showAvgSales && $seller->total_sales > 0 && $showAvgRatings)
                <span class="text-muted">|</span>
                @endif
                @if ($showAvgSales && $seller->total_sales > 0)
                <div class="d-flex align-items-center">
                    <i class="bi bi-cart-check text-primary me-1"></i>
                    <span class="fw-medium text-dark">{{ numberFormat($seller->total_sales) }}</span>
                    <span class="text-gray-700 ms-1">{{ translate('Sales') }}</span>
                </div>
                @endif
            </div>

            @if ($widgetDescription)
            <p class="small text-gray-700 mb-3 mx-auto">{{ $widgetDescription }}</p>
            @endif

            <div class="d-flex align-items-center justify-content-center gap-2">
                @if (($widgetSettings['show_follow_button'] ?? true) && !$seller->isSuspended())
                <livewire:follow :user="$seller" :iconButton="false" btnClass="btn-outline-primary rounded-pill" />
                @endif
                @if (($widgetSettings['show_contact_button'] ?? true) && authUser()?->id != $seller->id &&
                settings('chatbox')->status)
                <button
                    class="btn btn-sm btn-outline-secondary btn-modern rounded-pill px-3 needs-login-modal message-user-btn"
                    data-user-id="{{ $seller->id }}">
                    <i class="bi bi-send me-1"></i>{{ translate('Message') }}
                </button>
                @endif
            </div>
        </div>
        @elseif ($style === 'style-3')
        {{-- Style 3: Compact inline layout with stats --}}
        <div class="d-flex gap-3">
            @if ($showAvatar)
            <div class="flex-shrink-0">
                <a href="{{ $seller->profile_link }}" class="user-avatar user-avatar-lg rounded">
                    <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}">
                </a>
            </div>
            @endif

            <div class="flex-grow-1 d-flex flex-column">
                <h5 class="mb-1 fw-medium">
                    <a href="{{ $seller->profile_link }}" class="text-dark hover-primary">
                        {{ $seller->username }}
                        @if ($verifiedBadge)
                        <span class="verified-badge" data-bs-toggle="tooltip"
                            data-bs-title="{{ translate('Verified seller') }}">
                            <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}">
                        </span>
                        @endif
                    </a>
                </h5>
                <div class="d-flex align-items-center gap-2 small text-gray-700">
                    @if ($showAvgRatings && @$productSettings->reviews_status && $seller->avg_reviews > 0)
                    @themeInclude('partials.rating-stars', ['args' => $seller, 'counter_only' => true])
                    @endif
                    @if ($showAvgSales && $seller->total_sales > 0)
                    <div class="text-dark">
                        <i class="bi bi-cart-check"></i>
                        {{ $seller->total_sales }}
                        <span class="text-gray-700 small">
                            {{ translate('Sales') }}
                        </span>
                    </div>
                    @endif
                </div>
                @if ($showBadges && $badges->isNotEmpty())
                <div class="seller-badges d-flex align-items-center flex-wrap gap-1 mt-1">
                    @foreach($badges as $userBadge)
                    <img src="{{ $userBadge->badge->image_url }}" alt="{{ $userBadge->badge->name }}"
                        title="{{ $userBadge->badge->title ?? $userBadge->badge->name }}" width="20" height="20"
                        data-bs-toggle="tooltip">
                    @endforeach
                </div>
                @endif
                @if ($widgetDescription)
                <p class="small text-gray-700 mt-1 mb-0 text-truncate">{{ $widgetDescription }}</p>
                @endif

                <div class="d-flex gap-2 mt-2">
                    @if (($widgetSettings['show_follow_button'] ?? true) && !$seller->isSuspended())
                    <livewire:follow :user="$seller" :iconButton="false" btnClass="btn-outline-primary" />
                    @endif
                    @if (($widgetSettings['show_contact_button'] ?? true) && authUser()?->id != $seller->id &&
                    settings('chatbox')->status)
                    <button class="btn btn-sm btn-outline-secondary btn-modern needs-login-modal message-user-btn"
                        data-user-id="{{ $seller->id }}" title="{{ translate('Contact seller') }}">
                        <i class="bi bi-send me-1"></i>{{ translate('Message') }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Custom Services Section (shown for all styles) --}}
        @if (($widgetSettings['show_custom_services'] ?? true) && $product->has_custom_services)
        <div
            class="d-flex align-items-center mt-3 p-3 rounded-3 attribute-item {{ $style === 'style-2' ? 'flex-column justify-content-center text-center gap-1' : 'gap-2' }}">
            <i class="bi bi-ui-checks-grid text-primary fs-5"></i>
            <div class="small">
                <h6 class="fw-semibold mb-0">{{ translate('Need custom services?') }}</h6>
                <a href="{{ $seller->profile_link }}" class="text-primary hover-underline">{{ translate('Request an
                    offer!') }}</a>
            </div>
        </div>
        @endif
    </div>
</div>
