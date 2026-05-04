@extends('admin.layouts.form')
@section('section', translate('System'))
@section('title', translate('Maintenance Mode'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.system.maintenance') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Info Alert --}}
    <div class="alert alert-info border-0 shadow-sm mb-3" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle fs-5 me-3"></i>
            <div>
                <strong class="d-block mb-1">{{ translate('Admin Access') }}</strong>
                <span>{{ translate('As an admin, you can still view and control your website but visitors will be
                    redirected to the maintenance page.') }}</span>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            {{-- Status Section --}}
            <div class="row align-items-center mb-4 pb-4 border-bottom">
                <div class="col-md-9">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-text-pink">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ translate('Maintenance Status') }}</h6>
                            <small class="text-muted">{{ translate('Enable or disable maintenance mode') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <x-switch name="maintenance[status]" id="maintenance-status" :showLabel="false" onLabel="Enabled"
                        offLabel="Disabled" :checked="@$settings->maintenance->status ?? false" />
                </div>
            </div>

            {{-- Icon Section --}}
            <div class="mb-4 pb-4 border-bottom">
                <label class="form-label text-muted small text-uppercase fw-semibold mb-3">
                    <i class="bi bi-image me-1"></i>{{ translate('Maintenance Icon') }}
                </label>
                <div class="d-flex align-items-center gap-3">
                    <div class="border rounded-3 p-3 bg-light">
                        <img id="image-preview-0" src="{{ asset(@$settings->maintenance->icon) }}"
                            alt="{{ translate('Icon') }}" height="60px" class="rounded-2">
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="maintenance[icon]" class="form-control image-input" data-id="0"
                            accept=".jpg,.jpeg,.png,.svg">
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-file-earmark-image me-1"></i>{{ translate('Supported formats: JPEG, JPG,
                            PNG, SVG') }}
                        </small>
                    </div>
                </div>
            </div>

            {{-- Title Section --}}
            <div class="mb-4">
                <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                    <i class="bi bi-type me-1"></i>{{ translate('Page Title') }}
                </label>
                <input name="maintenance[title]" class="form-control form-control-lg"
                    value="{{ @$settings->maintenance->title }}"
                    placeholder="{{ translate('e.g., We\'ll be back soon!') }}">
            </div>

            {{-- Description Section --}}
            <div>
                <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                    <i class="bi bi-text-paragraph me-1"></i>{{ translate('Description') }}
                </label>
                <textarea name="maintenance[body]" class="form-control" rows="6"
                    placeholder="{{ translate('Enter the message your visitors will see during maintenance...') }}">{{ @$settings->maintenance->body }}</textarea>
                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-lightbulb me-1"></i>{{ translate('Provide helpful information about when the site
                    will be back online') }}
                </small>
            </div>

        </div>
    </div>
</form>
@endsection
