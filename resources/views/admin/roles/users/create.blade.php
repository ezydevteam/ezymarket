@extends('admin.layouts.form')
@section('section', translate('Roles'))
@section('title', translate('New User'))
@section('description', translate('Create a new user account.'))
@section('back', route('admin.roles.users.index'))
@section('container', 'container-max-md')
@section('content')
<div class="card">
    <div class="card-body p-4">
        <form id="ezydev-form" action="{{ route('admin.roles.users.store') }}" method="POST">
            @csrf
            <div class="row g-4 mb-2">
                <div class="col-lg-12">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h5 class="mb-0">{{ translate('Seller') }}</h5>
                            <p class="text-muted mb-0">
                                <small>{{ translate('Enable seller privileges for this user') }}</small>
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <div class="ezydev-switch-wrapper-xl">
                                <input type="hidden" name="seller" value="0">
                                <input id="seller-status" class="ezydev-switch-input codebay-toggle-switch"
                                    type="checkbox" name="seller" value="1">
                                <label class="ezydev-switch-label" for="seller-status">
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
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}"
                            required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="lastname" class="form-control" value="{{ old('lastname') }}" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Username') }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fa fa-at"></i></span>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('E-mail Address') }} <span
                            class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Password') }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input id="randomPasswordInput" type="text" class="form-control" name="password" required>
                        <button class="btn btn-secondary btn-copy" type="button"
                            data-clipboard-target="#randomPasswordInput"><i class="far fa-clone"></i></button>
                        <button id="randomPasswordBtn" class="btn btn-secondary" type="button"><i
                                class="fa-solid fa-rotate me-2"></i>{{ translate('Generate') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts_libs')
<script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush
@endsection
