@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - 2FA'))
@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            {{-- Header Card --}}
            <div class="card-v px-4 py-3 shadow-sm rounded-4 mb-4">
                <div class="card-v-header border-0 p-0 mb-n1">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                                {{ translate('Two-Factor Authentication') }}
                            </h5>
                        </div>
                        <div>
                            <span class="badge {{ $user->google2fa_status ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill shadow-sm">
                                <i class="bi {{ $user->google2fa_status ? 'bi-lock-fill' : 'bi-unlock-fill' }} me-1"></i>
                                {{ $user->google2fa_status ? translate('Enabled') : translate('Disabled') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Main Column --}}
                <div class="col-lg-7">
                    <div class="card-v px-4 py-4 shadow-sm rounded-4 h-100">
                        <div class="mb-4 text-center text-lg-start">
                            <h6 class="fw-bold mb-2 text-gray-800">{{ translate('Secure Your Account') }}</h6>
                            <p class="text-gray-600 fs-14 mb-0">
                                {{ translate('Two-factor authentication (2FA) adds an extra layer of security to your account by requiring two forms of identification to log in. This protects you from unauthorized access even if your password is stolen.') }}
                            </p>
                        </div>

                        @if (!$user->google2fa_status)
                            {{-- Setup Area --}}
                            <div class="p-4 rounded-4 bg-light bg-opacity-50 border border-dashed text-center">
                                <div class="bg-white p-3 d-inline-block rounded-4 shadow-sm mb-4 border border-white">
                                    <div class="qr-wrapper">
                                        {!! $QR_Image !!}
                                    </div>
                                </div>

                                <div class="mb-4 mx-auto max-w-400">
                                    <label class="form-label fw-semibold small text-uppercase text-gray-700 mb-2">{{ translate('Manual Setup Key') }}</label>
                                    <div class="input-group dashboard-input-group shadow-none border rounded-3 p-1 bg-white">
                                        <input id="input-link" type="text" class="form-control border-0 bg-transparent fs-14 fw-mono"
                                            value="{{ $user->google2fa_secret }}" readonly>
                                        <button class="btn btn-primary btn-md rounded-2 btn-copy px-3" data-clipboard-target="#input-link">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                    <div class="form-text fs-13 text-gray-600 mt-2">
                                        {{ translate('Scan the QR code or enter this key manually in your authenticator app.') }}
                                    </div>
                                </div>

                                <button class="btn btn-primary btn-lg px-5 py-3 rounded-3 shadow-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#2faEnableModal">
                                    <i class="bi bi-shield-plus me-2"></i>
                                    {{ translate('Activate 2FA Now') }}
                                </button>
                            </div>
                        @else
                            {{-- Enabled Area --}}
                            <div class="p-5 rounded-4 bg-success-subtle border border-success-subtle text-center d-flex flex-column justify-content-center">
                                <div class="icon-circle icon-circle-xl bg-white text-success mx-auto mb-4 shadow-sm border border-success border-opacity-10 border-2">
                                    <i class="bi bi-shield-fill-check display-4"></i>
                                </div>
                                <h4 class="fw-bold text-success mb-2">{{ translate('Security Enhanced') }}</h4>
                                <p class="text-gray-700 mb-4 px-lg-4">
                                    {{ translate('Your account is currently protected by Two-Factor Authentication (2FA). We recommend keeping this feature enabled for maximum security.') }}
                                </p>
                                <div class="col-lg-8 mx-auto">
                                    <button class="btn btn-outline-danger btn-md w-100 rounded-3 border-dashed" data-bs-toggle="modal"
                                        data-bs-target="#2faDisableModal">
                                        <i class="bi bi-shield-x me-1"></i>
                                        {{ translate('Disable 2FA Protection') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar Column --}}
                <div class="col-lg-5">
                    <div class="card-v px-4 py-4 shadow-sm rounded-4 bg-light-subtle h-100">
                        <h6 class="fw-bold mb-4 d-flex align-items-center">
                            <span class="icon-circle icon-circle-sm bg-white text-primary me-2 shadow-sm">
                                <i class="bi bi-phone-vibrate text-primary"></i>
                            </span>
                            {{ translate('Authenticator Apps') }}
                        </h6>

                        <p class="text-gray-700 fs-13 mb-4">
                            {{ translate('To use 2FA, you need an authenticator app installed on your smartphone. We recommend the following:') }}
                        </p>

                        <div class="vstack gap-3">
                            <a target="_blank" href="https://apps.apple.com/us/app/google-authenticator/id388497605"
                                rel="noopener noreferrer"
                                class="d-flex align-items-center p-3 rounded-4 bg-white border text-decoration-none transition-all hover-shadow">
                                <div class="icon-circle icon-circle-sm bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-google"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold fs-14 text-gray-800">{{ translate('Google Authenticator') }}</div>
                                    <div class="xsmall text-muted">{{ translate('Recommended for iOS') }}</div>
                                </div>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>

                            <a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                                rel="noopener noreferrer"
                                class="d-flex align-items-center p-3 rounded-4 bg-white border text-decoration-none transition-all hover-shadow">
                                <div class="icon-circle icon-circle-sm bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-android2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold fs-14 text-gray-800">{{ translate('Google Authenticator') }}</div>
                                    <div class="xsmall text-muted">{{ translate('Recommended for Android') }}</div>
                                </div>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>

                            <a target="_blank" href="https://apps.apple.com/us/app/microsoft-authenticator/id983156458"
                                rel="noopener noreferrer"
                                class="d-flex align-items-center p-3 rounded-4 bg-white border text-decoration-none transition-all hover-shadow">
                                <div class="icon-circle icon-circle-sm bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-microsoft"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold fs-14 text-gray-800">{{ translate('Microsoft Authenticator') }}</div>
                                    <div class="xsmall text-muted">{{ translate('Enterprise Security') }}</div>
                                </div>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>
                        </div>

                        <div class="mt-4 p-3 bg-primary-subtle text-primary rounded-4">
                            <div class="d-flex align-items-start small text-gray-600">
                                <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
                                <div>
                                    <span class="fw-bold text-primary">{{ translate('Lost access?') }}</span>
                                    <br>
                                    {{ translate('Keep your backup codes safe. If you lose your phone, you\'ll need them to access your account.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2FA Security Modal --}}
            @php
                $isEnabling = !$user->google2fa_status;
                $modalId = $isEnabling ? '2faEnableModal' : '2faDisableModal';
                $modalTitle = $isEnabling ? translate('Verify OTP Code') : translate('Disable 2FA Security');
                $modalIcon = $isEnabling ? 'bi-shield-lock' : 'bi-shield-x';
                $modalAction = $isEnabling ? route('user.settings.2fa.enable') : route('user.settings.2fa.disable');
            @endphp

            <x-modal :id="$modalId" :title="$modalTitle" :icon="$modalIcon" :static="true">
                <form action="{{ $modalAction }}" class="ajax-form" method="POST">
                    @csrf
                    <div class="modal-body-content">
                        <div class="text-center mb-4 {{ $isEnabling ? 'text-gray-700' : 'text-danger' }} small">
                            @if ($isEnabling)
                                {{ translate('Enter the 6-digit code currently displayed in your authenticator app to enable protection.') }}
                            @else
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ translate('Disabling 2FA makes your account significantly less secure. Are you sure?') }}
                            @endif
                        </div>
                        <div class="mb-5">
                            <input type="text" name="otp_code" class="form-control form-control-lg text-center fw-bold fs-24 bg-light rounded-3 input-numeric"
                                placeholder="&bull; &bull; &bull; &bull; &bull; &bull;" maxlength="6" autofocus required>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-md flex-fill text-uppercase"
                                data-bs-dismiss="modal">{{ translate('Close') }}</button>
                            <button type="submit"
                                class="btn {{ $isEnabling ? 'btn-primary' : 'btn-danger' }} btn-md flex-fill text-uppercase">
                                {{ $isEnabling ? translate('Enable Now') : translate('Disable Now') }}
                            </button>
                        </div>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
@endsection
