@extends('installer::layouts.app')
@section('title', translate_text('Database'))
@section('content')
    <div class="codebay-steps-body">
        <p class="codebay-form-info-text">
            {{ translate_text('Configure your MySQL database connection. Use the credentials provided by your hosting provider or local setup. Avoid special characters in database names.') }}
        </p>
        <form action="{{ route('installer.database.validate') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate_text('Database Host') }} <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="text" name="db_host" class="form-control form-control-md remove-spaces"
                                placeholder="{{ translate_text('Usually localhost or 127.0.0.1') }}"
                                value="{{ old('db_host') ?? 'localhost' }}"
                                required>
                            <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate_text('Database Name') }} <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="text" name="db_name" class="form-control form-control-md remove-spaces"
                                placeholder="{{ translate_text('Name of your database') }}"
                                value="{{ old('db_name') }}"
                                autocomplete="off" required>
                            <span class="input-group-text"><i class="bi bi-database"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate_text('Database Username') }} <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="text" name="db_user" class="form-control form-control-md remove-spaces"
                                placeholder="{{ translate_text('MySQL user with access rights') }}"
                                value="{{ old('db_user') }}"
                                autocomplete="off" required>
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate_text('Database Password') }}</label>
                        <div class="input-group">
                            <input type="password" name="db_pass" class="form-control form-control-md remove-spaces"
                                placeholder="{{ translate_text('Leave empty if none') }}"
                                autocomplete="off">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary btn-md">
                    {{ translate_text('Verify Connection') }}
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
@endsection


















