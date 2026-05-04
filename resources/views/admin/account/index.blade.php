@extends('admin.layouts.full')
@section('section', translate(':role', ['role' => $admin->role_label]))
@section('title', translate('Account Settings'))
@section('container', 'container-max-lg')
@section('content')
<div class="row g-4">
    <div class="col-lg-12">
        {{-- Nav Tabs --}}
        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="ezydev-tabs-container">
                    <div class="ezydev-tabs-wrapper">
                        <ul class="nav nav-tabs ezydev-tabs" id="accountNav" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link account-nav-link {{ request()->routeIs('admin.account.index') ? 'active' : '' }}"
                                    href="{{ route('admin.account.index') }}" data-bs-toggle="tab"
                                    data-bs-target="#account-details" data-url="{{ route('admin.account.index') }}"
                                    data-tab="account" role="tab">
                                    <i class="bi bi-person-check me-2"></i>{{ translate('Account') }}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link account-nav-link {{ request()->routeIs('admin.account.password') ? 'active' : '' }}"
                                    href="{{ route('admin.account.password') }}" data-bs-toggle="tab"
                                    data-bs-target="#change-password" data-url="{{ route('admin.account.password') }}"
                                    data-tab="password" role="tab">
                                    <i class="bi bi-shield-shaded me-2"></i>{{ translate('Password') }}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link account-nav-link {{ request()->routeIs('admin.account.security') ? 'active' : '' }}"
                                    href="{{ route('admin.account.security') }}" data-bs-toggle="tab"
                                    data-bs-target="#security-settings" data-url="{{ route('admin.account.security') }}"
                                    data-tab="security" role="tab">
                                    <i class="bi bi-person-lock me-2"></i>{{ translate('Security') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content card-tab-content" id="accountTabContent">
            <div class="tab-pane fade {{ request()->routeIs('admin.account.index') ? 'show active' : '' }}"
                id="account-details" role="tabpanel">
                @if(request()->routeIs('admin.account.index'))
                @include('admin.account.partials.details')
                @else
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                @endif
            </div>
            <div class="tab-pane fade {{ request()->routeIs('admin.account.password') ? 'show active' : '' }}"
                id="change-password" role="tabpanel">
                @if(request()->routeIs('admin.account.password'))
                @include('admin.account.partials.password')
                @else
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                @endif
            </div>
            <div class="tab-pane fade {{ request()->routeIs('admin.account.security') ? 'show active' : '' }}"
                id="security-settings" role="tabpanel">
                @if(request()->routeIs('admin.account.security'))
                @include('admin.account.partials.security')
                @else
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2FA Modals --}}
@include('admin.account.includes.2fa-modal')

@push('scripts')
<script>
    'use strict';

    $(document).ready(function () {
        // Track which tabs are already loaded based on current route
        const loadedTabs = {};

        // Mark the current tab as loaded
        const currentRoute = '{{ request()->route()->getName() }}';
        if (currentRoute === 'admin.account.index') {
            loadedTabs['account-details'] = true;
        } else if (currentRoute === 'admin.account.password') {
            loadedTabs['change-password'] = true;
        } else if (currentRoute === 'admin.account.security') {
            loadedTabs['security-settings'] = true;
        }

        // AJAX tab loading
        $('.account-nav-link').on('click', function (e) {
            e.preventDefault();

            const $link = $(this);
            const targetTab = $link.data('bs-target');
            const tabId = targetTab.replace('#', '');
            const url = $link.data('url');

            // If tab already loaded, just switch
            if (loadedTabs[tabId]) {
                const tab = new bootstrap.Tab($link[0]);
                tab.show();

                // Update URL without page reload
                if (url) {
                    window.history.pushState({}, '', url);
                }
                return;
            }

            // Show the tab first (with loading spinner)
            const tab = new bootstrap.Tab($link[0]);
            tab.show();

            // Load content via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    $(targetTab).html(response);
                    loadedTabs[tabId] = true;

                    // Reinitialize plugins if needed
                    if (typeof window.initializePlugins === 'function') {
                        window.initializePlugins();
                    }

                    // Initialize password toggles
                    if (tabId === 'change-password') {
                        $('.password-toggle').off('click').on('click', function () {
                            const $icon = $(this);
                            const $input = $icon.siblings('input');

                            if ($input.attr('type') === 'password') {
                                $input.attr('type', 'text');
                                $icon.removeClass('bi-eye-slash').addClass('bi-eye');
                            } else {
                                $input.attr('type', 'password');
                                $icon.removeClass('bi-eye').addClass('bi-eye-slash');
                            }
                        });
                    }

                    // Initialize clipboard for security tab
                    if (tabId === 'security-settings' && typeof ClipboardJS !== 'undefined') {
                        new ClipboardJS('.btn-copy');
                    }

                    // Update URL
                    if (url) {
                        window.history.pushState({}, '', url);
                    }
                },
                error: function (xhr) {
                    $(targetTab).html(
                        '<div class="card h-100">' +
                        '<div class="card-body p-4 text-center">' +
                        '<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>' +
                        '<p class="mt-3">Failed to load content. Please try again.</p>' +
                        '<button class="btn btn-primary" onclick="location.reload()">Reload Page</button>' +
                        '</div>' +
                        '</div>'
                    );
                }
            });
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function () {
            location.reload();
        });

        // Initialize tab manager for persistence
        window.initTabManager({
            storageKey: 'accountSettingsActiveTab_{{ $admin->id }}',
        });
    });
</script>
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush

@endsection
