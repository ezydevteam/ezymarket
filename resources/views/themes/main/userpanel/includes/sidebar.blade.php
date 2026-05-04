<aside class="userpanel-sidebar">
    <div class="userpanel-sidebar-header">
        <a href="{{ route('home') }}">
            <img src="{{ getSiteLogo() }}" alt="{{ getSiteName() }}" class="userpanel-sidebar-logo" />
        </a>
    </div>
    <div class="userpanel-sidebar-search">
        <div class="userpanel-search">
            <div class="userpanel-search-input-wrapper">
                <i class="bi bi-search userpanel-search-icon"></i>
                <input type="text" class="userpanel-search-input" placeholder="{{ translate('Search here...') }}"
                    autocomplete="off">
                <button type="button" class="userpanel-search-clear">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="userpanel-search-results"></div>
        </div>
    </div>
    <div class="userpanel-sidebar-menu" data-simplebar>
        <div class="userpanel-sidebar-inner">
            <div class="userpanel-sidebar-links">
                @php
                    $isActive = fn($routes, $class = 'current') => request()->routeIs(...(array) $routes) ? $class : '';
                @endphp

                @if (authUser()->isSeller())
                <div class="userpanel-sidebar-link {{ $isActive('user.dashboard') }}">
                    <a href="{{ route('user.dashboard') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-layout-split"></i>
                        <span>{{ translate('Dashboard') }}</span>
                    </a>
                </div>
                @endif
                <div class="userpanel-sidebar-link {{ $isActive('user.wallet.*') }}">
                    <a href="{{ route('user.wallet.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-wallet2"></i>
                        <span>{{ translate('My Wallet') }}</span>
                    </a>
                </div>

                @if (authUser()->isSeller())
                <div class="userpanel-sidebar-link {{ request()->segment(2) == 'products' || request()->segment(2) == 'product' ? 'active animated ' : '' }} userpanel-toggle" data-toggle>
                    <div class="userpanel-sidebar-link-title toggle-title">
                        <i class="bi bi-collection"></i>
                        <span>{{ translate('My Products') }}</span>
                    </div>
                    <div class="userpanel-sidebar-link-menu ps-4">
                        <div class="userpanel-sidebar-link {{ $isActive('user.product.create') }}">
                            <a href="{{ route('user.product.create') }}" class="userpanel-sidebar-link-title">
                                <span>{{ translate('Create Product') }}</span>
                            </a>
                        </div>
                        <div class="userpanel-sidebar-link {{ $isActive(['user.product.index', 'user.product.edit']) }}">
                            <a href="{{ route('user.product.index') }}" class="userpanel-sidebar-link-title">
                                <span>{{ translate('All Products') }}</span>
                            </a>
                        </div>
                        <div class="userpanel-sidebar-link {{ $isActive('user.product.drafts') }}">
                            <a href="{{ route('user.product.drafts') }}" class="userpanel-sidebar-link-title">
                                <span>{{ translate('Drafts') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="userpanel-sidebar-link {{ $isActive('user.purchase.*') }}">
                    <a href="{{ route('user.purchase.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-bag-check"></i>
                        <span>{{ translate('Purchases') }}</span>
                    </a>
                </div>

                <div class="userpanel-sidebar-link {{ $isActive('user.transaction.*') }}">
                    <a href="{{ route('user.transaction.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-receipt"></i>
                        <span>{{ translate('Transactions') }}</span>
                    </a>
                </div>
                @if (authUser()->isSeller())
                <div class="userpanel-sidebar-link {{ $isActive('user.payout.index') }}">
                    <a href="{{ route('user.payout.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-bank"></i>
                        <span>{{ translate('Payouts') }}</span>
                    </a>
                </div>
                @if (@$settings->referral->status)
                <div class="userpanel-sidebar-link {{ $isActive('user.referrals') }}">
                    <a href="{{ route('user.referrals') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-people"></i>
                        <span>{{ translate('Referrals') }}</span>
                    </a>
                </div>
                @endif
                @endif

                @if (@$settings->actions->refunds)
                <div class="userpanel-sidebar-link {{ $isActive('user.refund.*') }}">
                    <a href="{{ route('user.refund.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-recycle"></i>
                        <span>{{ translate('Refunds') }}</span>
                        @if ($counters['pending_refunds'])
                        <span class="sidebar-counter me-0">{{ limitCounter($counters['pending_refunds']) }}</span>
                        @endif
                    </a>
                </div>
                @endif
                @if (@$settings->ticket->status)
                <div class="userpanel-sidebar-link {{ $isActive('user.ticket.*') }}">
                    <a href="{{ route('user.ticket.index') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-envelope-open"></i>
                        <span>{{ translate('Tickets') }}</span>
                    </a>
                </div>
                @endif

                <!-- Extras -->
                @if (authUser()->isSeller() && isAddonActive('license_verification_tool'))
                <div class="userpanel-sidebar-link {{ request()->segment(2) == 'tools' ? 'active animated ' : '' }} userpanel-toggle"
                    data-toggle>
                    <div class="userpanel-sidebar-link-title toggle-title">
                        <i class="bi bi-tools"></i>
                        <span>{{ translate('Tools') }}</span>
                    </div>
                    <div class="userpanel-sidebar-link-menu">
                        @if (isAddonActive('license_verification_tool'))
                        <div class="userpanel-sidebar-link {{ $isActive('user.tool.license-verification.*') }}">
                            <a href="{{ route('user.tool.license-verification.index') }}"
                                class="userpanel-sidebar-link-title">
                                <span>{{ translate('License Verification') }}</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="userpanel-sidebar-link {{ $isActive('user.settings.*') }}">
                    <a href="{{ route('user.settings.account') }}" class="userpanel-sidebar-link-title">
                        <i class="bi bi-gear"></i>
                        <span>{{ translate('Settings') }}</span>
                    </a>
                </div>
                <div class="userpanel-sidebar-link">
                    <a href="javascript:void(0)" class="userpanel-sidebar-link-title logout-trigger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span> {{ translate('Logout') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="userpanel-sidebar-footer mt-auto p-3 border-top transition-all">
        <div
            class="userpanel-sidebar-footer-menu d-flex align-items-center justify-content-center flex-wrap gap-2 mb-1 fs-12">
            <a href="javascript:void(0)" class="text-gray-700 hover-primary">{{ translate('Help') }}</a>
            <span class="dot-seperator"></span>
            <a href="{{ @settings('links')->privacy_policy_link }}" target="_blank"
                class="text-gray-700 hover-primary">{{ translate('Privacy') }}</a>
            <span class="dot-seperator"></span>
            <a href="{{ @settings('links')->terms_of_use_link }}" target="_blank" class="text-gray-700 hover-primary">{{
                translate('Terms') }}</a>
        </div>
        <div class="userpanel-sidebar-footer-copyright text-muted text-center fs-12">
            &copy; {{ date('Y') }} {{ $settings->general->site_name }}
        </div>
    </div>
</aside>
<div class="userpanel-sidebar-overlay"></div>
