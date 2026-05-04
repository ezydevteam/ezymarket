<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom-dashed">
                    <div class="icon-circle icon-circle-md bg-warning-subtle text-warning me-2">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"> {{ translate('Reset Password') }}</h5>
                        <p class="text-muted small mb-0">{{ translate('Manually reset the staff member\'s account
                            password') }}</p>
                    </div>
                </div>

                <form action="{{ route('admin.roles.staff.password.update', $staff->id) }}" class="ajax-form" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('New
                                Password') }}</label>
                            <div class="input-group position-relative border">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="new-password"
                                    class="form-control border-0 password-input pe-5"
                                    placeholder="{{ translate('Enter new password') }}" required>
                                <i class="bi bi-eye password-toggle"></i>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Confirm
                                Password') }}</label>
                            <div class="input-group position-relative border">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-key-fill"></i></span>
                                <input type="password" name="new-password_confirmation"
                                    class="form-control border-0 password-input pe-5"
                                    placeholder="{{ translate('Confirm new password') }}" required>
                                <i class="bi bi-eye password-toggle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning fw-bold px-4">
                            <i class="bi bi-shield-check me-2"></i>{{ translate('Update Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom-dashed">
                    <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                        <i class="bi bi-phone fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"> {{ translate('Two-Factor Authentication') }}</h5>
                        <p class="text-muted small mb-0">{{ translate('Manage account level security with Google
                            Authenticator') }}</p>
                    </div>
                </div>

                <form action="{{ route('admin.roles.staff.2fa.update', $staff->id) }}" class="ajax-form" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="d-flex gap-3">
                                <div class="icon-circle icon-circle-sm bg-light text-muted">
                                    <i class="bi bi-info-circle"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-bold">{{ translate('Manual 2FA Control') }}</p>
                                    <p class="text-muted small mb-0">
                                        {{ translate('2FA is usually activated by the staff member. As an admin, you
                                        can only disable it if the staff member is locked out.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="ezydev-switch-wrapper-xl d-inline-block">
                                <input type="hidden" name="google2fa_status" value="0">
                                <input id="google2faSwitch" class="ezydev-switch-input" type="checkbox"
                                    name="google2fa_status" value="1" {{ $staff->has2fa() ? 'checked' : '' }}>
                                <label class="ezydev-switch-label" for="google2faSwitch">
                                    <span class="ezydev-switch-slider">
                                        <span class="ezydev-switch-button">
                                            <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                            <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-shield-lock me-2"></i>{{ translate('Save 2FA Status') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
