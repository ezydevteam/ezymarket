<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-person-lock me-2"></i>{{ translate('Two-factor Authentication') }}</div>
        @if (!$admin->google2fa_status)
            <span class="badge bg-danger">{{ translate('2FA Disabled') }}</span>
        @else
            <span class="badge bg-success">{{ translate('2FA Enabled') }}</span>
        @endif
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            {{ translate('Two-factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. Two-factor authentication protects against phishing, social engineering, and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.') }}
        </div>

        @if (!$admin->google2fa_status)
            <div class="row justify-content-center mb-4">
                <div class="col-lg-12">
                    <div class="card border">
                        <div class="card-body text-center p-4">
                            <h6 class="mb-3"><i class="bi bi-qrcode me-2"></i>{{ translate('Scan QR Code') }}</h6>
                            <div class="mb-3">
                                {!! $qrCode !!}
                            </div>
                            <div class="input-group mb-3">
                                <input id="input-secret" type="text" class="form-control form-control-lg"
                                    value="{{ $admin->google2fa_secret }}" readonly>
                                <button class="btn btn-secondary btn-copy" data-clipboard-target="#input-secret" type="button" title="Copy">
                                    <i class="far fa-clone"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mb-3">
                                {{ translate('Scan with Google Authenticator app or enter the secret key manually') }}
                            </small>
                            <button type="button" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#enable2FAModal">
                                <i class="bi bi-lock me-2"></i>{{ translate('Enable 2FA Authentication') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row justify-content-center mb-4">
                <div class="col-lg-12">
                    <div class="card border-success">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-check-circle text-success" style="font-size: 64px;"></i>
                            </div>
                            <h5 class="text-success mb-3">{{ translate('2FA is Active') }}</h5>
                            <p class="text-muted mb-3">
                                {{ translate('Your account is protected with two-factor authentication') }}
                            </p>
                            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#disable2FAModal">
                                <i class="bi bi-unlock me-2"></i>{{ translate('Disable 2FA Authentication') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="border-top pt-4">
            <h6 class="mb-3">{{ translate('Compatible Authenticator Apps') }}</h6>
            <p class="text-muted mb-3">
                {{ translate('To use the two factor authentication, you have to install a Google Authenticator compatible app. Here are some that are currently available:') }}
            </p>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="fab fa-apple text-dark me-2"></i>
                    <a target="_blank" href="https://apps.apple.com/us/app/google-authenticator/id388497605">
                        {{ translate('Google Authenticator for iOS') }}
                    </a>
                </li>
                <li class="mb-2">
                    <i class="fab fa-android text-success me-2"></i>
                    <a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en&gl=US">
                        {{ translate('Google Authenticator for Android') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
