@php
    $id = $data['uniqueId'];
    $blockGap = $data['blockGap'] ?? 'gap-3';
    $spaceBetween = $data['spaceBetween'] ?? false;

    // Login Data
    $isLoginModal = $data['loginTrigger'] === 'modal';
    $loginBtnClass = $data['loginBtnClass'];
    $loginWrapperAttrs = $data['loginWrapperAttrs'];
    $showLoginIcon = $data['showLoginIcon'];
    $loginIcon = $data['loginIcon'];
    $loginIconSize = $data['loginIconSize'];
    $loginDisplay = $data['loginDisplay'];
    $showLoginText = $data['showLoginText'];
    $loginText = $data['loginText'];

    // Register Data
    $registrationEnabled = $data['registrationEnabled'];
    $isRegisterModal = $data['registerTrigger'] === 'modal';
    $registerBtnClass = $data['registerBtnClass'];
    $registerWrapperAttrs = $data['registerWrapperAttrs'];
    $showRegisterIcon = $data['showRegisterIcon'];
    $registerIcon = $data['registerIcon'];
    $registerIconSize = $data['registerIconSize'];
    $registerDisplay = $data['registerDisplay'];
    $showRegisterText = $data['showRegisterText'];
    $registerText = $data['registerText'];

    // General
    $showModals = $data['showModals'];
    $authDisplay = $data['authDisplay'];
@endphp

<div id="{{ $id }}" class="header-auth">
    @guest
        <div class="d-flex align-items-center {{ $blockGap }}">
            {{-- Login Action --}}
            <a href="{{ $isLoginModal ? 'javascript:void(0)' : route('login') }}"
               role="button"
               class="{{ $loginBtnClass }}"
               @if($isLoginModal)
                   data-bs-toggle="modal"
                   data-bs-target="#loginModal"
                   data-action="{{ route('login') }}"
               @endif
               {!! $loginWrapperAttrs !!}>
                @if($showLoginIcon)
                    <i class="bi {{ $loginIcon }} {{ $loginIconSize }} {{ ($loginDisplay === 'icon_text') ? 'me-1' : '' }}"></i>
                @endif
                @if($showLoginText)
                    <span class="{{ $loginDisplay === 'icon_text_bottom' ? 'small lh-1' : '' }}">{{ $loginText }}</span>
                @endif
            </a>

            {{-- Register Action --}}
            @if($registrationEnabled)
                <a href="{{ $isRegisterModal ? 'javascript:void(0)' : route('register') }}"
                   role="button"
                   class="{{ $registerBtnClass }}"
                   @if($isRegisterModal)
                       data-bs-toggle="modal"
                       data-bs-target="#registerModal"
                       data-action="{{ route('register') }}"
                   @endif
                   {!! $registerWrapperAttrs !!}>
                    @if($showRegisterIcon)
                        <i class="bi {{ $registerIcon }} {{ $registerIconSize }} {{ ($registerDisplay === 'icon_text') ? 'me-1' : '' }}"></i>
                    @endif
                    @if($showRegisterText)
                        <span class="{{ $registerDisplay === 'icon_text_bottom' ? 'small lh-1' : '' }}">{{ $registerText }}</span>
                    @endif
                </a>
            @endif
        </div>

        {{-- Include Modals & Scripts if ANY action uses modals --}}
        @if($showModals)
            @push('footer_content')
                 {{-- Prevent duplicate inclusion using a shared view variable check --}}
                 @if(!isset($__auth_modals_included))
                     @php $__auth_modals_included = true; @endphp

                     {{-- Login Modal --}}
                     <x-modal id="loginModal" :header="false" body-class="p-0"></x-modal>

                     {{-- Register Modal --}}
                     <x-modal id="registerModal" :header="false" body-class="p-0"></x-modal>

                     {{-- Forgot Password Modal --}}
                     <x-modal id="forgotPasswordModal" :header="false" body-class="p-0"></x-modal>
                 @endif
            @endpush

            @push('scripts')
                @if(!isset($__auth_scripts_included))
                    @php $__auth_scripts_included = true; @endphp
                    @themeInclude('auth.includes.script')
                @endif
            @endpush
        @endif

    @else
        {{-- Authenticated User Menu (Reuses global profile menu) --}}
        @php
            $userMenuBtnClass = 'd-flex align-items-center';
            $userMenuNameClass = 'header-user-name small';

            [$btnExtra, $nameExtra] = match ($authDisplay) {
                'avatar_name_bottom' => ['flex-column text-center lh-1 gap-1', ''],
                'avatar_name'        => ['', 'ms-2'],
                default              => ['', 'd-none'],
            };

            $userMenuBtnClass .= ' ' . $btnExtra;
            $userMenuNameClass .= ' ' . $nameExtra;
        @endphp
        @themeInclude('partials.user-menu', [
            'menu_class' => 'header-user-menu',
            'menu_position' => 'right',
            'btn_class' => $userMenuBtnClass,
            'username_class' => $userMenuNameClass
        ])
    @endguest
</div>
