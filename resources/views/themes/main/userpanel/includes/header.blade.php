<nav class="userpanel-nav">
    <div class="userpanel-header-left d-flex align-items-center">
        <button class="btn btn-header userpanel-toggle-btn me-3">
            <i class="bi bi-grid-fill"></i>
        </button>

        <div class="userpanel-search d-none d-xl-block">
            <div class="userpanel-search-input-wrapper">
                <i class="bi bi-search userpanel-search-icon"></i>
                <input type="text" class="userpanel-search-input"
                    placeholder="{{ translate('Search here...') }}" autocomplete="off">
                <button type="button" class="userpanel-search-clear">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="userpanel-search-results"></div>
        </div>

        <a href="{{ route('home') }}" class="logo logo-sm d-none d-sm-inline d-xl-none">
            <img src="{{ asset($themeSettings->general->logo_light) }}"
                alt="{{ @$settings->general->site_name }}" class="userpanel-sidebar-logo" />
        </a>
    </div>

    <div class="userpanel-header-actions d-flex align-items-center gap-2 ms-auto">
        @if ($settings->actions->become_a_seller && !authUser()->isSeller())
            <a href="{{ route('user.become_seller') }}"
                class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none d-lg-inline">
                {{ translate('Become a Seller') }}
            </a>
        @endif

        <div class="header-action-item d-none d-md-block">
            <a href="{{ route('home') }}" class="btn btn-header" title="{{ translate('Home') }}">
                <i class="bi bi-house-door"></i>
            </a>
        </div>

        @if(settings('chatbox')->status)
        <div class="header-action-item">
            <div class="chatbox-wrapper position-relative">
                <button class="btn btn-header chat-dropdown-btn" id="chatIcon"
                    type="button" title="{{ translate('Messages') }}">
                    <i class="bi bi-chat"></i>
                    <span class="header-counter-badge bg-success d-none" id="unreadMessageBadge"></span>
                </button>
                @themeInclude('partials.chatbox-dropdown')
            </div>
        </div>
        @endif

        <div class="header-action-item">
            <div class="notification-wrapper position-relative">
                <button class="notification-bell btn btn-header"
                    id="notificationBell" title="{{ translate('Notifications') }}">
                    <i class="bi bi-bell"></i>
                    <span class="header-counter-badge bg-danger d-none" id="notificationBadge"></span>
                </button>
                @themeInclude('partials.notification-dropdown')
            </div>
        </div>

        @if (!request()->routeIs('portal.products.index') && authUser()->isSeller())
        <div class="header-action-item">
            <button class="btn btn-header" id="addproductModelLabel"
                data-bs-toggle="modal" data-bs-target="#addNewproductModal"
                title="{{ translate('Add new product') }}">
                <i class="bi bi-plus-circle"></i>
            </button>
        </div>
        @endif

        <div class="header-action-user">
            @themeInclude('partials.user-menu', [
                'menu_class' => 'userpanel-avatar',
                'btn_class' => 'btn btn-header'
            ])
        </div>
    </div>
</nav>

@themeInclude('partials.modals.navbar-modal')

