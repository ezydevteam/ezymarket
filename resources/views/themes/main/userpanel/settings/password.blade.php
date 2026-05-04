@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Password'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card-v px-4 py-3 shadow-sm rounded-4">
                <div class="card-v-header border-0 p-0 mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom-dashed pb-2">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </span>
                                {{ translate('Reset Password') }}
                            </h5>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-3" form="passwordUpdateForm">
                                <i class="bi bi-save me-1"></i>
                                {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Form Column --}}
                    <div class="col-lg-7">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-gray-700 small text-uppercase">
                                {{ translate('Current Password') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 py-2">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-end-0 ps-2"
                                       form="passwordUpdateForm"
                                       @if(session('password_reset_verified')) disabled value="********" @endif
                                       name="current-password" placeholder="{{ translate('Enter current password') }}" @if(!session('password_reset_verified')) required @endif>
                                @if(!session('password_reset_verified'))
                                    <span class="input-group-text bg-transparent border-start-0 cursor-pointer py-2">
                                        <i class="bi bi-eye text-muted password-toggle"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="form-text small text-gray-600 mt-2 d-flex justify-content-between align-items-center">
                                @if(session('password_reset_verified'))
                                    <span class="text-success fw-medium">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        {{ translate('Identity verified via OTP.') }}
                                    </span>
                                @else
                                    <span>{{ translate('We need your current password to confirm your identity.') }}</span>
                                    <button type="button" class="btn btn-link text-decoration-none text-primary fw-medium fs-12 action-confirm"
                                       data-action="{{ route('user.settings.password.reset_otp') }}"
                                       data-method="POST"
                                       data-confirm="{{ translate('A verification code will be sent to your email address. Do you want to proceed?') }}">
                                        {{ translate('Forgot your password?') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('user.settings.password') }}" method="POST" class="ajax-form" id="passwordUpdateForm">
                            @csrf
                            <hr class="border-dashed my-4">

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-gray-700 small text-uppercase mb-2">{{ translate('New Password') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 py-2">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-end-0 ps-2 password-strength-input"
                                           name="new-password" id="new-password" placeholder="{{ translate('Enter new password') }}" required>
                                    <span class="input-group-text bg-transparent border-start-0 cursor-pointer py-2">
                                        <i class="bi bi-eye text-muted password-toggle"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-gray-700 small text-uppercase">{{ translate('Confirm New Password') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 py-2">
                                        <i class="bi bi-patch-check text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-end-0 ps-2"
                                           name="new-password_confirmation" id="password-confirmation" placeholder="{{ translate('Repeat new password') }}" required>
                                    <span class="input-group-text bg-transparent border-start-0 cursor-pointer py-2">
                                        <i class="bi bi-eye text-muted password-toggle"></i>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Hints Column --}}
                    <div class="col-lg-5">
                        <div class="p-4 border rounded-4 bg-light bg-opacity-50 h-100">
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <span class="icon-circle icon-circle-sm bg-white text-primary me-2 shadow-sm">
                                    <i class="bi bi-shield-check text-primary"></i>
                                </span>
                                {{ translate('Password Requirements') }}
                            </h6>
                            <p class="text-gray-600 small">
                                {{ translate('To ensure your account stay secure, your password must meet these conditions:') }}
                            </p>

                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="length">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('At least 8 characters long') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="uppercase">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('At least one uppercase letter (A-Z)') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="lowercase">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('At least one lowercase letter (a-z)') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="number">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('At least one number (0-9)') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="special">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('At least one special character (!@#$%)') }}</span>
                                </li>
                                <li class="d-flex align-items-center gap-3 pw-req" data-req="match">
                                    <div class="req-indicator">
                                        <i class="bi bi-dash-circle text-muted req-icon"></i>
                                    </div>
                                    <span class="small fw-medium">{{ translate('Passwords must match') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
