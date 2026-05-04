<div class="auth-form-container">
    <div class="auth-header d-flex flex-column align-items-center mb-4 text-center">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3"
                title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
        @endif
        <h2 class="fw-bold text-dark mb-1">{{ translate('Create Account') }}</h2>
        <p class="text-gray-700 fs-14 mb-0">{{ translate('Enter your details to join with us!') }}</p>
    </div>

    <!-- Registration Form -->
    <form id="{{ (isset($isAjax) && $isAjax) ? 'registerFormModal' : 'registerFormPage' }}"
          class="ajax-form"
          action="{{ route('register') }}"
          method="POST">
        @csrf

        <div class="row g-4">
            <!-- Username -->
            <div class="col-12">
                <div class="auth-input-group position-relative">
                    <label for="username">{{ translate('Username') }}</label>
                    <i class="bi bi-at input-icon"></i>
                    <input type="text"
                           id="registerUsernameInput"
                           class="form-control"
                           name="username"
                           data-check-url="{{ route('check.username.availability') }}"
                           value="{{ old('username') }}"
                           minlength="6"
                           required>
                </div>
                <div id="usernameAvailabilityMessage" class="mt-1 mx-1">
                    <small id="usernameStatusText" class="form-text" style="display: none;"></small>
                </div>
            </div>

            <!-- Email -->
            <div class="col-12">
                <div class="auth-input-group position-relative">
                    <label for="email">{{ translate('Email') }}</label>
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email"
                           class="form-control"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required>
                </div>
            </div>

            <!-- Password -->
            <div class="col-12">
                <div class="auth-input-group position-relative">
                    <label for="password">{{ translate('Password') }}</label>
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           class="form-control password-input"
                           id="registerPasswordInput"
                           name="password"
                           required>
                    <i class="bi bi-eye password-toggle"></i>
                </div>
                <!-- Password Strength -->
                <div id="passwordStrengthIndicator" class="mt-2 mx-1" style="display: none;">
                    <div class="progress rounded-pill" style="height: 4px;">
                        <div id="passwordStrengthBar"
                             class="progress-bar"
                             role="progressbar"
                             style="width: 0%;"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small id="passwordStrengthText" class="form-text"></small>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column gap-1 bg-light rounded-4 p-3">
                    <div class="form-check form-switch d-flex align-items-start justify-content-between ps-0 mb-2">
                        <label class="form-check-label text-dark fw-medium fs-16" for="is_seller">
                            {{ translate('I want to become a seller') }}
                        </label>
                        <input class="form-check-input"
                            type="checkbox"
                            name="is_seller"
                            id="is_seller"
                            value="1"
                            role="switch"
                            {{ old('is_seller') ? 'checked' : '' }}>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input register-subscribe-input"
                            type="checkbox"
                            name="newsletter"
                            id="newsletter"
                            value="1"
                            {{ !old() || old('newsletter') ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted ms-1" for="newsletter">
                            {{ translate('Subscribe me to get latest updates and offers') }}
                        </label>
                    </div>

                    @if (@$settings->links->terms_of_use_link || @$settings->links->privacy_policy_link)
                    <div class="form-check">
                        <input class="form-check-input"
                                type="checkbox"
                                name="terms"
                                id="terms"
                                {{ old('terms') ? 'checked' : '' }}
                                required>
                        <label class="form-check-label small text-muted ms-1" for="terms">
                            {{ translate('I agree to the ') }}
                            @if(@$settings->links->terms_of_use_link)
                            <a href="{{ @$settings->links->terms_of_use_link }}"
                                target="_blank"
                                class="text-primary fw-medium hover-underline">
                                {{ translate('Terms of Use') }}
                            </a>
                            {{ translate(' and') }}
                            @endif
                            @if(@$settings->links->privacy_policy_link)
                            <a href="{{ @$settings->links->privacy_policy_link }}"
                                target="_blank"
                                class="text-primary fw-medium hover-underline">
                                {{ translate('Privacy Policy') }}
                            </a>
                            @endif
                        </label>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Captcha -->
        <div class="mt-4">
            <x-captcha />
        </div>

        <!-- Submit Button -->
        <div class="mt-4">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-auth-primary">
                    {{ translate('Create Account') }}
                </button>
            </div>
        </div>
    </form>

    <!-- Social Auth -->
    <div class="mt-4">
        <x-social-auth-buttons />
    </div>

    <!-- Sign In Link -->
    <div class="d-flex flex-wrap align-items-center gap-2 mt-4 border-top pt-3 {{ (isset($isAjax) && $isAjax) ? 'justify-content-center' : 'justify-content-between' }}">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="text-muted small fw-medium">
                <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Home') }}
            </a>
        @endif

        <p class="text-muted small fw-medium mb-0">
            {{ translate('Already have an account?') }}
            <a href="{{ route('login') }}"
               class="text-primary fw-bold ms-1"
               @if(isset($isAjax) && $isAjax)
               data-bs-toggle="modal"
               data-bs-target="#loginModal"
               data-action="{{ route('login') }}"
               @endif>
                {{ translate('Sign In') }}
            </a>
        </p>
    </div>
</div>
