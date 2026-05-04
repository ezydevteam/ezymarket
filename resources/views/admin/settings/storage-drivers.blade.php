@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Storage Drivers'))
@section('container', 'container-max-lg')
@section('content')
    <form id="storageDriversForm" action="{{ route('admin.settings.storage-drivers.update') }}" method="POST">
        @csrf

        {{-- Storage Driver Selection --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-text-primary">
                            <i class="bi bi-hdd-network"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ translate('Storage Driver Configuration') }}</h6>
                            <small class="text-muted">{{ translate('Choose where your files will be stored') }}</small>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>
                            {{ translate('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-primary bg-info bg-opacity-10 border-0 mb-4">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill text-info me-3 mt-1"></i>
                        <div>
                            <strong>{{ translate('About Storage Drivers') }}</strong>
                            <p class="mb-0 mt-1 small text-muted">{{ translate('Local storage keeps files on your server, while cloud providers offer scalability and redundancy.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">
                        {{ translate('Select Storage Driver') }}
                    </label>
                    <select id="storage-driver" name="storage_driver" class="form-select form-select-lg selectpicker">
                        @foreach ($storageDrivers as $storageDriver)
                            <option value="{{ $storageDriver->alias }}"
                                {{ $storageDriver->isDefault() ? 'selected' : '' }}>
                                {{ $storageDriver->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted mt-2">
                        <i class="bi bi-check2-circle me-1"></i> {{ translate('Current active storage driver for all file uploads') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Credentials Section (Only for Cloud Storage) --}}
        @php
            $isDefaultLocal = false;
            foreach ($storageDrivers as $storageDriver) {
                if ($storageDriver->isDefault() && $storageDriver->isLocal()) {
                    $isDefaultLocal = true;
                    break;
                }
            }
        @endphp

        <div class="credentials-section {{ $isDefaultLocal ? 'd-none' : '' }}">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                     <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-text-orange">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ translate('Driver Credentials') }}</h6>
                            <small class="text-muted">{{ translate('API keys and connection details') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach ($storageDrivers as $driver)
                            @if (!$driver->isLocal() && $driver->credentials)
                                @foreach ($driver->credentials as $key => $value)
                                    <div class="col-12 credential-field credential-{{ str_replace('_', '-', $driver->alias) }} {{ !$driver->isDefault() ? 'd-none' : '' }}">
                                        <label class="form-label fw-semibold text-capitalize">
                                            {{ str_replace('_', ' ', $key) }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                @if(str_contains($key, 'key'))
                                                    <i class="bi bi-key text-muted"></i>
                                                @elseif(str_contains($key, 'secret'))
                                                    <i class="bi bi-shield-lock text-muted"></i>
                                                @elseif(str_contains($key, 'region'))
                                                    <i class="bi bi-globe text-muted"></i>
                                                @elseif(str_contains($key, 'bucket'))
                                                    <i class="bi bi-box-seam text-muted"></i>
                                                @elseif(str_contains($key, 'endpoint'))
                                                    <i class="bi bi-link-45deg text-muted"></i>
                                                @else
                                                    <i class="bi bi-pencil text-muted"></i>
                                                @endif
                                            </span>
                                            <input type="text"
                                                name="credentials[{{ $driver->alias }}][{{ $key }}]"
                                                value="{{ hideInDemo($value) }}"
                                                class="form-control border-start-0 ps-0 remove-spaces"
                                                placeholder="{{ translate('Enter') }} {{ str_replace('_', ' ', $key) }}">
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Local Storage Message --}}
        <div class="local-storage-message {{ !$isDefaultLocal ? 'd-none' : '' }}">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-3 text-success">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ translate('Local Storage Active') }}</h5>
                    <p class="text-muted mb-0">{{ translate('No additional configuration required. Files are stored securely on your server.') }}</p>
                </div>
            </div>
        </div>
    </form>

    {{-- Migration Instructions (Only for Cloud Storage) --}}
    <div class="migration-section {{ $isDefaultLocal ? 'd-none' : '' }}">
         <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-icon bg-text-purple">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ translate('Migration Guide') }}</h6>
                        <small class="text-muted">{{ translate('Instructions for moving files') }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info bg-info bg-opacity-10 border-0 mb-4">
                    <div class="d-flex gap-2">
                        <i class="bi bi-question-circle-fill text-info me-3 mt-1"></i>
                        <div>
                            <strong>{{ translate('Important: Manual Migration Required') }}</strong>
                            <p class="mb-0 mt-1 small text-muted">{{ translate('When changing storage drivers, you must manually transfer all existing files from the old storage to the new one to ensure links don\'t break.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-4 rounded-3 border bg-light h-100">
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-hdd text-primary me-2"></i>
                                {{ translate('Local Storage Paths') }}
                            </h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <code class="text-dark">public/images/editor/</code>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <code class="text-dark">public/images/products/</code>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <code class="text-dark">public/files/products/</code>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <code class="text-dark">storage/app/files/</code>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <code class="text-dark">storage/app/files/products/</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-3 border bg-light h-100">
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-cloud text-info me-2"></i>
                                {{ translate('Cloud Storage Paths') }}
                            </h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-info me-2"></i>
                                    <code class="text-dark">images/editor/</code>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-info me-2"></i>
                                    <code class="text-dark">images/products/</code>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-info me-2"></i>
                                    <code class="text-dark">files/products/</code>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-info me-2"></i>
                                    <code class="text-dark">files/</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Connection Test (Only for Cloud Storage) --}}
    <div class="test-section {{ $isDefaultLocal ? 'd-none' : '' }}">
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                         <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                            <div class="card-icon bg-text-dark">
                                <i class="bi bi-plug"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ translate('Test Storage Connection') }}</h6>
                                <p class="small text-muted mb-0">{{ translate('Verify your storage credentials are working correctly by creating a temporary test file.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                         <form action="{{ route('admin.settings.storage-drivers.test') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-lightning-charge me-2"></i>
                                {{ translate('Test Connection') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('storage-driver');

            if (select) {
                select.addEventListener('change', function() {
                    var driver = this.value.replace(/_/g, '-');
                    var isLocal = this.value === 'local';

                    // Hide all credential fields
                    var allFields = document.querySelectorAll('.credential-field');
                    for (var i = 0; i < allFields.length; i++) {
                        allFields[i].classList.add('d-none');
                    }

                    // Show selected driver's fields
                    var selectedFields = document.querySelectorAll('.credential-' + driver);
                    for (var i = 0; i < selectedFields.length; i++) {
                        selectedFields[i].classList.remove('d-none');
                    }

                    // Toggle sections based on local vs cloud storage
                    var credentialsSection = document.querySelector('.credentials-section');
                    var localStorageMessage = document.querySelector('.local-storage-message');
                    var migrationSection = document.querySelector('.migration-section');
                    var testSection = document.querySelector('.test-section');

                    if (isLocal) {
                        // Show local storage message, hide cloud sections
                        if (credentialsSection) credentialsSection.classList.add('d-none');
                        if (localStorageMessage) localStorageMessage.classList.remove('d-none');
                        if (migrationSection) migrationSection.classList.add('d-none');
                        if (testSection) testSection.classList.add('d-none');
                    } else {
                        // Show cloud sections, hide local storage message
                        if (credentialsSection) credentialsSection.classList.remove('d-none');
                        if (localStorageMessage) localStorageMessage.classList.add('d-none');
                        if (migrationSection) migrationSection.classList.remove('d-none');
                        if (testSection) testSection.classList.remove('d-none');
                    }
                });
            }
        });
    </script>
@endpush
