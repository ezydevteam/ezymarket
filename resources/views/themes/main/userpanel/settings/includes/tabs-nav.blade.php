<div class="ajax-tabs-wrapper position-relative mb-4">
    <button class="tabs-nav-control prev d-none" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="ajax-tabs-nav">
        <a href="{{ route('user.settings.account') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.account') ? 'current' : '' }}">
            <i class="bi bi-person-gear"></i>
            <span>{{ translate('Account') }}</span>
        </a>
        <a href="{{ route('user.settings.profile') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.profile') ? 'current' : '' }}">
            <i class="bi bi-person-badge"></i>
            <span>{{ translate('Profile') }}</span>
        </a>
        <a href="{{ route('user.settings.password') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.password') ? 'current' : '' }}">
            <i class="bi bi-lock"></i>
            <span>{{ translate('Password') }}</span>
        </a>
        <a href="{{ route('user.settings.2fa') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.2fa') ? 'current' : '' }}">
            <i class="bi bi-shield-lock"></i>
            <span>{{ translate('2FA') }}</span>
        </a>
        @if (@$settings->actions->id_verification)
            <a href="{{ route('user.settings.id-verification') }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.settings.id-verification') ? 'current' : '' }}">
                <i class="bi bi-person-check"></i>
                <span>{{ translate('ID Verification') }}</span>
            </a>
        @endif
        <a href="{{ route('user.settings.badges') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.badges') ? 'current' : '' }}">
            <i class="bi bi-stars"></i>
            <span>{{ translate('Badges') }}</span>
        </a>
        @if (authUser()->isSeller())
            <a href="{{ route('user.settings.payout') }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.settings.payout') ? 'current' : '' }}">
                <i class="bi bi-send-plus"></i>
                <span>{{ translate('Payout') }}</span>
            </a>
        @endif
        @if (isPremiumAvailable())
            <a href="{{ route('user.settings.premium') }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.settings.premium') ? 'current' : '' }}">
                <i class="bi bi-gem"></i>
                <span>{{ translate('Premium') }}</span>
            </a>
        @endif
        <a href="{{ route('user.settings.notification.preferences') }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.settings.notification.preferences') ? 'current' : '' }}">
            <i class="bi bi-bell"></i>
            <span>{{ translate('Notifications') }}</span>
        </a>
        @if(settings('chatbox')->status)
            <a href="{{ route('user.settings.chatbox.blocked-users') }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.settings.chatbox.blocked-users') ? 'current' : '' }}">
                <i class="bi bi-chat"></i>
                <span>{{ translate('Chatbox') }}</span>
            </a>
        @endif
        @if (@$settings->actions->api)
            <a href="{{ route('user.settings.api-key') }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.settings.api-key') ? 'current' : '' }}">
                <i class="bi bi-code-slash"></i>
                <span>{{ translate('API Key') }}</span>
            </a>
        @endif
    </div>
    <button class="tabs-nav-control next d-none" type="button"><i class="bi bi-chevron-right"></i></button>
</div>
