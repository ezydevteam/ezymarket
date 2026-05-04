@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Edit :provider', ['provider' => $socialAuth->name]))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        {{-- Provider Header --}}
        <div class="card-header bg-white border-bottom p-4">
            {{-- Action Buttons --}}
            <div class="d-flex align-items-center justify-content-end gap-3 mb-2">
                <a href="{{ route('admin.settings.social-auth.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>{{ translate('Back') }}
                </a>
                <button type="submit" form="socialAuthConfigForm" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>{{ translate('Save') }}
                </button>
            </div>
            <div class="text-center">
                <div class="image-fluid image-lg d-inline-block mb-2">
                    <img id="social-auth-logo" src="{{ $socialAuth->logo_url }}" alt="{{ translate($socialAuth->name) }}">
                </div>
                <h4 class="mb-2 fw-semibold">{{ translate($socialAuth->name) }}</h4>
                <p class="text-muted mb-0">{{ translate('Configure OAuth credentials and settings') }}</p>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="card-body p-4">
            <form id="socialAuthConfigForm" action="{{ route('admin.settings.social-auth.update', $socialAuth->id) }}" method="POST">
                @csrf
                <div class="row g-4">
                    {{-- Provider Name & Type --}}
                    <div class="col-md-8">
                        <h6 class="mb-3">
                            <i class="bi bi-tag text-primary me-2"></i>
                            {{ translate('Provider Name') }}
                        </h6>
                        <label class="form-label">{{ translate('Provider Name') }}</label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-tag"></i>
                            </span>
                            <input type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $socialAuth->name) }}"
                                class="form-control border-start-0"
                                placeholder="{{ translate('Enter provider name') }}"
                                required>
                        </div>
                        <small class="text-muted">{{ translate('Display name shown to users') }}</small>
                    </div>

                    {{-- Provider Status --}}
                    <div class="col-md-4 pt-4">
                        <h6 class="mb-3">
                            <i class="bi bi-toggle-on text-primary me-2"></i>
                            {{ translate('Provider Status') }}
                        </h6>
                        <x-switch
                            name="is_active"
                            id="switch-status"
                            :checked="$socialAuth->isActive()"
                            onText="{{ translate('Active') }}"
                            offText="{{ translate('Inactive') }}"
                            :showLabel="false"/>
                    </div>

                    {{-- Display Type --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('Display Type') }}</label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-palette"></i>
                            </span>
                            <select class="form-select border-start-0" id="type" name="type" required>
                                @foreach(\App\Models\SocialAuth::getTypeOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $socialAuth->type) == $value ? 'selected' : '' }}>
                                        {{ translate($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">{{ translate('How the login button should be displayed') }}</small>
                    </div>

                    {{-- Logo Upload --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('Provider Logo') }}</label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <img id="attach-image-preview-social-auth"
                                    src="{{ $socialAuth->logo_url }}"
                                    width="24" height="24"
                                    style="object-fit: contain;"
                                    alt="">
                            </span>
                            <input type="text"
                                class="form-control border-start-0"
                                id="logoFilename"
                                value="{{ $socialAuth->logo ? basename($socialAuth->logo) : '' }}"
                                placeholder="{{ translate('No file selected') }}"
                                readonly>
                            <button type="button" class="btn bg-text-green attach-image-button m-0" data-id="social-auth">
                                <i class="bi bi-cloud-upload me-1"></i>{{ translate('Upload') }}
                            </button>
                        </div>
                        <input id="attach-image-targeted-input-social-auth"
                            type="file"
                            name="logo"
                            accept="image/png,image/jpg,image/jpeg,image/svg+xml"
                            hidden>
                        <small class="text-muted">{{ translate('PNG, JPG, SVG (Max: 2MB)') }}</small>
                    </div>

                    {{-- Client ID --}}
                    <div class="col-12">
                        <h6 class="mb-3">
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            {{ translate('OAuth Credentials') }}
                        </h6>
                        <label class="form-label">{{ translate('Client ID') }}</label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="text"
                                name="client_id"
                                value="{{ old('client_id', $socialAuth->client_id) }}"
                                class="form-control border-start-0 remove-spaces"
                                placeholder="{{ translate('Enter OAuth Client ID') }}">
                        </div>
                        <small class="text-muted">{{ translate('The public client/application ID from your OAuth app') }}</small>
                    </div>

                    {{-- Client Secret --}}
                    <div class="col-12">
                        <label class="form-label">
                            {{ translate('Client Secret') }}
                        </label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="text"
                                name="client_secret"
                                value="{{ old('client_secret', hideInDemo(strip_tags($socialAuth->client_secret))) }}"
                                class="form-control border-start-0 remove-spaces"
                                placeholder="{{ translate('Enter OAuth Client Secret') }}"
                                autocomplete="off">
                        </div>
                        <small class="text-danger">
                            {{ translate('Keep this secret key secure and confidential') }}
                        </small>
                    </div>

                    {{-- Callback URL --}}
                    <div class="col-12">
                        <label class="form-label">{{ translate('OAuth Callback URL') }}</label>
                        <div class="input-group input-group-md">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-link-45deg"></i>
                            </span>
                            <input type="text"
                                class="form-control border-start-0"
                                id="callback-url"
                                value="{{ url('/') }}/oauth/{{ strtolower($socialAuth->alias) }}/callback"
                                readonly>
                            <button class="btn bg-text-primary btn-copy" type="button" data-clipboard-target="#callback-url">
                                <i class="bi bi-clipboard me-1"></i>{{ translate('Copy') }}
                            </button>
                        </div>
                        <small class="text-muted">{{ translate('Use this URL in your OAuth app configuration') }}</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush
