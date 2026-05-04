<!-- Registration Success Modal (Global) - Always present but hidden -->
<div class="modal fade" id="registrationSuccessModal" tabindex="-1" aria-labelledby="registrationSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success display-1"></i>
                </div>
                <h3 class="fw-bold mb-3">{{ translate('Account Created Successfully!') }}</h3>
                <p class="text-gray-700 mb-4">
                    {{ translate('Welcome to '.getSiteName().'! To get the best experience, please take a moment to complete your profile details.') }}
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('user.settings.account') }}" class="btn btn-primary btn-md fw-semibold rounded-3">
                        {{ translate('Complete My Profile') }}
                    </a>
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">
                        {{ translate('I\'ll do it later') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('footer_content')
    <script>
    (function() {
        @if (session()->has('registration_complete'))
            const initRegistrationSuccessModal = () => {
                const modalEl = document.getElementById('registrationSuccessModal');
                if (modalEl) {
                    const successModal = new bootstrap.Modal(modalEl);
                    successModal.show();
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initRegistrationSuccessModal);
            } else {
                initRegistrationSuccessModal();
            }
        @endif
    })();
    </script>
    @if (session()->has('registration_complete'))
        @php session()->forget('registration_complete'); @endphp
    @endif
@endpush
