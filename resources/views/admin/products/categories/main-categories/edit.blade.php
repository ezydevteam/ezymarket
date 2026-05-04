@extends('admin.layouts.app')
@section('section', translate('Product Categories'))
@section('title', translate('Edit Category'))
@section('header_title', translate('Edit Category') . ' - ' . $category->name)
@section('description', translate('Modify existing main category details and configuration'))
@section('back', route('admin.products.categories.index'))
@section('container', 'container-max-xxl')
@section('header_actions')
<button type="submit" form="editCategoryForm" class="btn btn-md btn-primary rounded-pill"><i
        class="bi bi-check2-circle me-2"></i>{{ translate('Save Changes') }}</button>
<x-dropdown icon="bi-three-dots-vertical" buttonClass="btn btn-md bg-secondary-subtle text-secondary rounded-pill">
    <x-dropdown.item
        href="{{ $category->view_link }}"
        target="_blank"
        icon="bi bi-eye"
        iconClass="me-2">
        {{ translate('Preview') }}
    </x-dropdown.item>
    <x-dropdown.item type="divider" />
    <x-dropdown.item
        type="button"
        icon="bi bi-trash"
        class="text-danger action-confirm"
        data-action="{{ route('admin.products.categories.destroy', $category->id) }}"
        data-method="DELETE"
        data-confirm="{{ translate('Are you sure want to delete this category? This action can not be undone.') }}">
        {{ translate('Delete') }}
    </x-dropdown.item>
