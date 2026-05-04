@extends('themes.main.auth.layout')
@section('title', $title ?? translate('Verify OTP'))

@section('content')
<div class="card border-0 rounded-4 shadow-lg">
    <div class="card-body p-4">
        <div id="otpSection">
            <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
                <a href="{{ route('home') }}" class="image-fluid image-lg mb-3" title="{{ getSiteName() }}">
                    <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
                </a>
                <h2 class="fw-bold text-dark mb-3">{{ $title ?? translate('Verification Code') }}</h2>
                <div class="text-gray-700 fs-14">
                    {{ translate('We have just sent a 6-digit verification code to') }}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark fs-14">
                        {{ maskEmail($email) }}
                    </span>
                    <button type="button" id="showChangeEmail" class="btn btn-link text-decoration-none text-muted fs-13">
                        {{ translate('Change email?') }}
                    </button>
                </div>
            </div>

            <form id="otpVerifyForm" class="ajax-form" action="{{ $verifyRoute }}" method="POST">
                @csrf

                <!-- OTP Input Group -->
                <div class="otp-input-group d-flex justify-content-between gap-2 mb-2">
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus autocomplete="one-time-code">
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-field form-control" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>

                <div id="otpError" class="text-danger small fw-medium text-center mb-4" style="display: none;"></div>

                @error('otp')
                    <div class="text-danger small fw-medium text-center mb-4">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Hidden input to store combined OTP -->
                <input type="hidden" name="otp" id="combinedOtp">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-auth-primary fw-bold py-2" id="verifyBtn">
                        {{ translate('Verify OTP') }}
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-muted small mb-2">{{ translate("Didn't receive the code?") }}</p>
                <div id="resendCountdown" class="text-muted small d-none">
                    {{ translate('Resend in') }} <span id="timer" class="fw-bold">60</span>s
                </div>
                <button type="button" id="resendBtn" class="btn btn-transparent text-primary fw-semibold small" disabled>
                    {{ translate('Resend Code') }}
                </button>
            </div>
        </div>

        <!-- Change Email Section (Hidden by default) -->
        <div id="changeEmailSection" class="d-none">
            <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
                <h2 class="fw-bold text-dark mb-1">{{ translate('Change Email') }}</h2>
                <p class="text-gray-700 fs-14 mb-0">
                    {{ translate('Enter your correct email address below') }}
                </p>
            </div>

            <form id="changeEmailForm" class="ajax-form" action="{{ route('change.email') }}" method="POST">
                @csrf
                <div class="auth-input-group position-relative mb-4">
                    <label for="new_email">{{ translate('New Email Address') }}</label>
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" id="new_email" class="form-control" required>
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-primary btn-auth-primary fw-bold py-2">
                        {{ translate('Send New Code') }}
                    </button>
                    <button type="button" id="hideChangeEmail" class="btn btn-transparent text-muted small hover-primary">
                        {{ translate('Cancel and stay with current email') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4 pt-3 border-top text-center">
            <a href="{{ route('login') }}" class="text-decoration-none text-muted small fw-medium hover-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ translate('Back to Sign In') }}
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fields = document.querySelectorAll('.otp-field');
    const combinedInput = document.getElementById('combinedOtp');
    const form = document.getElementById('otpVerifyForm');
    const resendBtn = document.getElementById('resendBtn');
    const resendCountdown = document.getElementById('resendCountdown');
    const timerSpan = document.getElementById('timer');
    const otpError = document.getElementById('otpError');

    const otpSection = document.getElementById('otpSection');
    const changeEmailSection = document.getElementById('changeEmailSection');
    const showChangeEmailBtn = document.getElementById('showChangeEmail');
    const hideChangeEmailBtn = document.getElementById('hideChangeEmail');

    // Toggle Logic
    showChangeEmailBtn.addEventListener('click', () => {
        otpSection.classList.add('d-none');
        changeEmailSection.classList.remove('d-none');
    });

    hideChangeEmailBtn.addEventListener('click', () => {
        changeEmailSection.classList.add('d-none');
        otpSection.classList.remove('d-none');
    });

    // Focus first field
    fields[0].focus();

    fields.forEach((field, index) => {
        // Handle input
        field.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < fields.length - 1) {
                fields[index + 1].focus();
            }
            updateCombined();
        });

        // Handle backspace
        field.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                fields[index - 1].focus();
            }
        });

        // Handle paste
        field.addEventListener('paste', (e) => {
            e.preventDefault();
            const data = e.clipboardData.getData('text').slice(0, 6);
            if (/^\d+$/.test(data)) {
                data.split('').forEach((char, i) => {
                    if (fields[i]) fields[i].value = char;
                });
                updateCombined();
                if (data.length === 6) $(form).submit();
            }
        });
    });

    function updateCombined() {
        let otp = '';
        fields.forEach(f => otp += f.value);
        combinedInput.value = otp;
        if (otp.length === 6) {
            $(form).submit();
        }
    }

    // Handle AJAX Errors (Custom logic for OTP fields)
    $(form).on('ajax-form:error', function(e, xhr) {
        fields.forEach(f => {
            f.value = '';
            f.classList.add('border-danger');
        });
        fields[0].focus();

        const res = xhr.responseJSON;
        if (res && res.message) {
            otpError.textContent = res.message;
            $(otpError).fadeIn();
        }

        // Remove error highlight on re-entry
        fields.forEach(f => {
            f.addEventListener('input', () => {
                fields.forEach(el => el.classList.remove('border-danger'));
                $(otpError).fadeOut();
            }, { once: true });
        });
    });

    // Resend Logic
    let timeLeft = 60;
    let timerId = null;

    function startTimer() {
        resendBtn.disabled = true;
        resendCountdown.classList.remove('d-none');
        timeLeft = 60;
        timerSpan.textContent = timeLeft;

        if (timerId) clearInterval(timerId);
        timerId = setInterval(() => {
            timeLeft--;
            timerSpan.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timerId);
                resendBtn.disabled = false;
                resendCountdown.classList.add('d-none');
            }
        }, 1000);
    }

    // Start initial timer on load
    startTimer();

    resendBtn.addEventListener('click', function() {
        window.EzyDev.ajaxRequest({
            url: '{{ $resendRoute }}',
            method: 'POST',
            trigger: $(this),
            onSuccess: function(res) {
                startTimer();
            }
        });
    });
});
</script>
@endpush
