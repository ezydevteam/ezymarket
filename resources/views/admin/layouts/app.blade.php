<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    @section('admin_head')
    @include('admin.includes.head')
    @show

    @section('admin_styles')
    @include('admin.includes.styles')
    @show

    @stack('head_content')
</head>

<body class="@yield('body_class')">

    @section('body_content')
    @include('admin.includes.sidebar')
    <div class="ezydev-main-wrapper">
        @include('admin.includes.navbar')
        <div class="container @yield('container')">
            <main class="ezydev-main-content mt-4">
                @hasSection('header_title')
                <div class="row g-3 justify-content-between align-items-center mb-4">
                    <div class="col">

                        @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                        @endif

                        <h1 class="h3 mb-0 fw-bold">@yield('header_title')</h1>

                        @hasSection('description')
                        <p class="text-gray-600 small mb-0">@yield('description')</p>
                        @endif

                    </div>
                    <div class="col-auto">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            @hasSection('back')
                            <a href="@yield('back')" class="btn btn-md btn-soft btn-padding fw-medium rounded-pill">
                                <i class="bi bi-arrow-left me-1"></i> {{ translate("Back") }}
                            </a>
                            @endif

                            @hasSection('save')
                            <button form="ezydev-regular-form"
                                class="btn btn-md btn-padding fw-medium rounded-pill @yield('save_class', 'btn-primary')">
                                <i class="bi bi-save me-1"></i>
                                {{ translate($__env->yieldContent('save', 'Save')) }}
                            </button>
                            @endif

                            @hasSection('modal_button')
                            <button type="button" class="btn btn-md btn-primary btn-padding fw-medium rounded-pill"
                                data-bs-toggle="modal" data-bs-target="@yield('modal_target')"
                                data-action="{{ $__env->yieldContent('modal_action', '#') }}">
                                <i class="bi bi-plus-lg me-1"></i>
                                {{ translate($__env->yieldContent('modal_button', 'Add New')) }}
                            </button>
                            @endif

                            @yield('header_actions')
                        </div>
                    </div>
                </div>
                @endif

                <div class="row g-4">
                    <div class="col">
                        @yield('content')
                    </div>
                </div>
            </main>

        </div>
    </div>
    @show

    @section('admin_scripts')
    @include('admin.includes.scripts')
    @show

    @stack('footer_content')
</body>

</html>
