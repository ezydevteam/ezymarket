@extends('themes.main.auth.layout')
@section('title', translate('Complete your information'))

@section('content')
<div class="card border-0 rounded-4 shadow-lg">
    <div class="card-body p-4 p-md-5">

        <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3" title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
            <h2 class="fw-bold text-dark mb-1">{{ translate('Complete your information') }}</h2>
            <p class="text-gray-700 fs-14 mb-0">
                {{ translate('You need to complete some basic information required to log in next time') }}
            </p>
        </div>

        <form action="{{ route('oauth.data.complete') }}" method="POST" class="ajax-form">
            @csrf

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="auth-input-group position-relative">
                        <label for="firstname">{{ translate('First Name') }}</label>
                        <i class="bi bi-person input-icon"></i>
                        <input type="text"
                               name="firstname"
                               id="firstname"
                               class="form-control"
                               value="{{ authUser()->firstname }}"
                               required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="auth-input-group position-relative">
                        <label for="lastname">{{ translate('Last Name') }}</label>
                        <i class="bi bi-person input-icon"></i>
                        <input type="text"
                               name="lastname"
                               id="lastname"
                               class="form-control"
                               value="{{ authUser()->lastname }}"
                               required>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="registerUsernameInput">{{ translate('Username') }}</label>
                    <i class="bi bi-person-badge input-icon"></i>
                    <input type="text"
                           name="username"
                           id="registerUsernameInput"
                           class="form-control"
                           value="{{ authUser()->username }}"
                           required>
                    <div id="username-check" class="input-activity-indicator" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                </div>
                <div id="username-availability-message" class="mx-1 mt-2" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="email">{{ translate('Email address') }}</label>
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control"
                           value="{{ authUser()->email }}"
                           required>
                </div>
            </div>

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="registerPasswordInput">{{ translate('Password') }}</label>
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           name="password"
                           id="registerPasswordInput"
                           class="form-control"
                           minlength="8"
                           required>
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

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <label for="password_confirmation">{{ translate('Confirm Password') }}</label>
                    <i class="bi bi-person-lock input-icon"></i>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control"
                           minlength="8"
                           required>
                    <i class="bi bi-eye password-toggle"></i>
                </div>
            </div>

            @if (@$settings->links->terms_of_use_link)
                <div class="mb-4 px-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms"
                            {{ old('terms') ? 'checked' : '' }} required>
                        <label class="form-check-label text-gray-700 fs-14" for="terms">
                            {{ translate('I agree to the') }}
                            <a href="{{ @$settings->links->terms_of_use_link }}" class="text-primary hover-primary fw-medium">
                                {{ translate('Terms of service') }}
                            </a>
                        </label>
                    </div>
                </div>
            @endif

            <x-captcha />

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-auth-primary fw-bold py-2">
                    {{ translate('Complete Registration') }}
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
