@extends('themes.main.auth.layout')
@section('title', translate('Set New Password'))

@section('content')
<div class="card border-0 rounded-4 shadow-lg">
    <div class="card-body p-4">
        <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3" title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
            <h2 class="fw-bold text-dark mb-1">{{ translate('Set New Password') }}</h2>
            <p class="text-gray-700 fs-14 mb-0">
                {{ translate('Choose a strong password for your account') }}
            </p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="ajax-form">
            @csrf

            <!-- New Password -->
            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="registerPasswordInput">{{ translate('New Password') }}</label>
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           class="form-control"
                           name="password"
                           id="registerPasswordInput"
                           minlength="8"
                           required
                           autofocus>
                    <i class="bi bi-eye password-toggle"></i>
                </div>
                <!-- Password Strength -->
                <div id="passwordStrengthIndicator" class="mt-2 mx-1" style="display: none;">
                    <div class="progress rounded-pill" style="height: 4px;">
                        <div id="passwordStrengthBar"
                             class="progress-bar"
                             role="progressbar"
                             style="width: 0%"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small id="passwordStrengthText" class="form-text"></small>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="registerConfirmPasswordInput">{{ translate('Confirm Password') }}</label>
                    <i class="bi bi-person-lock input-icon"></i>
                    <input type="password"
                           class="form-control"
                           name="password_confirmation"
                           id="registerConfirmPasswordInput"
                           minlength="8"
                           required>
                    <i class="bi bi-eye password-toggle"></i>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-auth-primary fw-bold py-2">
                    {{ translate('Reset Password') }}
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
