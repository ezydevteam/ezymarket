@extends('admin.layouts.full')
@section('section', translate('Blog'))
@section('title', translate('Edit Blog Article'))
@section('content')
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>{{ translate('Edit Blog Article') }}</h5>
        <div class="d-flex align-items-center justify-content-between gap-3">
            <a href="{{ route('admin.blog.articles.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i><span class="d-none d-md-inline">{{ translate('Back') }}</span>
            </a>
            <a class="btn bg-text-primary" href="{{ $article->view_link }}" target="_blank">
                <i class="bi bi-eye me-1"></i><span class="d-none d-md-inline">{{ translate('Preview') }}</span>
            </a>
            <button type="submit" form="editArticleForm" class="btn btn-primary">
                <i class="bi bi-save2 me-1"></i><span class="d-none d-sm-inline">{{ translate('Update') }}</span>
            </button>
        </div>
    </div>
    <form id="editArticleForm"
        action="{{ route('admin.blog.articles.update', $article->id) }}"
        method="POST"
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
                                <input type="text"
                                    name="title"
                                    id="create_slug"
                                    class="form-control form-control-lg"
                                    value="{{ $article->title }}"
                                    placeholder="{{ translate('Enter article title...') }}"
                                    required
                                    autofocus />
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
                                <input type="text" name="slug" class="form-control"
                                    value="{{ $article->slug }}" placeholder="{{ translate('article-url-slug') }}" required />
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="ckeditor-lg">
                            <label class="form-label fw-medium">
                                {{ translate('Description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="body" rows="15" class="form-control ckeditor"
                                placeholder="{{ translate('Write your article content here...') }}"
                            >{{ $article->body }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Article Stats --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            {{ translate('Article Statistics') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class="bi bi-eye me-2"></i>{{ translate('Views') }}
                            </span>
                            <strong>{{ numberFormat($article->views ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class="bi bi-chat-dots me-2"></i>{{ translate('Comments') }}
                            </span>
                            <strong>{{ numberFormat($article->comments_count ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class="bi bi-calendar-plus me-2"></i>{{ translate('Created') }}
                            </span>
                            {{ dateFormat($article->created_at) }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="bi bi-calendar-check me-2"></i>{{ translate('Updated') }}
                            </span>
                            {{ dateFormat($article->updated_at) }}
                        </div>
                    </div>
                </div>

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
                            'value' => $article->image,
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
                                <option value="{{ $category->id }}"
                                    {{ $article->category->id == $category->id ? 'selected' : '' }}>
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
                            maxlength="200" required>{{ $article->short_description }}</textarea>
                        <div class="form-text">
                            {{ translate('50-200 characters at most') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end d-lg-none mt-4">
            <button type="submit" form="editArticleForm" class="btn btn-md btn-primary px-4">
                <i class="bi bi-save2 me-2"></i>{{ translate('Update Article') }}
            </button>
        </div>
    </form>
    @include('admin.partials.ckeditor')
@endsection

@push('styles_libs')
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
@endpush
@push('scripts_libs')
    <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
@endpush


















