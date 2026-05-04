@extends('admin.layouts.form')
@section('title', translate('Edit Advertisement'))
@section('back', route('admin.ads.index'))
@section('container', 'container-max-lg')
@section('content')
<form id="ezydev-form" action="{{ route('admin.ads.update', $advertisement->id) }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-9">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-badge-ad text-primary fs-5"></i>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ translate($advertisement->position) }}</h6>
                            @if($advertisement->size)
                            <small class="text-muted">{{ $advertisement->size }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <x-switch name="is_active" id="ad-status" :showLabel="false" onLabel="Active" offLabel="Inactive"
                        :checked="$advertisement->isActive()" />
                </div>
            </div>

            <div>
                <label class="form-label text-muted small text-uppercase fw-semibold mb-2">
                    <i class="bi bi-code-square me-1"></i>{{ translate('Ad Code') }}
                </label>
                <textarea id="html-editor" name="ad_code" class="form-control"
                    rows="15">{{ hideInDemo($advertisement->ad_code) }}</textarea>
                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-info-circle me-1"></i>{{ translate('Paste your advertisement HTML/JavaScript code
                    here') }}
                </small>
            </div>
        </div>
    </div>
</form>
@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/codemirror/codemirror.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/codemirror/monokai.min.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/codemirror/codemirror.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/htmlmixed.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/xml.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/javascript.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/sublime.min.js') }}"></script>
@endpush
@endsection