</x-dropdown>
@endsection
@section('content')
<form id="editCategoryForm" class="ajax-form" action="{{ route('admin.products.categories.update', $category->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2 text-primary"></i>{{ translate('Basic Information') }}
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ translate('Category Name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="create_slug" class="form-control form-control-md"
                                placeholder="{{ translate('Enter category name') }}" value="{{ $category->name }}"
                                data-slug="{{ route('admin.products.categories.slug') }}" required autofocus />
                            <div class="form-text">
                                {{ translate('The display name for this category (e.g., Graphics, Themes)') }}
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ translate('URL Slug') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="slug" id="show_slug" class="form-control form-control-md"
                                placeholder="{{ translate('auto-generated-from-name') }}" value="{{ $category->slug }}"
                                required />
                            <div class="form-text">
                                {{ translate('URL-friendly version (auto-generated, but you can customize)') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-currency-dollar me-2 text-primary"></i>{{ translate('Buyer Fees') }}
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            @include('admin.partials.input-price', [
                            'label' => translate('Regular License Fee'),
                            'name' => 'regular_buyer_fee',
                            'value' => $category->regular_buyer_fee,
                            'required' => false,
                            ])
                            <div class="form-text">
                                {{ translate('Additional fee charged to buyers for regular license purchases') }}
                            </div>
                        </div>
                        <div class="col-lg-6">
                            @include('admin.partials.input-price', [
                            'label' => translate('Extended License Fee'),
                            'name' => 'extended_buyer_fee',
                            'value' => $category->extended_buyer_fee,
                            'required' => false,
                            ])
                            <div class="form-text">
                                {{ translate('Additional fee charged to buyers for extended license purchases') }}
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-center mt-3 mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>{{ translate('These fees are added on top of the base product price. Leave as 0.00 if no
                            additional fees apply.') }}</small>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-gear-wide-connected me-2 text-primary"></i>{{ translate('File Configuration') }}
                </div>
                <div class="card-body p-4">
                    {{-- Thumbnail Settings --}}
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-image me-2"></i>{{ translate('Thumbnail Settings') }}
                        </h6>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <label class="form-label">
                                    {{ translate('Preview Type') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="preview_type" class="form-select form-select-md selectpicker"
                                    placeholder="{{ translate('Select preview type') }}">
                                    @foreach (\App\Enums\Product\ProductCategoryPreviewType::options() as $value =>
                                    $label)
                                    <option value="{{ $value }}" {{ old('preview_type', $category->preview_type?->value)==$value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    {{ translate('Choose whether products in this category use images, videos, or audio
                                    previews') }}
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">
                                    {{ translate('Max. Preview File Size') }}
                                    <span class="badge bg-text-secondary ms-1">{{ translate('Optional') }}</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="preview_file_size" class="form-control form-control-md"
                                        placeholder="e.g. 5" value="{{ old('preview_file_size', $category->preview_file_size ? $category->preview_file_size / 1048576 : '') }}" step="0.01" min="0">
                                    <span class="input-group-text">{{ translate('MB') }}</span>
                                </div>
                                <div class="form-text">
                                    {{ translate('Set max file size for previews. Leave empty to use system default') }}
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    {{ translate('Max. Number of Gallery Images') }}
                                </label>
                                <div class="input-group">
                                    <input type="number" name="gallery_images_count"
                                        class="form-control form-control-md" placeholder="e.g. 5"
                                        value="{{ old('gallery_images_count', $category->gallery_images_count) }}" step="1" min="1">
                                    <span class="input-group-text">{{ translate('Images') }}</span>
                                </div>
                                <div class="form-text">
                                    {{ translate('Set max number of gallery images. Leave empty to disable') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Main File Settings --}}
                    <div class="mb-0">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-file-arrow-up me-2"></i>{{ translate('Main File Settings') }}
                        </h6>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <label class="form-label">
                                    {{ translate('Allowed Main File Types') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="main_file_types"
                                    class="form-control form-control-md tags-input" placeholder="e.g. ZIP, RAR, PDF"
                                    value="{{ old('main_file_types', $category->main_file_types) }}">
                                <div class="form-text">
                                    {{ translate('Type file extensions and press Enter. Examples: ZIP, RAR, PDF, MP4,
                                    MP3, PSD, AI.') }}
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">
                                    {{ translate('Max. Main File Size') }}
                                    <span class="badge bg-text-secondary ms-1">{{ translate('Optional') }}</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="main_file_size" class="form-control form-control-md"
                                        placeholder="e.g. 100" value="{{ old('main_file_size', $category->main_file_size ? $category->main_file_size / 1048576 : '') }}" step="0.01" min="0">
                                    <span class="input-group-text">{{ translate('MB') }}</span>
                                </div>
                                <div class="form-text">
                                    {{ translate('Set max file size for main product files. Leave empty to use system
                                    default') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-layers me-2 text-primary"></i>{{ translate('Category Options') }}
                    <span class="badge bg-light text-muted fw-normal ms-2 border">{{ translate('Optional') }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                        <i class="bi bi-lightbulb fs-5 me-3 mt-1"></i>
                        <div>
                            <strong>{{ translate('What are category options?') }}</strong>
                            <p class="mb-0 mt-2">{{ translate('Category options are custom fields that appear when users
                                create products in this category. For example, you might add "Resolution" or "File
                                Format" options that sellers must specify for their products.') }}</p>
                        </div>
                    </div>
                    <div id="category-options-list">
                        @foreach ($category->options ?? [] as $index => $option)
                            <div class="card mb-4 border shadow-none category-option-item-{{ $index }}">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light-subtle">
                                    <span class="fw-bold text-dark"><i class="bi bi-gear me-2"></i>{{ translate('Option') }} #{{ $loop->iteration }}</span>
                                    <button type="button" class="btn btn-sm text-danger delete-category-option" data-index="{{ $index }}" title="{{ translate('Remove') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <input type="hidden" name="category_options[{{ $index }}][id]" value="{{ $option['id'] }}">
                                    <div class="row g-4">
                                        <div class="col-lg-7">
                                            <label class="form-label fw-medium">
                                                {{ translate('Option Name') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="category_options[{{ $index }}][name]" class="form-control form-control-md"
                                                placeholder="{{ translate('e.g., Resolution, Format') }}" value="{{ $option['name'] }}" required />
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="form-label fw-medium">
                                                {{ translate('Input Type') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="category_options[{{ $index }}][type]" class="form-select form-select-md">
                                                @foreach (\App\Models\Product\ProductCategory::getTypeOptions() as $value => $label)
                                                    <option value="{{ $value }}" @selected($option['type'] == $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="category-options category-options-{{ $index }}">
                                                <label class="form-label fw-medium">
                                                    {{ translate('Option Values') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                @foreach ($option['options'] ?? [] as $valKey => $value)
                                                    <div class="category-option-{{ $valKey + 1 }} {{ !$loop->first ? 'mt-3' : '' }}">
                                                        <div class="input-group">
                                                            <input type="text" name="category_options[{{ $index }}][options][]" class="form-control form-control-md"
                                                                placeholder="{{ translate('e.g., 1920x1080') }}" value="{{ $value }}" required>
                                                            @if ($loop->first)
                                                                <button id="addCategoryOption-{{ $index }}" class="btn bg-primary-subtle text-primary add-option-value-btn" data-index="{{ $index }}" type="button" title="{{ translate('Add option value') }}">
                                                                    <i class="bi bi-plus-lg"></i>
                                                                </button>
                                                            @else
                                                                <button class="btn bg-danger-subtle text-danger px-3 remove-option-value" data-id="{{ $valKey + 1 }}" type="button" title="{{ translate('Remove this') }}">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="form-label fw-medium d-block">{{ translate('Set as Required Field') }}</label>
                                            <div class="ezydev-switch-wrapper-xl mt-2">
                                                <input type="checkbox" class="ezydev-switch-input"
                                                    name="category_options[{{ $index }}][required]"
                                                    id="switch-required-{{ $index }}"
                                                    value="1" {{ (isset($option['is_required']) && $option['is_required']) ? 'checked' : '' }}>
                                                <label class="ezydev-switch-label" for="switch-required-{{ $index }}">
                                                    <span class="ezydev-switch-slider">
                                                        <span class="ezydev-switch-button">
                                                            <span class="ezydev-switch-on">{{ translate('YES') }}</span>
                                                            <span class="ezydev-switch-off">{{ translate('NO') }}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-new-category-option" class="btn btn-md bg-primary-subtle text-primary btn-padding rounded-pill">
                        <i class="bi bi-plus-circle me-2"></i>{{ translate('Add New Option') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card sticky-top">
                <div class="card-header">
                    <i class="bi bi-globe2 me-2 text-primary"></i>{{ translate('SEO Settings') }}
                    <span class="badge bg-light text-muted fw-normal ms-2 border">{{ translate('Optional') }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">{{ translate('Meta Title') }}</label>
                            <input type="text" name="title" class="form-control form-control-md"
                                placeholder="{{ translate('Custom title for search engines') }}"
                                value="{{ old('title', $category->title) }}" maxlength="100" />
                            <div class="form-text">
                                {{ translate('Recommended length: 50-60 characters. Leave empty to use category name.')
                                }}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Meta Description') }}</label>
                            <textarea name="description" class="form-control" rows="6"
                                placeholder="{{ translate('Brief description for search engine results') }}"
                                maxlength="255">{{ old('description', $category->description) }}</textarea>
                            <div class="form-text">
                                {{ translate('Recommended length: 120-150 characters. Briefly describe what products are
                                in this category.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@push('top_scripts')
<script>
    "use strict";
    let categoryOptionIndex = {{ count($category->options ?? []) }};
</script>
@endpush
@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
@endpush
@push('scripts')
<script>
    $(document).ready(function () {
        // Add new category option
        $('#add-new-category-option').on('click', function () {
            const index = categoryOptionIndex++;
            const optionCard = `
                        <div class="card mb-4 border shadow-none category-option-item-${index}">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light-subtle">
                                <span class="fw-bold text-dark"><i class="bi bi-gear me-2"></i>{{ translate('Option') }} #${index + 1}</span>
                                <button type="button" class="btn btn-sm text-danger delete-category-option" data-index="${index}" title="{{ translate('Remove') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-lg-7">
                                        <label class="form-label fw-medium">
                                            {{ translate('Option Name') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="category_options[${index}][name]" class="form-control form-control-md"
                                            placeholder="{{ translate('e.g., Resolution, Format') }}" required />
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label fw-medium">
                                            {{ translate('Input Type') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_options[${index}][type]" class="form-select form-select-md">
                                            @foreach (\App\Models\Product\ProductCategory::getTypeOptions() as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="category-options category-options-${index}">
                                            <label class="form-label fw-medium">
                                                {{ translate('Option Values') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="category-option-1">
                                                <div class="input-group">
                                                    <input type="text" name="category_options[${index}][options][]" class="form-control form-control-md"
                                                        placeholder="{{ translate('e.g., 1920x1080') }}" required>
                                                    <button id="addCategoryOption-${index}" class="btn bg-primary-subtle text-primary add-option-value-btn" data-index="${index}" type="button" title="{{ translate('Add option value') }}">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label fw-medium d-block">{{ translate('Set as Required Field') }}</label>
                                        <div class="ezydev-switch-wrapper-xl mt-2">
                                            <input type="checkbox" class="ezydev-switch-input"
                                                name="category_options[${index}][required]"
                                                id="switch-required-${index}"
                                                value="1" checked>
                                            <label class="ezydev-switch-label" for="switch-required-${index}">
                                                <span class="ezydev-switch-slider">
                                                    <span class="ezydev-switch-button">
                                                        <span class="ezydev-switch-on">{{ translate('YES') }}</span>
                                                        <span class="ezydev-switch-off">{{ translate('NO') }}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
            $('#category-options-list').append(optionCard);
        });

        // Add option value to dynamically created category option
        $(document).on('click', '.add-option-value-btn', function () {
            const optionIndex = $(this).data('index');
            const categoryOptionsDiv = $(`.category-options-${optionIndex}`);
            const optionCount = categoryOptionsDiv.find('.input-group').length + 1;

            const valueHtml = `
                        <div class="category-option-${optionCount} mt-3">
                            <div class="input-group">
                                <input type="text" name="category_options[${optionIndex}][options][]" class="form-control form-control-md"
                                    placeholder="{{ translate('Option value') }}">
                                <button class="btn bg-danger-subtle text-danger px-3 remove-option-value" data-id="${optionCount}" type="button" title="{{ translate('Remove this') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

            categoryOptionsDiv.append(valueHtml);
        });

        // Remove option value
        $(document).on('click', '.remove-option-value', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const $optionValue = $(this).closest(`.category-option-${id}`);

            if (confirm("{{ translate('Are you sure you want to remove this option value?') }}")) {
                $optionValue.remove();
            }
        });

        // Delete entire category option card
        $(document).on('click', '.delete-category-option', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const index = $(this).data('index');
            const $optionCard = $(`.category-option-item-${index}`);

            if (confirm("{{ translate('Are you sure you want to delete this category option?') }}")) {
                $optionCard.remove();
            }
        });
    });
</script>
@endpush
@endsection
