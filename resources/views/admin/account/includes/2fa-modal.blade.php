{{-- Enable 2FA Modal --}}
<x-modal id="enable2FAModal"
         :title="translate('Enable 2FA Authentication')"
         icon="bi bi-lock">

    <form id="enable2FAForm" action="{{ route('admin.account.2fa.enable') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ translate('Enter OTP Code') }}</label>
            <input type="text" name="otp_code" id="enable_otp_code" class="form-control form-control-lg input-numeric text-center"
                placeholder="• • • • • •" maxlength="6" minlength="6" required>
            <small class="form-text text-muted">
                {{ translate('Enter the 6-digit code from your authenticator app') }}
            </small>
        </div>
        <div class="d-flex gap-3">
            <button type="button" class="btn btn-md btn-cancel w-50" data-bs-dismiss="modal">
                {{ translate('Cancel') }}
            </button>
            <button type="submit" class="btn btn-success btn-md w-50">
                <i class="bi bi-check me-2"></i>{{ translate('Enable') }}
            </button>
        </div>
    </form>

    @push('styles')
    <style>
        #enable2FAModal .modal-header {
            background-color: var(--bs-success);
            color: white;
        }
        #enable2FAModal .modal-header .btn-close {
            filter: invert(1);
        }
    </style>
    @endpush
</x-modal>

{{-- Disable 2FA Modal --}}
<x-modal id="disable2FAModal"
         :title="translate('Disable 2FA Authentication')"
         icon="bi bi-unlock">

    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ translate('Disabling 2FA will reduce your account security') }}
    </div>

    <form id="disable2FAForm" action="{{ route('admin.account.2fa.disable') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ translate('Enter OTP Code') }}</label>
            <input type="text" name="otp_code" id="disable_otp_code" class="form-control form-control-lg input-numeric text-center"
                placeholder="• • • • • •" maxlength="6" minlength="6" required>
            <small class="form-text text-muted">
                {{ translate('Enter the 6-digit code from your authenticator app to confirm') }}
            </small>
        </div>
        <div class="d-flex gap-3">
            <button type="button" class="btn btn-md btn-cancel w-50" data-bs-dismiss="modal">
                {{ translate('Cancel') }}
            </button>
            <button type="submit" class="btn btn-danger btn-md w-50">
                <i class="bi bi-x me-2"></i>{{ translate('Disable') }}
            </button>
        </div>
    </form>

    @push('styles')
    <style>
        #disable2FAModal .modal-header {
            background-color: var(--bs-danger);
            color: white;
        }
        #disable2FAModal .modal-header .btn-close {
            filter: invert(1);
        }
    </style>
    @endpush
</x-modal>
