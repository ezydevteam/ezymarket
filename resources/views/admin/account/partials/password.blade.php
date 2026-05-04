<div class="card h-100">
    <div class="card-header"><i class="bi bi-shield-shaded me-2"></i>{{ translate('Change Password') }}</div>
    <div class="card-body p-4">
        <form action="{{ route('admin.account.password.update') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Current Password') }} <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" name="current-password" class="form-control form-control-md pe-5"
                            placeholder="{{ translate('Enter current password') }}" required>
                        <i class="bi bi-eye-slash password-toggle"></i>
                    </div>
                    <div class="form-text">{{ translate('Enter your current password to verify your identity.') }}</div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('New Password') }} <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" name="new-password" class="form-control form-control-md pe-5"
                            placeholder="{{ translate('Enter new password') }}" minlength="8" required>
                        <i class="bi bi-eye-slash password-toggle"></i>
                    </div>
                    <div class="form-text">{{ translate('Password must be at least 8 characters long.') }}</div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Confirm New Password') }} <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" name="new-password_confirmation" class="form-control form-control-md pe-5"
                            placeholder="{{ translate('Confirm new password') }}" minlength="8" required>
                        <i class="bi bi-eye-slash password-toggle"></i>
                    </div>
                    <div class="form-text">{{ translate('Password must match.') }}</div>
                </div>
            </div>
            <button class="btn btn-primary btn-md mt-4">{{ translate('Save Changes') }}</button>
        </form>
    </div>
</div>
