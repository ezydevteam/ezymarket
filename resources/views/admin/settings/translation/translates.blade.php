@extends('admin.layouts.form')
@section('section', translate('Settings'))
@section('title', translate(':language_name Translates', ['language_name' => $language]))
@section('description', translate('Translate your site into different languages to reach a broader audience.'))
@section('back', route('admin.settings.translation.index'))
@section('content')
{{-- Important Info Alert --}}
<div class="alert alert-warning mb-4">
    <div class="d-flex align-items-start me-4">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <h6 class="mb-2">
                <strong>{{ translate('Important Translation Guidelines!') }}</strong>
            </h6>
            <ul class="mb-2 ps-3">
                <li class="mb-2">
                    {!! translate(
                    'Do not translate dynamic variables and placeholders like :words - these are automatically replaced
                    by the system',
                    ['words' => '<code>:value</code>, <code>:seconds</code>, <code>:min</code>, <code>:max</code>,
                    <code>{username}</code>, <code>[URL]</code>'],
                    ) !!}
                </li>
                <li class="mb-2">
                    <strong>{{ translate('Always clear the cache after saving translations') }}</strong> {{
                    translate('to see your changes take effect on the live site') }}
                </li>
                <li>
                    {{ translate('Use the search feature to quickly find specific strings you want to translate') }}
                </li>
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

{{-- Search & Filter --}}
<div class="card mb-4">
    <div class="card-header bg-primary bg-opacity-10">
        <h6 class="mb-0">
            <i class="fa fa-search me-2"></i>{{ translate('Search Translations') }}
        </h6>
    </div>
    <div class="card-body p-3">
        <form action="{{ url()->current() }}" method="GET">
            <div class="row g-3">
                <div class="col-12">
                    <div class="input-group input-group-lg">
                        <input type="text" name="search" class="form-control form-control-lg"
                            placeholder="{{ translate('Search by key or translated text...') }}"
                            value="{{ request('search') ?? '' }}">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="fa fa-search me-2"></i>{{ translate('Search') }}
                        </button>
                        @if(request('search'))
                        <a href="{{ url()->current() }}" class="btn btn-secondary px-4">
                            <i class="fa fa-times me-2"></i>{{ translate('Clear') }}
                        </a>
                        @endif
                    </div>
                    @if(request('search'))
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fa fa-info-circle me-1"></i>
                            {{ translate('Showing results for') }}: <strong>"{{ request('search') }}"</strong>
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Translation Form --}}
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-language me-2"></i>{{ translate('Translation Strings') }}
            </h5>
            <span class="badge bg-white text-primary">
                {{ $translates->total() }} {{ translate('strings') }}
            </span>
        </div>
    </div>
    <div class="card-body p-4">
        @if($translates->count() > 0)
        <div class="alert alert-info mb-4">
            <i class="fa fa-info-circle me-2"></i>
            {{ translate('Edit the translations below. The left side shows the original key, and the right side is your
            custom translation. Text areas will auto-expand as you type.') }}
        </div>

        <form id="ezydev-form" action="{{ route('admin.settings.translation.translates.update') }}" method="POST">
            @csrf

            <div class="translation-list">
                @foreach ($translates as $index => $translate)
                <div class="vironeer-translate-box mb-3">
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                {{-- Original Key (Editable for development) --}}
                                <div class="col-lg-5">
                                    <label class="form-label text-muted small mb-2">
                                        <i class="fa fa-key me-1"></i>{{ translate('Original Key') }}
                                        <span class="badge bg-warning text-dark ms-2">{{ translate('DEV ONLY') }}</span>
                                    </label>
                                    <textarea name="translates[{{ $translate->id }}][key]"
                                        class="vironeer-translate-key translate-fields form-control"
                                        rows="1">{{ $translate->key }}</textarea>
                                </div>

                                {{-- Arrow Icon --}}
                                <div class="col-lg-2 text-center d-none d-lg-block">
                                    <i class="fas fa-arrow-right fa-2x text-primary"></i>
                                </div>

                                {{-- Translation --}}
                                <div class="col-lg-5">
                                    <label class="form-label text-primary small mb-2">
                                        <i class="fa fa-language me-1"></i>{{ translate('Your Translation') }}
                                    </label>
                                    <textarea name="translates[{{ $translate->id }}][value]"
                                        class="translate-fields form-control" rows="1"
                                        placeholder="{{ translate('Enter translation...') }}">{{ $translate->value }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </form>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $translates->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fa fa-search fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">
                {{ translate('No translation strings found matching your search criteria') }}
            </p>
            <a href="{{ url()->current() }}" class="btn btn-primary mt-3">
                <i class="fa fa-arrow-left me-2"></i>{{ translate('View All Translations') }}
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Translation Tips --}}
<div class="card mt-4 border-info">
    <div class="card-header bg-info bg-opacity-10">
        <h6 class="mb-0 text-info">
            <i class="fa fa-lightbulb me-2"></i>{{ translate('Translation Best Practices') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="d-flex align-items-start">
                    <div class="codebay-feature-icon me-3">
                        <i class="fa fa-code"></i>
                    </div>
                    <div>
                        <h6 class="codebay-feature-title">{{ translate('Preserve Variables') }}</h6>
                        <p class="codebay-feature-description mb-0">
                            {{ translate('Keep placeholders like :name, {value}, [URL] exactly as they are - they\'re
                            replaced dynamically.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-start">
                    <div class="codebay-feature-icon me-3">
                        <i class="fa fa-align-left"></i>
                    </div>
                    <div>
                        <h6 class="codebay-feature-title">{{ translate('Match Context') }}</h6>
                        <p class="codebay-feature-description mb-0">
                            {{ translate('Maintain the tone and context of the original text while translating to your
                            language.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-start">
                    <div class="codebay-feature-icon me-3">
                        <i class="fa fa-save"></i>
                    </div>
                    <div>
                        <h6 class="codebay-feature-title">{{ translate('Save Regularly') }}</h6>
                        <p class="codebay-feature-description mb-0">
                            {{ translate('Save your translations frequently to avoid losing work, especially when
                            editing many strings.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-start">
                    <div class="codebay-feature-icon me-3">
                        <i class="fa fa-sync"></i>
                    </div>
                    <div>
                        <h6 class="codebay-feature-title">{{ translate('Clear Cache') }}</h6>
                        <p class="codebay-feature-description mb-0">
                            {{ translate('Always clear cache after saving to see your translations on the live site
                            immediately.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts_libs')
<script src="{{ asset('vendor/libs/autosize/autosize.min.js') }}"></script>
@endpush
@push('scripts')
<script>
    'use strict';
    autosize($('textarea.translate-fields'));
</script>
@endpush
@endsection
