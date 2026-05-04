@extends('admin.layouts.app')
@section('section', translate('Pages'))
@section('header_title', translate('Create New Page'))
@section('description', translate('Create a new page for your site'))
@section('back', route('admin.pages.index'))
@section('header_actions')
<button type="submit" form="createPageForm" class="btn btn-md btn-primary rounded-pill">
    <i class="bi bi-check2-circle me-2"></i>{{ translate('Create') }}
</button>
@endsection
@section('container', 'container-max-xxl')
@section('content')
<form id="createPageForm" class="ajax-form" action="{{ route('admin.pages.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            {{-- Page Details --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-file-text me-2"></i>{{ translate('Page Details') }}
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ translate('Page Title') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" id="create_slug" class="form-control form-control-md"
                                placeholder="{{ translate('Enter page title') }}" value="{{ old('title') }}"
                                data-slug="{{ route('admin.pages.slug') }}" autofocus />
                            <div class="form-text">
                                {{ translate('This will be displayed as the page heading') }}
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ translate('URL Slug') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="slug" id="show_slug" class="form-control form-control-md"
                                placeholder="{{ translate('auto-generated-from-title') }}" value="{{ old('slug') }}"
                                required />
                            <div class="form-text">
                                {{ translate('Auto-generated, but you can customize') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page Content --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>{{ translate('Page Content') }}
                </div>
                <div class="card-body p-4">
                    <div class="ckeditor-lg">
                        <label class="form-label">
                            {{ translate('Content') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="content" rows="15" class="form-control ckeditor"
                            placeholder="{{ translate('Write your page content here...') }}">{{ old('content') }}</textarea>
                        <div class="form-text">
                            {{ translate('Main content of the page. You can use rich text formatting.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO Settings --}}
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-search me-2"></i>{{ translate('SEO') }}
                    <span class="badge bg-text-secondary ms-2">{{ translate('Optional') }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ translate('Meta Description') }}</label>
                            <textarea name="description" class="form-control" rows="6"
                                placeholder="{{ translate('Brief description for search engines and social media') }}"
                                maxlength="200">{{ old('description') }}</textarea>
                            <div class="form-text">
                                {{ translate('Recommended length: 50-200 characters for optimal SEO') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Page Layout --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-layout-sidebar me-2"></i>{{ translate('Page Layout') }}
                </div>
                <div class="card-body p-4">
                    <label class="form-label">
                        {{ translate('Layout Type') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select name="layout" class="form-select form-select-md selectpicker"
                        data-placeholder="{{ translate('Select layout type') }}" required>
                        @foreach (\App\Enums\Page\PageLayout::cases() as $layout)
                        <option value="{{ $layout->value }}" {{ old('layout', 'full' )==$layout->value ? 'selected' : ''
                            }}>
                            {{ $layout->label() }}
                        </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        {{ translate('Choose how this page will be displayed') }}
                    </div>
                </div>
            </div>

            {{-- Page Header --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-display me-2"></i>{{ translate('Page Header') }}
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">
                            {{ translate('Header Style') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="header[style]" class="form-select form-select-md selectpicker"
                            data-placeholder="{{ translate('Select header style') }}"
                            data-conditional-toggle="#header-options-wrapper"
                            data-conditional-value="{{ \App\Enums\Page\PageHeaderStyle::NO_HEADER->value }}"
                            data-conditional-logic="not-equal">
                            @foreach (\App\Enums\Page\PageHeaderStyle::cases() as $style)
                            <option value="{{ $style->value }}" {{ old('header.style', 'style-1' )==$style->value ?
                                'selected' : '' }}>
                                {{ $style->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 d-none" id="header-options-wrapper">
                        <div class="col-6">
                            <label class="form-label d-block">{{ translate('Show Breadcrumb') }}</label>
                            <div class="ezydev-switch-wrapper">
                                <input type="checkbox" class="ezydev-switch-input" name="header[breadcrumb]"
                                    id="header-breadcrumb" value="1" {{ old('header.breadcrumb', '1' ) ? 'checked' : ''
                                    }}>
                                <label class="ezydev-switch-label" for="header-breadcrumb">
                                    <span class="ezydev-switch-slider">
                                        <span class="ezydev-switch-button">
                                            <span class="ezydev-switch-on">{{ translate('Yes') }}</span>
                                            <span class="ezydev-switch-off">{{ translate('No') }}</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label d-block">{{ translate('Show Description') }}</label>
                            <div class="ezydev-switch-wrapper">
                                <input type="checkbox" class="ezydev-switch-input" name="header[description]"
                                    id="header-description" value="1" {{ old('header.description') ? 'checked' : '' }}>
                                <label class="ezydev-switch-label" for="header-description">
                                    <span class="ezydev-switch-slider">
                                        <span class="ezydev-switch-button">
                                            <span class="ezydev-switch-on">{{ translate('Yes') }}</span>
                                            <span class="ezydev-switch-off">{{ translate('No') }}</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview Image --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-image me-2"></i>{{ translate('Preview Image') }}
                    <span class="badge bg-text-secondary ms-2">{{ translate('Optional') }}</span>
                </div>
                <div class="card-body p-4">
                    @include('admin.partials.input-image', [
                    'label' => translate('Featured Image'),
                    'name' => 'preview_image',
                    'value' => old('preview_image'),
                    'infoText' => translate('Supported: PNG, JPG, JPEG, GIF, WEBP.')
                    ])
                </div>
            </div>
        </div>
    </div>
</form>
@include('admin.partials.ckeditor')
@endsection
