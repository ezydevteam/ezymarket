@extends('themes.main.layouts.app')
@section('noindex', true)
@section('theme_header', '')
@section('theme_footer', '')

@push('styles_libs')
    <link rel="stylesheet" href="{{ asset('vendor/libs/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatable/css/datatables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/datatable/css/buttons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}" />
@endpush
@push('head_content')
    <link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/userpanel.css') }}">
@endpush

@section('body_content')
    <div class="userpanel">
        @themeInclude('userpanel.includes.sidebar')

        <div class="userpanel-body">
            @themeInclude('userpanel.includes.header')
            <div class="userpanel-container @yield('container')">
                @hasSection('header_title')
                    <div class="mb-4">
                        <div class="row g-3 justify-content-between align-items-center">
                            <div class="col">
                                @hasSection('breadcrumbs')
                                    @yield('breadcrumbs')
                                @endif
                                <h1 class="h3 mb-1 fw-semibold">@yield('header_title')</h1>
                                @hasSection('description')
                                    <p class="text-muted small mb-0">@yield('description')</p>
                                @endif
                            </div>
                            @hasSection('header_actions')
                                <div class="col-auto">
                                    @yield('header_actions')
                                </div>
                            @endif
                            @hasSection('back')
                                <div class="col-auto">
                                    <a href="@yield('back')" class="btn btn-soft btn-padding fw-medium">
                                        <i class="bi bi-arrow-left me-1"></i> {{ translate('Back') }}
                                    </a>
                                </div>
                            @endif
                            @hasSection('create')
                                <div class="col-auto">
                                    <a href="@yield('create')" class="btn btn-secondary btn-padding fw-medium">
                                        <i class="bi bi-plus-lg me-1"></i> {{ translate('Create') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
@endsection

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/datatables.jq.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/datatables.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/vfs-fonts.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/geochart/geochart.min.js') }}"></script>

    @themeInclude('userpanel.partials.ckeditor')
@endpush

@push('footer_content')
    <script src="{{ theme_assets_with_version('assets/js/charts.js') }}"></script>
    <script src="{{ theme_assets_with_version('assets/js/userpanel.js') }}"></script>
@endpush
