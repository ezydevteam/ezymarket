@extends('admin.layouts.full')
@section('section', translate('Id Verification'))
@section('title', translate('Settings'))
@section('container', 'container-max-lg')
@section('content')
<form id="verificationSettingsForm" action="{{ route('admin.id-verification.settings.update') }}" method="POST"
    enctype="multipart/form-data">
    @csrf

    {{-- Configuration Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="card-icon card-icon-md bg-text-primary me-3">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ translate('ID Verification Settings') }}</h5>
                        <p class="text-muted small mb-0">{{ translate('Configure verification requirements and
                            features') }}</p>
                    </div>
                </div>
                <div>
                    <button type="submit" form="verificationSettingsForm" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ translate('Save Settings') }}
                    </button>
                </div>
            </div>

            <div class="row g-3">
                {{-- Required --}}
                <div class="col-md-4">
                    <div class="d-flex flex-column align-items-center gap-3 p-3 bg-light rounded-3">
                        <div>
                            <h6 class="mb-1">{{ translate('Required/Optional') }}</h6>
                            <p class="text-muted small mb-0">{{ translate('Users must complete ID verification to buy or
                                sell products') }}</p>
                        </div>
                        <div class="ezydev-switch-wrapper-xl">
                            <input type="hidden" name="id_verification[required]" value="0">
                            <input id="kyc-required" class="ezydev-switch-input" type="checkbox"
                                name="id_verification[required]" value="1" {{ @$settings->id_verification->required ?
                            'checked' : '' }}>
                            <label class="ezydev-switch-label" for="kyc-required">
                                <span class="ezydev-switch-slider">
                                    <span class="ezydev-switch-button">
                                        <span class="ezydev-switch-on">{{ translate('Required') }}</span>
                                        <span class="ezydev-switch-off">{{ translate('Optional') }}</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Photo Verification --}}
                <div class="col-md-4">
                    <div class="d-flex flex-column align-items-center gap-3 p-3 bg-light rounded-3">
                        <div>
                            <h6 class="mb-1">{{ translate('Photo Verification') }}</h6>
                            <p class="text-muted small mb-0">{{ translate('Require users to upload a selfie photo during
                                verification') }}</p>
                        </div>
                        <div class="ezydev-switch-wrapper-xl">
                            <input type="hidden" name="id_verification[photo_verification]" value="0">
                            <input id="photo-verification" class="ezydev-switch-input" type="checkbox"
                                name="id_verification[photo_verification]" value="1" {{
                                @$settings->id_verification->photo_verification ? 'checked' : '' }}>
                            <label class="ezydev-switch-label" for="photo-verification">
                                <span class="ezydev-switch-slider">
                                    <span class="ezydev-switch-button">
                                        <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                        <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Auto Delete Documents --}}
                <div class="col-md-4">
                    <div class="d-flex flex-column align-items-center gap-3 p-3 bg-light rounded-3">
                        <div>
                            <h6 class="mb-1">{{ translate('Auto Delete Documents') }}</h6>
                            <p class="text-muted small mb-0">{{ translate('Enable or disable auto deletion of ID
                                verification documents') }}</p>
                        </div>
                        <div class="ezydev-switch-wrapper-xl">
                            <input type="hidden" name="id_verification[auto_delete]" value="0">
                            <input id="id-auto-delete" class="ezydev-switch-input" type="checkbox"
                                name="id_verification[auto_delete]" value="1" {{
                                @$settings->id_verification->auto_delete ? 'checked' : '' }}>
                            <label class="ezydev-switch-label" for="id-auto-delete">
                                <span class="ezydev-switch-slider">
                                    <span class="ezydev-switch-button">
                                        <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                        <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info border-0 bg-info bg-opacity-10 mt-4 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <span>{{ translate('When ID Verification is required the user will not be able to buy or sell products
                    until finish the Identity Verification.') }}</span>
            </div>
        </div>
    </div>

    {{-- Document Samples Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="card-icon card-icon-md bg-text-green me-3">
                    <i class="bi bi-card-image"></i>
                </div>
                <div>
                    <h5 class="mb-1">{{ translate('Document Samples') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Upload sample images to guide users during
                        verification') }}</p>
                </div>
            </div>

            <div class="row g-4">
                {{-- ID Front --}}
                <div class="col-md-6">
                    <div class="document-upload-wrapper">
                        <div class="document-preview rounded-3 overflow-hidden mb-3 border">
                            <img id="attach-image-preview-0"
                                src="{{ asset(@$settings->id_verification->id_front_image) }}" class="w-100"
                                style="height: 240px; object-fit: cover;" alt="ID Front Sample">
                        </div>
                        <input id="attach-image-targeted-input-0" type="file" name="id_verification[id_front_image]"
                            accept=".jpg,.jpeg,.png,.svg" class="form-control" hidden>
                        <button data-id="0" type="button"
                            class="attach-image-button btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-camera me-2"></i>{{ translate('ID Front Image') }}
                        </button>
                        <p class="text-muted small text-center mb-0">{{ translate('JPEG, JPG, PNG, SVG') }}</p>
                    </div>
                </div>

                {{-- ID Back --}}
                <div class="col-md-6">
                    <div class="document-upload-wrapper">
                        <div class="document-preview rounded-3 overflow-hidden mb-3 border">
                            <img id="attach-image-preview-1"
                                src="{{ asset(@$settings->id_verification->id_back_image) }}" class="w-100"
                                style="height: 240px; object-fit: cover;" alt="ID Back Sample">
                        </div>
                        <input id="attach-image-targeted-input-1" type="file" name="id_verification[id_back_image]"
                            accept=".jpg,.jpeg,.png,.svg" class="form-control" hidden>
                        <button data-id="1" type="button"
                            class="attach-image-button btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-camera me-2"></i>{{ translate('ID Back Image') }}
                        </button>
                        <p class="text-muted small text-center mb-0">{{ translate('JPEG, JPG, PNG, SVG') }}</p>
                    </div>
                </div>

                {{-- Passport --}}
                <div class="col-md-6">
                    <div class="document-upload-wrapper">
                        <div class="document-preview rounded-3 overflow-hidden mb-3 border">
                            <img id="attach-image-preview-4"
                                src="{{ asset(@$settings->id_verification->passport_image) }}" class="w-100"
                                style="height: 240px; object-fit: cover;" alt="Passport Sample">
                        </div>
                        <input id="attach-image-targeted-input-4" type="file" name="id_verification[passport_image]"
                            accept=".jpg,.jpeg,.png,.svg" class="form-control" hidden>
                        <button data-id="4" type="button"
                            class="attach-image-button btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-camera me-2"></i>{{ translate('Passport Image') }}
                        </button>
                        <p class="text-muted small text-center mb-0">{{ translate('JPEG, JPG, PNG, SVG') }}</p>
                    </div>
                </div>

                {{-- Selfie --}}
                <div class="col-md-6">
                    <div class="document-upload-wrapper">
                        <div class="document-preview rounded-3 overflow-hidden mb-3 border">
                            <img id="attach-image-preview-2"
                                src="{{ asset(@$settings->id_verification->selfie_image) }}" class="w-100"
                                style="height: 240px; object-fit: cover;" alt="Selfie Sample">
                        </div>
                        <input id="attach-image-targeted-input-2" type="file" name="id_verification[selfie_image]"
                            accept=".jpg,.jpeg,.png,.svg" class="form-control" hidden>
                        <button data-id="2" type="button"
                            class="attach-image-button btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-camera me-2"></i>{{ translate('Selfie Image') }}
                        </button>
                        <p class="text-muted small text-center mb-0">{{ translate('JPEG, JPG, PNG, SVG') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
