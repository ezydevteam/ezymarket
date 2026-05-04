@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - ID Verification'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            {{-- Header Card --}}
            <div class="card-v px-4 py-3 shadow-sm rounded-4 mb-4">
                <div class="card-v-header border-0 p-0 mb-n1">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-person-check-fill"></i>
                                </span>
                                {{ translate('ID Verification (KYC)') }}
                            </h5>
                        </div>
                        <div>
                            @if (authUser()->isIdVerified())
                                <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-patch-check-fill me-1"></i>
                                    {{ translate('Verified') }}
                                </span>
                            @elseif(authUser()->isIdPending())
                                <span class="badge bg-warning px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    {{ translate('Pending Review') }}
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ translate('Not Verified') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (authUser()->isIdVerified())
                {{-- Verified State --}}
                <div class="card-v px-4 py-5 shadow-sm rounded-4 text-center">
                    <div class="icon-circle icon-circle-xl bg-success bg-opacity-10 text-success mx-auto mb-4 border border-success border-opacity-10 border-2">
                        <i class="bi bi-shield-fill-check display-4"></i>
                    </div>
                    <h3 class="fw-bold text-gray-900 mb-2">{{ translate('Identity Verified') }}</h3>
                    <p class="text-gray-700 mb-4 max-w-600 mx-auto">
                        {{ translate('Congratulations! Your identity has been successfully verified. You now have full access to all platform features, higher limits, and increased trust within our community.') }}
                    </p>
                    <div class="col-lg-6 mx-auto">
                        <div class="p-3 bg-success-subtle rounded-4 text-success border border-success border-opacity-10">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ translate('Verified on') }} {{ $idVerification?->updated_at?->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            @elseif (authUser()->isIdPending())
                {{-- Pending State --}}
                <div class="card-v px-4 py-5 shadow-sm rounded-4 text-center">
                    <div class="icon-circle icon-circle-xl bg-warning bg-opacity-10 text-warning mx-auto mb-4 border border-warning border-opacity-10 border-2">
                        <i class="bi bi-clock-history display-4"></i>
                    </div>
                    <h3 class="fw-bold text-gray-900 mb-2">{{ translate('Verification in Progress') }}</h3>
                    <p class="text-gray-700 mb-4 max-w-600 mx-auto">
                        {{ translate('We have received your documents and our security team is currently reviewing them. This process usually takes 24-48 hours. You will receive a notification once the review is complete.') }}
                    </p>
                    <div class="col-lg-6 mx-auto">
                        <div class="p-3 bg-warning-subtle rounded-4 text-dark border border-warning border-opacity-10">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            {{ translate('Submitted on') }} {{ $idVerification?->created_at?->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            @else
                {{-- Form State --}}
                <form action="{{ route('user.settings.id-verification.store') }}" method="POST" class="ajax-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        {{-- Main Column --}}
                        <div class="col-lg-7">
                            <div class="card-v px-4 py-4 shadow-sm rounded-4 h-100">
                                <h6 class="fw-bold mb-4 text-gray-800">{{ translate('Verify Your Identity') }}</h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-gray-700">{{ translate('Document Type') }}</label>
                                        <select id="kycDocument" name="document_type" class="form-select selectpicker">
                                            <option value="national_id">{{ translate('National ID Card') }}</option>
                                            <option value="passport">{{ translate('International Passport') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="nationalIDNumber">
                                        <label class="form-label fw-semibold text-gray-700">{{ translate('Document ID Number') }}</label>
                                        <input type="text" name="national_id_number" class="form-control" placeholder="e.g. 123456789">
                                    </div>
                                    <div class="col-md-6 d-none" id="passportNumber">
                                        <label class="form-label fw-semibold text-gray-700">{{ translate('Passport Number') }}</label>
                                        <input type="text" name="passport_number" class="form-control" placeholder="e.g. A12345678">
                                    </div>
                                </div>

                                {{-- National ID Uploads --}}
                                <div id="nationalId" class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 text-center">
                                            <h6 class="fs-13 fw-bold mb-3 text-gray-700">{{ translate('Front Side') }}</h6>
                                            <div class="bg-white p-2 rounded-3 shadow-sm mb-3 border border-white">
                                                <div class="image-preview-wrapper ratio ratio-16x9">
                                                    <img id="image-preview-1" src="{{ asset(@settings('id_verification')->id_front_image) }}"
                                                        class="object-fit-contain rounded-2">
                                                </div>
                                            </div>
                                            <label class="btn btn-primary btn-sm rounded-pill px-4">
                                                <i class="bi bi-upload me-1"></i> {{ translate('Upload Photo') }}
                                                <input type="file" name="front_of_id" class="d-none image-input" data-id="1" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 text-center">
                                            <h6 class="fs-13 fw-bold mb-3 text-gray-700">{{ translate('Back Side') }}</h6>
                                            <div class="bg-white p-2 rounded-3 shadow-sm mb-3 border border-white">
                                                <div class="image-preview-wrapper ratio ratio-16x9">
                                                    <img id="image-preview-2" src="{{ asset(@settings('id_verification')->id_back_image) }}"
                                                        class="object-fit-contain rounded-2">
                                                </div>
                                            </div>
                                            <label class="btn btn-primary btn-sm rounded-pill px-4">
                                                <i class="bi bi-upload me-1"></i> {{ translate('Upload Photo') }}
                                                <input type="file" name="back_of_id" class="d-none image-input" data-id="2" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Passport Upload --}}
                                <div id="passport" class="d-none">
                                    <div class="p-4 border rounded-4 bg-light bg-opacity-50 text-center">
                                        <h6 class="fs-13 fw-bold mb-3 text-gray-700">{{ translate('Passport Main Page') }}</h6>
                                        <div class="bg-white p-2 rounded-3 shadow-sm mb-3 border border-white">
                                            <div class="image-preview-wrapper ratio" style="--bs-aspect-ratio: 50%;">
                                                <img id="image-preview-4" src="{{ asset(@settings('id_verification')->passport_image) }}"
                                                    class="object-fit-contain rounded-2">
                                            </div>
                                        </div>
                                        <label class="btn btn-primary btn-sm rounded-pill px-4">
                                            <i class="bi bi-upload me-1"></i> {{ translate('Upload Photo') }}
                                            <input type="file" name="passport" class="d-none image-input" data-id="4" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                {{-- Photo Verification (Selfie) --}}
                                @if (@settings('id_verification')->photo_verification)
                                    <div class="mt-4 pt-4 border-top">
                                        <h6 class="fw-bold mb-3 text-gray-800">{{ translate('Photo Verification') }}</h6>
                                        <div class="p-4 border rounded-4 bg-primary-subtle text-primary text-center">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-4 text-center">
                                                    <div class="bg-white p-2 rounded-circle shadow-sm d-inline-block border border-white">
                                                        <div class="ratio ratio-1x1 overflow-hidden rounded-circle" style="width: 120px;">
                                                            <img id="image-preview-3" src="{{ asset(@settings('id_verification')->selfie_image) }}"
                                                                class="object-fit-cover">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8 text-md-start">
                                                    <p class="text-gray-700 fs-14 mb-3">
                                                        {{ translate('Please upload a clear selfie of you holding your ID. Ensure your face and ID details are clearly visible.') }}
                                                    </p>
                                                    <label class="btn btn-primary rounded-pill px-4">
                                                        <i class="bi bi-camera me-1"></i> {{ translate('Upload Selfie') }}
                                                        <input type="file" name="selfie" class="d-none image-input" data-id="3" accept="image/*">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-5 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg btn-modern w-100">
                                        <i class="bi bi-send me-2"></i>
                                        {{ translate('Submit Verification Request') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <div class="col-lg-5">
                            <div class="card-v px-4 py-4 shadow-sm rounded-4 h-100">
                                <h6 class="fw-bold mb-4 d-flex align-items-center">
                                    <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2 shadow-sm">
                                        <i class="bi bi-lightbulb"></i>
                                    </span>
                                    {{ translate('Verification Guide') }}
                                </h6>

                                <div class="vstack gap-4">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-circle icon-circle-sm bg-light text-gray-600 me-3">
                                            <i class="bi bi-brightness-high"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-14 text-gray-800">{{ translate('Good Lighting') }}</div>
                                            <div class="text-gray-600 fs-13">
                                                {{ translate('Ensure documents are well-lit and not blurry.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start">
                                        <div class="icon-circle icon-circle-sm bg-light text-gray-600 me-3">
                                            <i class="bi bi-crop"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-14 text-gray-800">{{ translate('No Cropping') }}</div>
                                            <div class="text-gray-600 fs-13">
                                                {{ translate('Ensure all four corners of the document are visible.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start">
                                        <div class="icon-circle icon-circle-sm bg-light text-gray-600 me-3">
                                            <i class="bi bi-card-text"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-14 text-gray-800">{{ translate('Legible Text') }}</div>
                                            <div class="text-gray-600 fs-13">
                                                {{ translate('Text must be clearly readable without shadows.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start">
                                        <div class="icon-circle icon-circle-sm bg-light text-gray-600 me-3">
                                            <i class="bi bi-file-earmark-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-14 text-gray-800">{{ translate('Original Documents') }}</div>
                                            <div class="text-gray-600 fs-13">
                                                {{ translate('Photocopies or digital versions are not accepted.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto pt-5">
                                    <div class="p-3 bg-light rounded-4 border border-dashed">
                                        <div class="d-flex align-items-start small text-gray-600">
                                            <i class="bi bi-shield-lock-fill text-primary me-2 mt-1"></i>
                                            <div>
                                                <span class="fw-bold text-gray-800">{{ translate('Secure & Encrypted') }}</span>
                                                <br>
                                                {{ translate('Your identity data is encrypted and used only for verification purposes. We do not share your documents.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
