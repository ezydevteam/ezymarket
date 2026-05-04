@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Edit :provider', ['provider' => $captchaProvider->name]))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        {{-- Captcha Header --}}
        <div class="card-header bg-white border-bottom p-4">
            {{-- Action Buttons --}}
            <div class="d-flex align-items-center justify-content-end gap-3 mb-2">
                <a href="{{ route('admin.settings.captcha.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>{{ translate('Back') }}
                </a>
                @if (!$captchaProvider->isDefault())
                    <a href="{{ route('admin.settings.captcha.default', $captchaProvider->id) }}"
                        data-method="POST"
                        data-confirm="{{ translate('Are you sure want to make :captcha as a default captcha provider?', ['captcha' => $captchaProvider->name]) }}"
                        class="btn btn-warning action-confirm">
                       <i class="bi bi-star me-2"></i>{{ translate('Make Default') }}
                    </a>
                @endif
                <button type="submit" form="captchaConfigForm" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>{{ translate('Save') }}
                </button>
            </div>
            <div class="text-center">
                <div class="image-fluid image-lg d-inline-block mb-2">
                    <img src="{{ $captchaProvider->logo_url }}" alt="{{ translate($captchaProvider->name) }}">
                </div>
                <h4 class="mb-2 fw-semibold">
                    {{ translate($captchaProvider->name) }}
                    @if ($captchaProvider->isDefault())
                        <span class="badge bg-text-primary">
                            <i class="bi bi-star-fill me-1"></i>{{ translate('Default') }}
                        </span>
                    @endif
                </h4>
                <p class="text-muted mb-0">{{ translate('Configure captcha provider settings and API credentials') }}</p>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="card-body p-4">
            <form id="captchaConfigForm" action="{{ route('admin.settings.captcha.update', $captchaProvider->id) }}" method="POST">
                @csrf
                <div class="row align-items-center g-4">
                    {{-- API Credentials --}}
                    <div class="col-md-8">
                        <h6 class="mb-3">
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            {{ translate('API Credentials') }}
                        </h6>
                        <div class="row g-4">
                            {{-- Site Key --}}
                            <div class="col-12">
                                <label class="form-label">{{ translate('Site Key') }}</label>
                                <div class="input-group input-group-md">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="text"
                                        name="site_key"
                                        value="{{ old('site_key', hideInDemo($captchaProvider->site_key)) }}"
                                        class="form-control border-start-0 remove-spaces"
                                        placeholder="{{ translate('Enter site key') }}">
                                </div>
                                <small class="text-muted">{{ translate('The public site key used in your website\'s frontend forms') }}</small>
                            </div>

                            {{-- Secret Key --}}
                            <div class="col-12">
                                <label class="form-label">
                                    {{ translate('Secret Key') }}
                                </label>
                                <div class="input-group input-group-md">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="text"
                                        name="secret_key"
                                        value="{{ old('secret_key', hideInDemo($captchaProvider->secret_key)) }}"
                                        class="form-control border-start-0 remove-spaces"
                                        placeholder="{{ translate('Enter secret key') }}"
                                        autocomplete="off">
                                </div>
                                <small class="text-danger">
                                    {{ translate('Never share or expose this secret key') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Provider Status --}}
                    <div class="card bg-light rounded-3 p-4 h-100 col-md-4">
                        <h6 class="mb-3">
                            <i class="bi bi-toggle-on text-primary me-2"></i>
                            {{ translate('Provider Status') }}
                        </h6>
                        <x-switch
                            name="is_active"
                            id="switch-status"
                            :checked="$captchaProvider->isActive()"
                            onText="{{ translate('Active') }}"
                            offText="{{ translate('Inactive') }}"
                            :showLabel="false"/>
                        @if (!$captchaProvider->isActive())
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <small>{{ translate('This provider is currently disabled') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
