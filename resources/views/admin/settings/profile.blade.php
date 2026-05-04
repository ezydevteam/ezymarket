@extends('admin.layouts.form')
@section('section', translate('Settings'))
@section('title', translate('Profile Settings'))
@section('description', translate('Upload default avatar and cover images for user profiles.'))
@section('container', 'container-max-xl')
@section('content')
<form id="ezydev-form" action="{{ route('admin.settings.profile.update') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3 mb-4">
            <div class="card admin-settings-sidebar sticky-top">
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills" id="settings-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active d-flex align-items-center" id="avatar-tab" data-bs-toggle="pill"
                            data-bs-target="#avatar" type="button" role="tab" aria-controls="avatar"
                            aria-selected="true">
                            <i class="fa fa-user-circle me-2"></i>
                            <span>{{ translate('Avatar') }}</span>
                        </button>
                        <button class="nav-link d-flex align-items-center" id="cover-tab" data-bs-toggle="pill"
                            data-bs-target="#cover" type="button" role="tab" aria-controls="cover"
                            aria-selected="false">
                            <i class="fa fa-image me-2"></i>
                            <span>{{ translate('Cover') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="col-lg-9">
            <div class="tab-content" id="settings-tabContent">

                {{-- Avatar Tab --}}
                <div class="tab-pane fade show active" id="avatar" role="tabpanel" aria-labelledby="avatar-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fa fa-user-circle me-2"></i>{{ translate('Default Avatar Image')
                                }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <i class="fa fa-info-circle me-2"></i>
                                        {{ translate('This default avatar will be used for users who haven\'t uploaded
                                        their own profile picture') }}
                                    </div>
                                </div>

                                {{-- Avatar Image Upload --}}
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="codebay-feature-icon mb-3">
                                                <i class="fa fa-user-circle"></i>
                                            </div>
                                            <h6 class="codebay-feature-title mb-3">
                                                {{ translate('Upload Default Avatar') }}
                                            </h6>

                                            <div class="image-box p-4 border bg-light rounded-3 text-center">
                                                <div class="mb-4">
                                                    <img id="image-preview-0"
                                                        class="border p-3 rounded-circle bg-white shadow"
                                                        src="{{ asset(@$settings->profile->default_avatar) }}"
                                                        alt="default_avatar"
                                                        style="width: 150px; height: 150px; object-fit: cover;">
                                                </div>
                                                <input type="file" name="profile[default_avatar]"
                                                    class="form-control form-control-lg image-input" data-id="0"
                                                    accept=".jpg,.jpeg,.png">
                                                <div class="form-text mt-3">
                                                    <i class="fa fa-info-circle me-1"></i>
                                                    {{ translate('Supported formats: JPEG, JPG, PNG') }}
                                                </div>
                                                <div class="form-text">
                                                    <i class="fa fa-ruler me-1"></i>
                                                    {{ translate('Recommended size: 120x120 pixels') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Avatar Info --}}
                                <div class="col-12">
                                    <div class="card bg-light border-success">
                                        <div class="card-body">
                                            <h6 class="text-success mb-3">
                                                <i class="fa fa-lightbulb me-2"></i>{{ translate('Avatar Best
                                                Practices') }}
                                            </h6>
                                            <ul class="mb-0">
                                                <li>{{ translate('Use a square image with equal width and height') }}
                                                </li>
                                                <li>{{ translate('Recommended dimensions: 120x120 pixels or larger') }}
                                                </li>
                                                <li>{{ translate('Keep the file size under 500KB for better
                                                    performance') }}</li>
                                                <li>{{ translate('Use a simple, recognizable icon or silhouette') }}
                                                </li>
                                                <li>{{ translate('Ensure the image looks good in a circular frame') }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cover Tab --}}
                <div class="tab-pane fade" id="cover" role="tabpanel" aria-labelledby="cover-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fa fa-image me-2"></i>{{ translate('Default Cover Image') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <i class="fa fa-info-circle me-2"></i>
                                        {{ translate('This default cover image will be used for user profiles who
                                        haven\'t uploaded their own cover photo') }}
                                    </div>
                                </div>

                                {{-- Cover Image Upload --}}
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="codebay-feature-icon mb-3">
                                                <i class="fa fa-image"></i>
                                            </div>
                                            <h6 class="codebay-feature-title mb-3">
                                                {{ translate('Upload Default Cover') }}
                                            </h6>

                                            <div class="image-box p-4 border bg-light rounded-3 text-center">
                                                <div class="mb-4">
                                                    <img id="image-preview-1"
                                                        class="border p-2 rounded-3 bg-white shadow"
                                                        src="{{ asset(@$settings->profile->default_cover) }}"
                                                        alt="default_cover"
                                                        style="max-width: 100%; height: auto; max-height: 250px;">
                                                </div>
                                                <input type="file" name="profile[default_cover]"
                                                    class="form-control form-control-lg image-input" data-id="1"
                                                    accept=".jpg,.jpeg,.png">
                                                <div class="form-text mt-3">
                                                    <i class="fa fa-info-circle me-1"></i>
                                                    {{ translate('Supported formats: JPEG, JPG, PNG') }}
                                                </div>
                                                <div class="form-text">
                                                    <i class="fa fa-ruler me-1"></i>
                                                    {{ translate('Recommended size: 1200x500 pixels') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cover Info --}}
                                <div class="col-12">
                                    <div class="card bg-light border-success">
                                        <div class="card-body">
                                            <h6 class="text-success mb-3">
                                                <i class="fa fa-lightbulb me-2"></i>{{ translate('Cover Best Practices')
                                                }}
                                            </h6>
                                            <ul class="mb-0">
                                                <li>{{ translate('Use a landscape/wide format image (aspect ratio:
                                                    2.4:1)') }}</li>
                                                <li>{{ translate('Recommended dimensions: 1200x500 pixels') }}</li>
                                                <li>{{ translate('Keep the file size under 2MB for faster loading') }}
                                                </li>
                                                <li>{{ translate('Avoid placing important content in the center (avatar
                                                    may overlap)') }}</li>
                                                <li>{{ translate('Use vibrant colors or gradients for visual appeal') }}
                                                </li>
                                                <li>{{ translate('Ensure text and details are readable on all devices')
                                                    }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Aspect Ratio Guide --}}
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="text-info mb-3">
                                                <i class="fa fa-ruler-combined me-2"></i>{{ translate('Cover Image
                                                Aspect Ratio') }}
                                            </h6>
                                            <div class="border rounded-3 p-3 bg-light">
                                                <div
                                                    style="width: 100%; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; position: relative;">
                                                    <div
                                                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center;">
                                                        <strong>1200 x 500 pixels</strong><br>
                                                        <small>(2.4:1 ratio)</small>
                                                    </div>
                                                    <div
                                                        style="position: absolute; bottom: 10px; left: 10px; width: 40px; height: 40px; background: white; border-radius: 50%; border: 3px solid #764ba2;">
                                                    </div>
                                                </div>
                                                <p class="text-center text-muted mt-3 mb-0">
                                                    <small>{{ translate('The avatar (circle) will overlay the
                                                        bottom-left corner of the cover image') }}</small>
                                                </p>
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
    </div>
</form>
@endsection
