@extends('admin.layouts.app')
@section('section', translate('Pages'))
@section('header_title', translate('Edit Page'))
@section('description', translate('Edit page ' . $page->title))
@section('back', route('admin.pages.index'))
@section('container', 'container-max-xxl')
@section('header_actions')
<button type="submit" form="editPageForm" class="btn btn-md btn-primary rounded-pill">
    <i class="bi bi-check2-circle me-2"></i>{{ translate('Update') }}
</button>
<x-dropdown icon="bi-three-dots-vertical" buttonClass="btn btn-md bg-secondary-subtle text-secondary rounded-pill">
    <x-dropdown.item href="{{ $page->link }}" target="_blank" icon="bi bi-eye">
        {{ translate('Preview') }}
    </x-dropdown.item>
    <x-dropdown.item type="divider" />
    <x-dropdown.item type="button" data-action="{{ route('admin.pages.destroy', $page->id) }}"
        icon="bi bi-trash" class="text-danger action-confirm"
        data-method="DELETE" data-text="{{ translate('Are you sure you want to delete this page?') }}">
        {{ translate('Delete') }}
    </x-dropdown.item>
</x-dropdown>
@endsection
@section('content')
<form id="editPageForm" class="ajax-form" action="{{ route('admin.pages.update', $page->id) }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        {{ translate('Page Content') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    {{-- Title --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            {{ translate('Title') }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-type-h1"></i>
                            </span>
                            <input type="text" name="title" class="form-control form-control-lg" id="create_slug"
                                value="{{ $page->title }}" placeholder="{{ translate('Enter page title...') }}"
                                data-slug="{{ route('admin.pages.slug') }}" autofocus />
                        </div>
                    </div>

                    {{-- Slug --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            {{ translate('Slug') }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-globe"></i>
                            </span>
                            <input type="text" name="slug" class="form-control" id="show_slug" value="{{ $page->slug }}"
                                placeholder="{{ translate('page-url-slug') }}" required />
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="ckeditor-lg">
                        <label class="form-label fw-medium">
                            {{ translate('Content') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="content" rows="15" class="form-control ckeditor"
                            placeholder="{{ translate('Write your page content here...') }}">{!! sanitizeRichText($page->content) !!}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Page Stats --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-line me-2"></i>
                        {{ translate('Page Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">
                            <i class="bi bi-eye me-2"></i>{{ translate('Total Views') }}
                        </span>
                        <strong>{{ numberFormat($page->total_views) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">
                            <i class="bi bi-calendar-plus me-2"></i>{{ translate('Created') }}
                        </span>
                        {{ dateFormat($page->created_at) }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <i class="bi bi-calendar-check me-2"></i>{{ translate('Updated') }}
                        </span>
                        {{ dateFormat($page->updated_at) }}
                    </div>
                </div>
            </div>

            {{-- Page Layout --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-layout-sidebar me-2"></i>
                        {{ translate('Page Layout') }}
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-medium">
                        {{ translate('Layout Type') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select name="layout" class="form-select form-select-md selectpicker"
                        data-placeholder="{{ translate('Select layout type') }}" required>
                        @foreach (\App\Enums\Page\PageLayout::cases() as $layout)
                        <option value="{{ $layout->value }}" {{ $page->getLayout()->value == $layout->value ? 'selected'
                            : '' }}>
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
                    <h6 class="mb-0">
                        <i class="bi bi-display me-2"></i>
                        {{ translate('Page Header') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            {{ translate('Header Style') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="header[style]" class="form-select form-select-md selectpicker"
                            data-placeholder="{{ translate('Select header style') }}"
                            data-conditional-toggle="#header-options-wrapper"
                            data-conditional-value="{{ \App\Enums\Page\PageHeaderStyle::NO_HEADER->value }}"
                            data-conditional-logic="not-equal">
                            @foreach (\App\Enums\Page\PageHeaderStyle::cases() as $style)
                            <option value="{{ $style->value }}" {{ $page->getHeaderStyle()->value == $style->value ?
                                'selected' : '' }}>
                                {{ $style->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 {{ $page->getHeaderStyle()->value == \App\Enums\Page\PageHeaderStyle::NO_HEADER->value ? 'd-none' : '' }}"
                        id="header-options-wrapper">
                        <div class="col-6">
                            <label class="form-label fw-medium d-block">{{ translate('Show Breadcrumb') }}</label>
                            <div class="ezydev-switch-wrapper">
                                <input type="checkbox" class="ezydev-switch-input" name="header[breadcrumb]"
                                    id="header-breadcrumb" value="1" {{ $page->showBreadcrumb() ? 'checked' : '' }}>
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
                            <label class="form-label fw-medium d-block">{{ translate('Show Description') }}</label>
                            <div class="ezydev-switch-wrapper">
                                <input type="checkbox" class="ezydev-switch-input" name="header[description]"
                                    id="header-description" value="1" {{ $page->showDescription() ? 'checked' : '' }}>
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
                    <h6 class="mb-0">
                        <i class="bi bi-image me-2"></i>
                        {{ translate('Preview Image') }}
                    </h6>
                </div>
                <div class="card-body">
                    @include('admin.partials.input-image', [
                    'label' => translate('Featured Image'),
                    'name' => 'preview_image',
                    'value' => $page->preview_image,
                    'infoText' => translate('Supported: PNG, JPG, JPEG, GIF, WEBP.')
                    ])
                </div>
            </div>

            {{-- Meta Description --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-search me-2"></i>
                        {{ translate('SEO') }}
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-medium">
                        {{ translate('Meta Description') }}
                        <span class="text-muted small">({{ translate('Optional') }})</span>
                    </label>
                    <textarea name="description" rows="6" class="form-control"
                        placeholder="{{ translate('Brief description for search engines and social media...') }}"
                        maxlength="200">{{ $page->description }}</textarea>
                    <div class="form-text">
                        {{ translate('50-200 characters recommended for optimal SEO') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@include('admin.partials.ckeditor')
@endsection
