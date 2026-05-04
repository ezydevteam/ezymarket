@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - API Access'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card-v px-4 py-3 shadow-sm rounded-4 mb-4">
                <div class="card-v-header border-0 p-0 mb-n1">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2 shadow-sm">
                                    <i class="bi bi-code-square"></i>
                                </span>
                                {{ translate('API Access Management') }}
                            </h5>
                        </div>
                        <div>
                            @if ($user->api_key)
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-patch-check-fill me-1"></i>
                                    {{ translate('System Active') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-dash-circle me-1"></i>
                                    {{ translate('Not Generated') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Main Column --}}
                <div class="col-lg-7">
                    <div class="card-v px-4 py-4 shadow-sm rounded-4 h-100">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2 text-gray-800">{{ translate('Personal API Key') }}</h6>
                            <p class="text-gray-600 fs-14 mb-0">
                                {{ translate('Use this key to authenticate your requests when using our developer API. Your key should be kept secure and never shared publicly.') }}
                            </p>
                        </div>

                        @if ($user->api_key)
                            <div class="mb-4">
                                <label class="form-label fw-semibold small text-uppercase text-gray-700 mb-2">{{ translate('Your secret key') }}</label>
                                <div class="input-group dashboard-input-group shadow-none border rounded-3 p-1 bg-light">
                                    <input id="apiKey" type="text" class="form-control border-0 bg-transparent fs-14 fw-mono"
                                        value="{{ $user->api_key }}" readonly>
                                    <button class="btn btn-primary btn-md rounded-2 btn-copy px-3" data-clipboard-target="#apiKey">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </div>
                                <div class="form-text fs-13 text-gray-600 mt-2">
                                    {{ translate('Click to copy your unique API key.') }}
                                </div>
                            </div>
                        @else
                            <div class="p-5 rounded-4 bg-light bg-opacity-50 border border-dashed text-center mb-4">
                                <div class="icon-circle icon-circle-xl bg-white text-gray-400 mx-auto mb-4 shadow-sm border border-2">
                                    <i class="bi bi-key display-4"></i>
                                </div>
                                <h5 class="fw-bold text-gray-800 mb-2">{{ translate('No API Key Found') }}</h5>
                                <p class="text-gray-600 mb-0 px-lg-5 small">
                                    {{ translate('You haven\'t generated an API key yet. Generate one to start building with our platform.') }}
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('user.settings.api-key.generate') }}" method="POST" class="ajax-form">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-4 py-3 rounded-3 shadow-sm w-100 btn-modern {{ $user->api_key ? 'action-confirm' : '' }}"
                                @if($user->api_key) data-confirm="{{ translate('Generating a new API key will invalidate your current key. Any existing integrations using the old key will stop working. Are you sure?') }}" @endif>
                                <i class="bi {{ $user->api_key ? 'bi-arrow-repeat' : 'bi-plus-circle' }} me-2"></i>
                                {{ $user->api_key ? translate('Regenerate New API Key') : translate('Generate My First API Key') }}
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Sidebar Column --}}
                <div class="col-lg-5">
                    <div class="card-v px-4 py-4 shadow-sm rounded-4 bg-light-subtle h-100">
                        <h6 class="fw-bold mb-4 d-flex align-items-center">
                            <span class="icon-circle icon-circle-sm bg-white text-primary me-2 shadow-sm">
                                <i class="bi bi-journals"></i>
                            </span>
                            {{ translate('Developer Resources') }}
                        </h6>

                        <div class="vstack gap-3">
                            <a target="_blank" href="{{ route('api.docs') }}"
                                class="d-flex align-items-center p-3 rounded-4 bg-white border text-decoration-none transition-all hover-shadow">
                                <div class="icon-circle icon-circle-sm bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-file-earmark-code"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold fs-14 text-gray-800">{{ translate('API Documentation') }}</div>
                                    <div class="xsmall text-muted">{{ translate('Complete endpoint guide') }}</div>
                                </div>
                                <i class="bi bi-box-arrow-up-right text-muted small"></i>
                            </a>

                            <div class="p-4 rounded-4 bg-danger-subtle border border-danger-subtle border-opacity-25 mt-2">
                                <h6 class="fw-bold text-danger mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    {{ translate('Security Notice') }}
                                </h6>
                                <p class="text-danger-emphasis fs-13 mb-0 lh-base">
                                    {{ translate('Treat your API key with the same security as your password. If you believe your key has been compromised, regenerate it immediately.') }}
                                </p>
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="p-3 bg-light rounded-4 border border-dashed">
                                    <div class="d-flex align-items-start small text-gray-600">
                                        <i class="bi bi-shield-lock-fill text-primary me-2 mt-1"></i>
                                        <div>
                                            <span class="fw-bold text-gray-800">{{ translate('Restricted Access') }}</span>
                                            <br>
                                            {{ translate('API access is restricted to your account owner only. Ensure you are using HTTPS for all requests.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
