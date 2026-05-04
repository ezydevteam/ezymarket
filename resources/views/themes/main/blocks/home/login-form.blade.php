@php $data = (object)($data ?? []);@endphp

@guest
<div id="{{ $data->uniqueId }}" class="home-login-form {{ $isFullWidth ? $data->containerClass : '' }}">
    <div class="row justify-content-{{ $data->blockAlign ?? 'center' }}">
        <div class="{{ $data->formWidth ?? 'col-12' }}">
            <div class="login-form-container p-4 {{ $data->formShadow ?? 'shadow-sm' }} rounded-4">
                @themeInclude('blocks.home.partials.block-title', ['data' => $data])

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    @if(($data->blockStyle ?? 'default') === 'icons')
                    {{-- Icons Style --}}
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0"
                            placeholder="{{ translate('Email Address') }}" required>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0"
                            placeholder="{{ translate('Password') }}" required>
                    </div>
                    @elseif(($data->blockStyle ?? 'default') === 'rounded')
                    {{-- Rounded Pill Style --}}
                    <div class="mb-3">
                        <label class="form-label ms-3">{{ translate('Email Address') }}</label>
                        <input type="email" name="email" class="form-control rounded-pill px-4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label ms-3">{{ translate('Password') }}</label>
                        <input type="password" name="password" class="form-control rounded-pill px-4" required>
                    </div>
                    @else
                    {{-- Default Style --}}
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Email Address') }}</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Password') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember_{{ $data->uniqueId }}"
                                name="remember">
                            <label class="form-check-label" for="remember_{{ $data->uniqueId }}">{{
                                translate('Remember Me') }}</label>
                        </div>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-reset small">{{
                            translate('Forgot Password?') }}</a>
                        @endif
                    </div>

                    <button type="submit"
                        class="btn {{ $data->btnStyle ?? 'primary' }} w-100 mb-3 {{($data->blockStyle ?? 'default') === 'rounded' ? 'rounded-pill' : ''}}">
                        @if($data->btnIcon)
                        <i class="bi {{ $data->btnIcon }} me-2"></i>
                        @endif
                        {{ translate('Login') }}
                    </button>

                    <div class="text-center">
                        <span class="text-muted">{{ translate('Don\'t have an account?') }}</span>
                        <a href="{{ route('register') }}" class="fw-medium ms-1">{{
                            translate('Register') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endguest
