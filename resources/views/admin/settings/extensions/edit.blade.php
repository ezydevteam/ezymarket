@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Edit :extension', ['extension' => $extension->name]))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        {{-- Extension Header --}}
        <div class="card-header bg-white border-bottom p-4">
            {{-- Save Button --}}
            <div class="d-flex align-items-center justify-content-end gap-3 mb-2">
                <a href="{{ route('admin.settings.extensions.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>{{ translate('Back') }}
                </a>
                <button type="submit" form="extensionConfigForm" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>{{ translate('Save') }}
                </button>
            </div>
            <div class="text-center">
                <div class="image-fluid image-lg d-inline-block mb-2">
                    <img src="{{ $extension->logo_url }}" alt="{{ translate($extension->name) }}">
                </div>
                <h4 class="mb-2 fw-semibold">{{ translate($extension->name) }}</h4>
                <p class="text-muted mb-0">{{ translate('Configure extension settings and credentials') }}</p>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="card-body p-4">
            <form id="extensionConfigForm" action="{{ route('admin.settings.extensions.update', $extension->id) }}" method="POST">
                @csrf
                {{-- Extension Status --}}
                <div class="row align-items-center g-4">
                    {{-- API Credentials --}}
                    @if (count((array)$extension->credentials) > 0)
                    <div class="col-md-8">
                        <h6 class="mb-3">
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            {{ translate('API Credentials') }}
                        </h6>
                        <div class="row g-4">
                            @foreach ($extension->credentials as $key => $value)
                                <div class="col-12">
                                    <div class="input-group input-group-md">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-{{ Str::contains($key, ['key', 'secret', 'token']) ? 'key' : 'lock' }}"></i>
                                        </span>
                                        <input type="text"
                                            name="credentials[{{ $key }}]"
                                            value="{{ hideInDemo($value) }}"
                                            class="form-control border-start-0 remove-spaces"
                                            placeholder="{{ translate('Enter') }} {{ translate(str_replace('_', ' ', $key)) }}"
                                            {{ Str::contains($key, ['secret', 'password', 'token']) ? 'autocomplete=off' : '' }}>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                     <div class="col-md-4">
                        <h6 class="mb-3">
                            <i class="bi bi-toggle-on text-primary me-2"></i>
                            {{ translate('Extension Status') }}
                        </h6>
                        <x-switch
                            name="is_active"
                            id="switch-status"
                            :checked="$extension->isActive()"
                            onText="{{ translate('Enabled') }}"
                            offText="{{ translate('Disabled') }}"
                            :showLabel="false"/>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle text-muted mb-3" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mb-0">
                            {{ translate('This extension doesn\'t require additional configuration. Simply enable it to start using its features.') }}
                        </p>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection


















