<div class="ajax-tabs-wrapper position-relative">
    <button class="tabs-nav-control prev d-none" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="ajax-tabs-nav">
        @php $activeTab = request('tab', 'overview'); @endphp

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'overview']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'overview' ? 'current' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>{{ translate('Overview') }}</span>
        </a>

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'account']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'account' ? 'current' : '' }}">
            <i class="bi bi-person-gear"></i>
            <span>{{ translate('Account') }}</span>
        </a>

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'profile']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'profile' ? 'current' : '' }}">
            <i class="bi bi-person-badge"></i>
            <span>{{ translate('Profile') }}</span>
        </a>

        @if (isPremiumAvailable())
            <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'premium']) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ $activeTab === 'premium' ? 'current' : '' }}">
                <i class="bi bi-gem"></i>
                <span>{{ translate('Premium') }}</span>
            </a>
        @endif

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'wallet']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'wallet' ? 'current' : '' }}">
            <i class="bi bi-bank2"></i>
            <span>{{ $user->isSeller() ? translate('Wallet & Payout') : translate('Wallet') }}</span>
        </a>

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'security']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'security' ? 'current' : '' }}">
            <i class="bi bi-shield-lock"></i>
            <span>{{ translate('Security') }}</span>
        </a>

        <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'badges']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'badges' ? 'current' : '' }}">
            <i class="bi bi-award"></i>
            <span>{{ translate('Badges') }}</span>
        </a>

        @if ($user->isSeller())
            <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'referrals']) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ $activeTab === 'referrals' ? 'current' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>{{ translate('Referrals') }}</span>
            </a>
        @endif

        @if (@settings('actions')->api)
            <a href="{{ route('admin.roles.users.edit', ['user' => $user->id, 'tab' => 'api-key']) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ $activeTab === 'api-key' ? 'current' : '' }}">
                <i class="bi bi-code-slash"></i>
                <span>{{ translate('API Key') }}</span>
            </a>
        @endif
    </div>
    <button class="tabs-nav-control next d-none" type="button"><i class="bi bi-chevron-right"></i></button>
</div>
