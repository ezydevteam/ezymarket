@extends('admin.layouts.full')
@section('section', translate('Appearance'))
@section('title', translate('Themes'))
@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <h4 class="fw-bold mb-0">{{ translate('Themes') }}</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fa fa-upload me-2"></i>{{ translate('Upload') }}
                </button>
            </div>
        </div>
        @foreach ($themes as $theme)
            <div class="col-lg-6 col-xl-4">
                <div class="card theme-card">
                    @if ($theme->isActive())
                        <span class="badge bg-success theme-card-active-badge shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ translate('Active') }}</span>
                    @endif
                    <img src="{{ $theme->thumbnail_url }}" class="card-img-top theme-card-image border-1 border-bottom"
                        alt="{{ $theme->name }}">
                    <div class="card-body">
                        <h5 class="card-title theme-card-title">
                            {{ $theme->name }}
                            <span>v{{ $theme->version }}</span>
                        </h5>
                        <p class="card-text theme-card-text">{{ $theme->description }}</p>
                        <div class="row g-2">
                            <div class="col">
                                <form action="{{ route('admin.appearance.themes.active', $theme->id) }}" method="POST">
                                    @csrf
                                    <button
                                        class="btn btn-primary btn-md w-100 action-confirm {{ $theme->isActive() ? 'disabled' : '' }}"><i
                                            class="bi bi-check2-circle me-2"></i>{{ translate('Make Active') }}</button>
                                </form>
                            </div>
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-md w-100 dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li>
                                            <a class="dropdown-item text-primary"
                                                href="{{ route('admin.appearance.themes.settings.index', $theme->id) }}">
                                                <i class="bi bi-gear me-2"></i>
                                                {{ translate('Settings') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.appearance.themes.custom-css.index', $theme->id) }}">
                                                <i class="bi bi-code-slash me-2"></i>
                                                {{ translate('Custom CSS') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <x-modal id="uploadModal" :title="translate('Upload a theme')" icon="bi-upload" bodyClass="p-4">
        <div class="note note-warning">
            <h5 class="mb-2 text-danger"><strong><i class="bi bi-exclamation-circle me-2"></i>
                {{ translate('Important!') }}</strong></h5>
            <ul class="mb-0">
                <li class="mb-1">
                    {{ translate('Make sure you are uploading the correct files.') }}
                </li>
                <li class="mb-0">
                    {{ translate('Before uploading a new theme make sure to take a backup of your website files and database.') }}
                </li>
            </ul>
        </div>
        <form id="uploadModalForm" action="{{ route('admin.appearance.themes.upload') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ translate('Theme Purchase Code') }} </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="text" name="purchase_code" class="form-control form-control-lg"
                        placeholder="{{ translate('Enter purchase code here') }}" value="{{ old('purchase_code') }}"
                        required>
                </div>
            </div>
            <div class="mb-0">
                <label class="form-label">{{ translate('Theme Files (Zip)') }} </label>
                <input type="file" name="theme_files" class="form-control form-control-lg" accept=".zip"
                    required>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-cancel btn-lg flex-fill" data-bs-dismiss="modal"
                aria-label="Close"><i class="bi bi-x-circle me-2"></i>{{ translate('Close') }}</button>
            <button type="submit" form="uploadModalForm" class="btn btn-primary btn-lg flex-fill"><i
                    class="bi bi-upload me-2"></i>{{ translate('Upload') }}</button>
        </x-slot>
    </x-modal>
@endsection
