<div class="auth-form-container">
    <div class="auth-header d-flex flex-column align-items-center mb-5 text-center">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="image-fluid image-lg mb-3"
                title="{{ getSiteName() }}">
                <img src="{{ getSiteFavicon() }}" alt="{{ getSiteName() }}">
            </a>
        @endif
        <h2 class="fw-bold text-dark mb-1">{{ translate('Reset Password') }}</h2>
        <p class="text-gray-700 fs-14 mb-0">{{ translate('We will send a verification code to your email to reset your password') }}</p>
    </div>

    <!-- Forgot Password Form -->
    <form id="{{ (isset($isAjax) && $isAjax) ? 'forgotPasswordFormModal' : 'forgotPasswordFormPage' }}"
          class="ajax-form"
          action="{{ route('password.email') }}"
          method="POST">
        @csrf

        <div class="auth-input-group position-relative">
            <label for="email">{{ translate('Email') }}</label>
            <i class="bi bi-envelope input-icon"></i>
            <input type="email"
                    class="form-control"
                    name="email"
                    id="reset_email"
                    value="{{ old('email') }}"
                    required>
        </div>

        <div class="mt-4">
            <x-captcha />
        </div>

        <div class="mt-4">
            <div class="d-grid mt-2">
                <button type="submit" class="btn btn-primary btn-auth-primary">
                    {{ translate('Send Request') }}
                </button>
            </div>
        </div>
    </form>

    <!-- Navigation Link -->
    <div class="d-flex flex-wrap align-items-center gap-2 border-top pt-3 mt-4 {{ (isset($isAjax) && $isAjax) ? 'justify-content-center' : 'justify-content-between' }}">
        @if(isset($isAjax) && !$isAjax)
            <a href="{{ route('home') }}" class="text-muted small fw-medium">
                <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Home') }}
            </a>
        @endif

        <p class="text-muted small fw-medium mb-0">
            {{ translate('Got the password?') }}
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
