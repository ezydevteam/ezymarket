@extends('admin.layouts.full')
@section('section', translate('Blog'))
@section('title', translate('New Blog Article'))
@section('content')
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>{{ translate('Create Blog Article') }}</h5>
        <div class="d-flex align-items-center justify-content-between gap-3">
            <a href="{{ route('admin.blog.articles.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i><span class="d-none d-md-inline">{{ translate('Back') }}</span>
            </a>
            <button type="submit" form="createArticleForm" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i><span class="d-none d-sm-inline">{{ translate('Create') }}</span>
            </button>
        </div>
    </div>
    <form id="createArticleForm" action="{{ route('admin.blog.articles.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-file-text me-2"></i>
                            {{ translate('Article Content') }}
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
                                <input type="text" name="title" id="create_slug" class="form-control form-control-lg"
                                    value="{{ old('title') }}" placeholder="{{ translate('Enter article title...') }}"
                                    required autofocus />
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
                                <input type="text" name="slug" id="show_slug" class="form-control"
                                    value="{{ old('slug') }}" placeholder="{{ translate('article-url-slug') }}" required />
                            </div>
                        </div>

                        {{-- Description  --}}
                        <div class="ckeditor-lg">
                            <label class="form-label fw-medium">
                                {{ translate('Description ') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="body" rows="15" class="form-control ckeditor"
                                placeholder="{{ translate('Write your article content here...') }}"
                            >{{ old('body') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Featured Image --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-image me-2"></i>
                            {{ translate('Featured Image') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.input-image', [
                            'label' => translate('Article Image'),
                            'name' => 'image',
                            'required' => true,
                            'infoText' => translate('Supported: PNG, JPG, JPEG, WEBP.')
                        ])
                    </div>
                </div>

                {{-- Category --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-tag me-2"></i>
                            {{ translate('Category') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-medium">
                            {{ translate('Category') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select id="articleCategories" name="category" class="form-select form-select-md selectpicker"
                            data-live-search="true" title="{{ translate('Select category') }}" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            {{ translate('Choose the category for this article') }}
                        </div>
                    </div>
                </div>

                {{-- Short Description --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-text-paragraph me-2"></i>
                            {{ translate('SEO Meta Description') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-medium">
                            {{ translate('Short Description') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="short_description" rows="6" class="form-control"
                            placeholder="{{ translate('Brief summary of the article...') }}"
                            maxlength="200" required>{{ old('short_description') }}</textarea>
                        <div class="form-text">
                            {{ translate('50-200 characters at most') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end d-lg-none mt-4">
            <button type="submit" form="createArticleForm" class="btn btn-md btn-primary px-4">
                <i class="bi bi-check-lg me-2"></i>{{ translate('Create Article') }}
            </button>
        </div>
    </form>
    @include('admin.partials.ckeditor')
@endsection

@push('top_scripts')
    <script>
        "use strict";
        let GET_SLUG_URL = "{{ route('admin.blog.articles.slug') }}";
    </script>
@endpush
@push('styles_libs')
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
@endpush
@push('scripts_libs')
    <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
@endpush


















