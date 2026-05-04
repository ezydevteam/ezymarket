@extends('installer::layouts.app')
@section('title', translate_text('Complete'))
@section('content')
    <div class="codebay-steps-body">
        <p class="codebay-form-info-text">
            {{ translate_text('Please enter your website and admin access details. Make sure to remember the admin access path.') }}
        </p>
        <form id="completeForm" action="{{ route('installer.complete') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Website Name') }}<span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                        <input type="text" name="website_name" value="{{ old('website_name') }}"
                            class="form-control form-control-md" placeholder="{{ translate_text('Website name') }}"
                            autocomplete="off" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Website URL') }}<span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-link"></i></span>
                        <input type="text" name="website_url" value="{{ old('website_url') ?? url('/') }}"
                            class="form-control form-control-md remove-spaces"
                            placeholder="{{ translate_text('Website URL') }}" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Admin Username') }}<span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="form-control form-control-md" placeholder="{{ translate_text('Username') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Admin Email') }}<span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-md"
                            placeholder="name@example.com" autocomplete="off" required>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Admin Password') }}<span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control form-control-md"
                            placeholder="{{ translate_text('Password') }}" autocomplete="off" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate_text('Confirm Password') }}<span
                            class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password_confirmation" class="form-control form-control-md"
                            placeholder="{{ translate_text('Confirm password') }}" autocomplete="off" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate_text('Admin panel access path') }}<span class="required">*</span>
                    <small class="text-muted">({{ translate_text('Letters and numbers only') }})</small>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe me-2"></i>{{ url('/') }}/</span>
                    <input id="adminPath" type="text" name="admin_path" value="{{ old('admin_path') ?? 'admin' }}"
                        class="form-control form-control-md remove-spaces" placeholder="{{ translate_text('admin') }}"
                        required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate_text('Editor panel access path') }}<span
                        class="required">*</span>
                    <small class="text-muted">({{ translate_text('Letters and numbers only') }})</small>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe me-2"></i>{{ url('/') }}/</span>
                    <input id="adminPath" type="text" name="editor_path"
                        value="{{ old('editor_path') ?? 'editor' }}" class="form-control form-control-md remove-spaces"
                        placeholder="{{ translate_text('editor') }}" required>
                </div>
            </div>
        </form>
        <div class="d-flex justify-content-between align-items-center">
            <form action="{{ route('installer.complete.back') }}" method="POST">
                @csrf
                <button class="btn btn-dark btn-md"><i
                        class="bi bi-arrow-left me-2"></i>{{ translate_text('Back') }}</button>
            </form>
            <button form="completeForm" class="btn btn-primary btn-md">{{ translate_text('Finish') }}<i
                    class="bi bi-arrow-right ms-2"></i></button>
        </div>
    </div>
@endsection


















