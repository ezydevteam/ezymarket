@extends('themes.main.auth.layout')
@section('title', translate('2FA Verification'))

@section('content')
<div class="card border-0 rounded-4 shadow-lg">
    <div class="card-body p-4">

        <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3" title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
            <h2 class="fw-bold text-dark mb-1">{{ translate('2FA Verification') }}</h2>
            <p class="text-gray-700 fs-14 mb-0">
                {{ translate('Please enter the OTP from your authenticator app to continue') }}
            </p>
        </div>

        <form action="{{ route('2fa.verify') }}" method="POST" class="ajax-form">
            @csrf

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="otp_code">{{ translate('Authenticator Code') }}</label>
                    <i class="bi bi-shield-lock input-icon"></i>
                    <input type="text"
                           name="otp_code"
                           id="otp_code"
                           class="form-control text-center"
                           maxlength="6"
                           required
                           autofocus
                           autocomplete="one-time-code">
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-auth-primary fw-bold py-2">
                    {{ translate('Verify & Continue') }}
                </button>
            </div>
        </form>

        <div class="mt-4 pt-3 border-top text-center">
            <a href="{{ route('login') }}" class="text-decoration-none text-muted small fw-medium hover-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ translate('Back to Sign In') }}
            </a>
        </div>

    </div>
</div>
@endsection
