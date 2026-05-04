<header class="user-profile-header pb-0">
    @php
    $containerWidth = @$themeSettings->profile->container_width ?? 'default';
    $defaultBanner = @$themeSettings->profile->profile_default_banner ?? 'images/profile/default-banner.jpg';

    match ($containerWidth) {
        'boxed' => $containerClass = 'container container-boxed',
        'fluid' => $containerClass = 'container-fluid',
        default => $containerClass = 'container container-default',
    };
    @endphp

    <div class="profile-banner"
        style="{{ $user->profile_cover_url ? '--banner-url: url('.$user->profile_cover_url.');' : '--banner-url: url('.asset($defaultBanner).');' }}">
    </div>

    <div class="profile-identity-bar">
        <div class="{{ $containerClass }}">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-3">
                <div class="flex-shrink-0">
                    <div class="avatar-wrapper position-relative mt-n50">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                            class="user-avatar user-avatar-xxl rounded border border-4 border-white shadow-sm bg-white">
                        @php $verifiedBadge = $user->hasVerifiedBadge(); @endphp
                        @if ($verifiedBadge)
                        <div class="icon-circle icon-circle-sm position-absolute bottom-0 end-0 bg-white p-1 shadow-sm"
                            data-bs-toggle="tooltip"
                            data-bs-title="{{ translate('Verified by :site_name', ['site_name' => getSiteName()]) }}">
                            <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}"
                                class="w-100 h-100 object-fit-contain">
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div
                        class="d-flex flex-column flex-md-row align-items-center align-items-md-end justify-content-between gap-4">
                        <div class="text-center text-md-start">
                            <h2 class="fw-bold mb-1 h4">
                                {{ $user->full_name }}
                                @if ($user->firstname || $user->lastname)
                                <span class="text-gray-600 fs-16 fw-normal">{{ '@'.$user->username }}</span>
                                @endif
                            </h2>
                            <div
                                class="d-flex align-items-center justify-content-center justify-content-md-start flex-wrap gap-2 text-gray-700 fs-14">
                                @if($user->location)
                                <span>{{ $user->location }}</span>
                                <span class="dot-seperator"></span>
                                @endif

                                @if ($user->isSeller())
                                @if($user->total_sales > 0)
                                <span class="fw-medium">{{ numberFormat($user->total_sales) }}</span>
                                {{ translate('sales') }}
                                <span class="dot-seperator"></span>
                                @endif

                                @if (@$settings->product->reviews_status && $user->total_reviews > 0)
                                @themeInclude('partials.rating-stars', ['args' => $user, 'counter_only' => true])
                                <span class="dot-seperator"></span>
                                @endif
                                @endif

                                <span>{{ translate('Joined') }} {{ $user->created_at->format('M Y') }}</span>

                                @if ($user->isExclusiveSeller() || $user->isFeaturedSeller())
                                <span class="dot-seperator"></span>
                                <span class="text-success fw-medium">
                                    @if ($user->isExclusiveSeller())
                                    {{ translate('Exclusive Seller') }}
                                    @elseif ($user->isFeaturedSeller())
                                    {{ translate('Featured Seller') }}
                                    @endif
                                </span>
                                @endif

                            </div>
                        </div>

                        {{-- Right: Actions --}}
                        <div class="d-flex align-items-center gap-2 mb-md-1">
                            @if (authUser()?->id !== $user->id && settings('chatbox')->status)
                            <button class="btn btn-outline-dark rounded-pill px-3 message-user-btn"
                                data-user-id="{{ $user->id }}">
                                <i class="bi bi-chat-dots me-2"></i>{{ translate('Message') }}
                            </button>
                            @endif
                            <livewire:follow :user="$user" btnClass="btn-dark rounded-pill" btnPadding="px-4 py-2" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS & SOCIALS ROW --}}
    <div class="profile-tabs-row">
        <div class="{{ $containerClass }}">
            <div class="d-flex align-items-center justify-content-between">
                <nav class="nav profile-tabs ajax-tabs-nav py-0" role="tablist">
                    <a href="{{ $user->profile_link }}" data-ajax-tab="true"
                        class="nav-link ajax-tabs-item {{ request()->routeIs('profile.index') ? 'active' : '' }}">
                        {{ translate('Profile') }}
                    </a>

                    @if ($user->isSeller())
                    <a href="{{ $user->store_link }}" data-ajax-tab="true"
                        class="nav-link ajax-tabs-item {{ request()->routeIs('profile.store') ? 'active' : '' }}">
                        {{ translate('Store') }}
                    </a>

                    @if (@$settings->product->reviews_status)
                    <a href="{{ route('profile.reviews', $user->id) }}" data-ajax-tab="true"
                        class="nav-link ajax-tabs-item {{ request()->routeIs('profile.reviews') ? 'active' : '' }}">
                        {{ translate('Reviews') }}
                    </a>
                    @endif
                    @endif

                    <a href="{{ route('profile.followers', $user->id) }}" data-ajax-tab="true"
                        class="nav-link ajax-tabs-item {{ request()->routeIs('profile.followers') ? 'active' : '' }}">
                        {{ translate('Followers') }}
                    </a>

                    <a href="{{ route('profile.following', $user->id) }}" data-ajax-tab="true"
                        class="nav-link ajax-tabs-item {{ request()->routeIs('profile.following') ? 'active' : '' }}">
                        {{ translate('Following') }}
                    </a>
                </nav>

                {{-- SOCIALS --}}
                <div class="d-flex align-items-center gap-2">
                    @php $socialPlatforms = getSocialPlatforms(); @endphp
                    @foreach($socialPlatforms as $social)
                    @php
                    $platform = $social['name'];
                    $icon = $social['icon'];
                    $title = $social['title'];
                    @endphp
                    @if(!empty($user->basic_info[$platform]))
                    @php
                    $username = $user->basic_info[$platform];
                    $url = socialProfileUrl($platform, $username);
                    @endphp
                    @if($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-outline-secondary icon-circle icon-circle-sm p-3 flex-shrink-0"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ translate($title) }}">
                        <i class="bi {{ $icon }} fs-6"></i>
                    </a>
                    @endif
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>
