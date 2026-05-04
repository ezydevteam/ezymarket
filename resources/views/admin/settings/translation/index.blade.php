@extends('admin.layouts.full')
@section('section', translate('Settings'))
@section('title', translate('Translation'))
@section('container', 'container-max-lg')
@section('content')
    <div class="card border-0 shadow-sm">
        {{-- Card Header --}}
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-translate text-primary fs-4"></i>
                <h4 class="mb-0 fw-semibold">{{ translate('Translation Configuration') }}</h4>
            </div>
            <p class="text-muted mb-0 mt-2">{{ translate('Configure your platform translation settings') }}</p>
        </div>

        {{-- Card Body --}}
        <div class="card-body p-4">
            <form id="translationConfigForm" action="{{ route('admin.settings.translation.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    {{-- Left Column: Primary Language --}}
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                            <i class="bi bi-globe2 me-1"></i>{{ translate('Primary Language') }} <span class="text-danger">*</span>
                        </label>
                        <select name="language[code]"
                            class="form-select form-select-lg selectpicker"
                            data-live-search="true"
                            required>
                            @foreach ($languages as $key => $value)
                                <option value="{{ $key }}" @selected(@$settings->language->code == $key)>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block">
                            {{ translate('Select the main language for your platform interface') }}
                        </small>
                    </div>

                    {{-- Right Column: Text Direction --}}
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                            <i class="bi bi-arrow-left-right me-1"></i>{{ translate('Text Direction') }} <span class="text-danger">*</span>
                        </label>
                        <select name="language[direction]" class="form-select form-select-lg selectpicker" required>
                            <option value="ltr" data-icon="bi bi-arrow-right" @selected(@$settings->language->direction == 'ltr')>
                                {{ translate('LTR (Left-to-Right)') }}
                            </option>
                            <option value="rtl" data-icon="bi bi-arrow-left" @selected(@$settings->language->direction == 'rtl')>
                                {{ translate('RTL (Right-to-Left)') }}
                            </option>
                        </select>
                        <small class="text-muted mt-2 d-block">
                            {{ translate('Most languages use LTR, while Arabic and Hebrew use RTL') }}
                        </small>
                    </div>
                </div>

                {{-- Bottom Buttons --}}
                <div class="d-flex gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-check-circle me-2"></i>{{ translate('Save Changes') }}
                    </button>
                    <a href="{{ route('admin.settings.translation.translates.index') }}"
                        class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-translate me-2"></i>{{ translate('Manage Translations') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection



















