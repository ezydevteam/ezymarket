<div class="drop-down user-menu {{ $menu_class ?? '' }}"
    data-dropdown data-dropdown-position="{{ $menu_position ?? '' }}">
    <div class="drop-down-btn {{ $btn_class ?? '' }}">
        <span>
            <img src="{{ authUser()->avatar_url }}" alt="{{ authUser()->username }}" class="user-img">
        </span>
        <span class="text-truncate {{ $username_class ?? 'd-none' }}">
            {{ authUser()->username }}
        </span>
    </div>
    <div class="drop-down-menu justify-content-start text-start bg-white shadow-lg border-0 rounded-4 overflow-hidden">
        {{-- Conditional User Header --}}
        @if (authUser()->isDataCompleted())
            <div class="p-3 border-bottom-dashed">
                <div class="d-flex align-items-center">
                    <img src="{{ authUser()->avatar_url }}" alt="{{ authUser()->username }}"
                        class="user-avatar user-avatar-sm rounded me-2">
                    <div class="overflow-hidden">
                        <div class="text-dark fw-medium text-truncate fs-14">{{ authUser()->full_name }}</div>
                        <div class="text-gray-700 fs-12">{{ hideInDemo(authUser()->email) }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="py-2">
            <a href="{{ authUser()->profile_link }}" class="drop-down-item">
                <i class="bi bi-person me-2"></i>
                {{ translate('My Profile') }}
            </a>

            <a href="{{ route('user.wallet.index') }}" class="drop-down-item">
                <i class="bi bi-wallet2 me-2"></i>
                {{ translate('My Wallet') }}
            </a>

            <a href="{{ route('user.purchase.index') }}" class="drop-down-item">
                <i class="bi bi-bag-check me-2"></i>
                {{ translate('Purchases') }}
            </a>

            <div class="drop-down-divider my-2"></div>

            {{-- Growth & Role Management --}}
            @if (!authUser()->isSeller() && $settings->actions->become_a_seller)
                <a href="{{ route('user.become_seller') }}" class="drop-down-item text-primary">
                    <i class="bi bi-person-plus me-2"></i>
                    {{ translate('Become a Seller') }}
                </a>
            @endif

            @if (isPremiumAvailable() && !authUser()->isPremiumMember())
                <a href="{{ route('premium.plans') }}" class="drop-down-item text-success">
                    <i class="bi bi-gem me-2"></i>
                    {{ translate('Upgrade Premium') }}
                </a>
            @endif

            @if (!authUser()->isSeller() || (isPremiumAvailable() && !authUser()->isPremiumMember()))
                <div class="drop-down-divider my-2"></div>
            @endif

            {{-- Seller Tools --}}
            @if (authUser()->isSeller())
                <a href="{{ route('user.dashboard') }}" class="drop-down-item">
                    <i class="bi bi-layout-split me-2"></i>
                    {{ translate('My Dashboard') }}
                </a>
                <a href="{{ route('user.product.index') }}" class="drop-down-item">
                    <i class="bi bi-collection me-2"></i>
                    {{ translate('My Products') }}
                </a>
                <a href="{{ route('user.payout.index') }}" class="drop-down-item">
                    <i class="bi bi-bank me-2"></i>
                    {{ translate('Payout Requests') }}
                </a>
                <div class="drop-down-divider my-2"></div>
            @endif

            <a href="{{ route('user.settings.profile') }}" class="drop-down-item">
                <i class="bi bi-gear me-2"></i>
                {{ translate('Settings') }}
            </a>

            <a href="javascript:void(0)" class="drop-down-item text-danger logout-trigger">
                <i class="bi bi-box-arrow-right me-2"></i>
                {{ translate('Logout') }}
            </a>
        </div>
        <form action="{{ route('logout') }}" method="POST" id="logout-form">@csrf</form>
    </div>
</div>
