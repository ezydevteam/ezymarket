@extends('admin.layouts.form')
@section('section', translate('Mail Templates'))
@section('title', translate('Edit Mail Template'))
@section('description', translate($mailTemplate->name))
@section('back', route('admin.mail.templates.index'))
@section('content')
<div class="row g-4">
    {{-- Edit Form --}}
    <div class="col-lg-8">
        <form id="ezydev-form" action="{{ route('admin.mail.templates.update', $mailTemplate->id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-body p-4">
                    {{-- Subject Field --}}
                    <div class="mb-4">
                        <label class="form-label small text-uppercase fw-semibold mb-2">
                            <i class="bi bi-text-left me-1"></i>{{ translate('Email Subject') }}
                        </label>
                        <input type="text" name="subject" class="form-control form-control-lg"
                            value="{{ $mailTemplate->subject }}"
                            placeholder="{{ translate('Enter email subject line...') }}" required>
                    </div>

                    {{-- Content Field --}}
                    <div>
                        <label class="form-label small text-uppercase fw-semibold mb-2">
                            <i class="bi bi-file-text me-1"></i>{{ translate('Email Content') }}
                        </label>
                        <textarea name="content" class="ckeditor">{{ $mailTemplate->content }}</textarea>
                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-info-circle me-1"></i>{{ translate('Use the shortcodes from the sidebar to
                            personalize your email') }}
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Shortcodes Sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-code-slash fs-5"></i>
                    <h6 class="mb-0 fw-semibold">{{ translate('Available Shortcodes') }}</h6>
                </div>
                <p class="text-muted small mb-3">
                    {{ translate('Click to copy shortcodes and paste them into your email content') }}
                </p>

                @php
                $shortcodes = $mailTemplate->getFormattedShortcodes();
                @endphp

                @if(count($shortcodes) > 0)
                <div class="d-flex flex-column gap-2">
                    @foreach ($shortcodes as $index => $value)
                    <div class="position-relative">
                        <input id="shortcode_{{ $index }}" type="text"
                            class="form-control form-control-sm bg-light border" value="{{ $value }}" readonly>
                        <button
                            class="btn btn-sm bg-text-primary btn-copy position-absolute end-0 top-0 h-100 rounded-start-0"
                            type="button" data-clipboard-target="#shortcode_{{ $index }}"
                            title="{{ translate('Copy to clipboard') }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted fs-1"></i>
                    <p class="text-muted small mb-0 mt-2">{{ translate('No shortcodes available') }}</p>
                </div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <h6 class="mb-0 fw-semibold mb-3">
                    <i class="bi bi-toggle-off fs-5 me-1"></i>
                    {{ translate('Template Status') }}
                </h6>
                @if (!$mailTemplate->isDefault())
                <x-switch name="is_active" id="templateStatus" :showLabel="false" onLabel="Active" offLabel="Inactive"
                    :checked="$mailTemplate->isActive()" />
                @else
                <span class="badge bg-primary">
                    <i class="bi bi-shield-check me-1"></i>{{ translate('Default') }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
@push('scripts_libs')
<script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush
@include('admin.partials.ckeditor')
@endsection
