@extends('admin.layouts.form')
@section('section', translate('Help Center Articles'))
@section('title', translate('New Help Center Article'))
@section('back', route('admin.help.articles.index'))
@section('content')
<form id="ezydev-form" action="{{ route('admin.help.articles.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card p-2 mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Title') }} </label>
                        <input type="text" name="title" id="create_slug" class="form-control" value="{{ old('title') }}"
                            required autofocus />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Slug') }} </label>
                        <input type="text" name="slug" id="show_slug" class="form-control" value="{{ old('slug') }}"
                            required />
                    </div>
                    <div class="ckeditor-lg mb-0">
                        <label class="form-label">{{ translate('Content') }} </label>
                        <textarea name="content" rows="10" class="form-control ckeditor">{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-2 mb-3 h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Category') }} </label>
                        <select name="category" class="form-select selectpicker" data-live-search="true"
                            title="{{ translate('Choose') }}" required>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ translate('Description') }} </label>
                        <textarea name="description" rows="6" class="form-control"
                            placeholder="{{ translate('50 to 200 character at most') }}"
                            required>{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@push('top_scripts')
<script>
    "use strict";
    let GET_SLUG_URL = "{{ route('admin.help.articles.slug') }}";
</script>
@endpush
@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
@endpush
@include('admin.partials.ckeditor')
@endsection
