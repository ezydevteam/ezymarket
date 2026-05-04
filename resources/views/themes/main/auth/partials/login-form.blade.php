<div class="auth-form-container">
    <div class="auth-header d-flex flex-column align-items-center mb-4">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3"
                title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
        @endif
        <h2 class="fw-bold text-dark mb-1">{{ translate('Welcome back!') }}</h2>
        <p class="text-gray-700 fs-14 mb-0">{{ translate('Please enter your details to sign in.') }}</p>
    </div>

    <form id="{{ (isset($isAjax) && $isAjax) ? 'loginFormModal' : 'loginFormPage' }}"
          class="ajax-form"
          action="{{ route('login') }}"
          method="POST">
        @csrf

        <div class="auth-input-group position-relative mb-4">
            <label for="email_or_username">{{ translate('Email or username') }}</label>
            <i class="bi bi-envelope input-icon"></i>
            <input type="text"
                   class="form-control"
                   name="email_or_username"
                   id="email_or_username"
                   value="{{ old('email_or_username') }}"
                   required>
        </div>

        <div class="auth-input-group position-relative mb-4">
            <label for="password">{{ translate('Password') }}</label>
            <i class="bi bi-lock input-icon"></i>
            <input type="password"
                   class="form-control"
                   name="password"
                   id="password"
                   required>
            <i class="bi bi-eye password-toggle"></i>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                <label class="form-check-label text-gray-700 small" for="rememberMe">
                    {{ translate('Remember Me') }}
                </label>
            </div>
            <a href="{{ route('password.request') }}"
               class="text-primary small fw-medium"
               @if(isset($isAjax) && $isAjax)
               data-bs-toggle="modal"
               data-bs-target="#forgotPasswordModal"
               data-action="{{ route('password.request') }}"
               @endif>
                {{ translate('Forgot password?') }}
            </a>
        </div>

        <div class="mb-4">
            <x-captcha />
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-primary btn-auth-primary">
                {{ translate('Sign In') }}
            </button>
        </div>
    </form>

    <div class="mb-4">
        <x-social-auth-buttons />
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 border-top pt-3 {{ (isset($isAjax) && $isAjax) ? 'justify-content-center' : 'justify-content-between' }}">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="text-muted small fw-medium">
                <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Home') }}
            </a>
        @endif

        <p class="text-muted small fw-medium mb-0">
            {{ translate("Don't have an account?") }}
            <a href="{{ route('register') }}"
                class="text-primary fw-bold ms-1"
                @if(isset($isAjax) && $isAjax)
                data-bs-toggle="modal"
                data-bs-target="#registerModal"
                data-action="{{ route('register') }}"
                @endif>
                {{ translate('Sign Up') }}
            </a>
        </p>
    </div>
</div>
