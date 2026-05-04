@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Newsletter Settings'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.settings.newsletter.update') }}" method="POST"
    enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        {{-- Configuration Card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">
                        <i class="bi bi-sliders2 text-primary me-2"></i>
                        {{ translate('Newsletter Configuration') }}
                    </h4>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> {{ translate('Save Changes') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        {{-- Newsletter Status --}}
                        <div class="col-12 px-4">
                            <div class="row align-items-center px-2 py-3 border rounded bg-light">
                                <div class="col-md-9 d-flex align-items-center gap-3">
                                    <div class="card-icon card-icon-lg bg-text-blue">
                                        <i class="bi bi-newspaper"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ translate('Newsletter System') }}</h6>
                                        <p class="mb-0 small text-muted">{{ translate('Enable to collect and manage
                                            subscribers.') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <x-switch name="newsletter[status]" value="1"
                                        :checked="@$settings->newsletter->status" :showLabel="false"
                                        onLabel="{{ translate('Enabled') }}" offLabel="{{ translate('Disabled') }}" />
                                </div>
                            </div>
                        </div>

                        {{-- Features Switches --}}
                        @php
                        $features = [
                        'popup_status' => [
                        'title' => 'Show Popup',
                        'desc' => 'Display subscription popup to visitors.'
                        ],
                        'footer_status' => [
                        'title' => 'Form In Footer',
                        'desc' => 'Display subscription form in website footer.'
                        ],
                        'register_new_users' => [
                        'title' => 'Auto-subscribe New Users',
                        'desc' => 'Automatically subscribe new users upon registration.'
                        ],
                        ];
                        @endphp

                        <div class="col-12">
                            <div class="row g-4">
                                @foreach($features as $key => $feature)
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="mb-3">
                                            <h6 class="mb-1 fw-semibold">{{ translate($feature['title']) }}</h6>
                                            <p class="mb-0 small text-muted">{{ translate($feature['desc']) }}</p>
                                        </div>
                                        <x-switch name="newsletter[{{ $key }}]" value="1"
                                            :checked="@$settings->newsletter->{$key}" size="xl" :showLabel="false"
                                            onLabel="{{ translate('Yes') }}" offLabel="{{ translate('No') }}" />
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Popup Settings --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-window-sidebar text-success me-2"></i>
                        {{ translate('Popup Customization') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Popup Image') }}</label>
                                <div class="input-group">
                                    <button type="button" class="btn bg-text-primary attach-image-button"
                                        data-id="popup_image">
                                        <i class="bi bi-upload me-2"></i>{{ translate('Choose Image') }}
                                    </button>
                                    <input type="text" id="attach-image-display-popup_image" class="form-control"
                                        value="{{ basename(@$settings->newsletter->popup_image) }}"
                                        placeholder="{{ translate('No File Selected') }}" disabled>
                                </div>
                                <div class="form-text">{{ translate('Supported: JPEG, PNG, SVG') }}</div>
                                <input type="file" name="newsletter[popup_image]"
                                    id="attach-image-targeted-input-popup_image" class="d-none" accept="image/*">
                            </div>

                            <div>
                                <label class="form-label fw-bold">{{ translate('Show Reminder After (Hours)') }}</label>
                                <div class="input-group">
                                    <input type="number" name="newsletter[popup_reminder_time]" class="form-control"
                                        value="{{ @$settings->newsletter->popup_reminder_time }}" min="1"
                                        placeholder="24">
                                    <span class="input-group-text px-3 text-muted">
                                        {{ translate('Hours') }}
                                    </span>
                                </div>
                                <div class="form-text">{{ translate('Time before showing the popup again after
                                    closing.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 pb-4">
                            <label class="form-label fw-bold d-block">{{ translate('Preview') }}</label>
                            <div
                                class="border rounded p-3 text-center bg-light h-100 d-flex align-items-center justify-content-center">
                                <img id="attach-image-preview-popup_image"
                                    src="{{ asset(@$settings->newsletter->popup_image) }}" alt="Popup Preview"
                                    class="img-fluid rounded shadow-sm" style="max-height: 150px; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MailChimp --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope-paper text-purple me-2"></i>
                        {{ translate('MailChimp Integration') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ translate('API Key') }}</label>
                            <input type="text" name="newsletter[api_key]" class="form-control"
                                value="{{ hideInDemo(@$settings->newsletter->api_key) }}" placeholder="your-api-key">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ translate('Audience ID') }}</label>
                            <input type="text" name="newsletter[audience_id]" class="form-control"
                                value="{{ hideInDemo(@$settings->newsletter->audience_id) }}"
                                placeholder="your-audience-id">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-light border mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>{{ translate('Find Credentials:') }}</strong> {{ translate('Account → Extras →
                                API Keys') }} & {{ translate('Audience → Settings → Audience name and defaults') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
