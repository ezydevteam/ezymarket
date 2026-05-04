@extends('admin.layouts.full')
@section('section', translate('Sections'))
@section('title', translate('Announcement'))
@section('container', 'container-max-lg')
@section('content')
    <form id="announcementSettingsForm" action="{{ route('admin.sections.announcement.update') }}" method="POST">
        @csrf
        <div class="card mb-4">
             <div class="card-header border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-text-primary">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ translate('Announcement') }}</h4>
                            <small class="text-muted">{{ translate('Configure the announcement bar settings') }}</small>
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

            <div class="card-body p-4">
                <div class="row align-items-center px-2 py-3 mx-0 mb-4 border rounded bg-light">
                    <div class="col-md-9 d-flex align-items-center gap-3">
                            <div class="card-icon bg-text-green">
                            <i class="bi bi-sliders2"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ translate('Announcement Status') }}</h6>
                            <p class="mb-0 small text-muted">{{ translate('Enable to show the announcement bar on the website.') }}</p>
                        </div>
                    </div>
                        <div class="col-md-3 text-md-end">
                            <x-switch
                            name="announcement[status]"
                            value="1"
                            :checked="@$settings->announcement->status"
                            :showLabel="false"
                            onLabel="{{ translate('Enabled') }}"
                            offLabel="{{ translate('Disabled') }}"
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ translate('Announcement Description') }}</label>
                    <textarea name="announcement[body]" class="form-control" rows="6">{{ @$settings->announcement->body }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Button Title') }}</label>
                        <input type="text" name="announcement[button_title]" class="form-control form-control-lg"
                            value="{{ @$settings->announcement->button_title }}">
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Button Link') }}</label>
                        <input type="text" name="announcement[button_link]" class="form-control form-control-lg"
                            value="{{ @$settings->announcement->button_link }}">
                    </div>
                    <div class="col-lg-12">
                        <label class="form-label">{{ translate('Announcement Background Color') }}</label>
                        <div class="colorpicker">
                            <input type="text" name="announcement[background_color]"
                                class="form-control form-control-lg coloris"
                                value="{{ @$settings->announcement->background_color }}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Button Background Color') }}</label>
                        <div class="colorpicker">
                            <input type="text" name="announcement[button_background_color]"
                                class="form-control form-control-lg coloris"
                                value="{{ @$settings->announcement->button_background_color }}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Button Text Color') }}</label>
                        <div class="colorpicker">
                            <input type="text" name="announcement[button_text_color]"
                                class="form-control form-control-lg coloris"
                                value="{{ @$settings->announcement->button_text_color }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/coloris/coloris.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/coloris/coloris.min.js') }}"></script>
    @endpush
@endsection


















