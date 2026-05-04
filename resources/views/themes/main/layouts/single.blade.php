@extends('themes.main.layouts.app')
@section('body_class', 'layout-single')

@section('body_content')
    @php
        $pageSettings = themePageSettings($page ?? null);
        $context = $pageSettings->context ?? '';
        $sidebarType = $pageSettings->sidebar_type ?? 'page-sidebar';
        $headerStyle = View::hasSection('header_style') ?
        $__env->yieldContent('header_style')
        : ($pageSettings->header_style ?? 'minimal');
        $sidebarLayout = $pageSettings->sidebar_layout ?? 'no_sidebar';
        $hasSidebar = $sidebarLayout !== 'no_sidebar';
        $leftSidebar = $sidebarLayout === 'left_sidebar';
        $showBreadcrumbs = $pageSettings->show_breadcrumbs ?? true;
        $showDescription = $pageSettings->show_description ?? true;

        $headerClasses = $pageSettings->header_classes ?? 'page-header header-minimal';
        $containerClass = View::hasSection('container')
        ? $__env->yieldContent('container')
        : ($pageSettings->container_class ?? 'container container-default');
    @endphp

    @if($headerStyle !== 'no_header')
    <header class="{{ $headerClasses }}">
        <div class="{{ $containerClass }}">
            @if($headerStyle === 'split')
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    @if($showBreadcrumbs)
                    @yield('breadcrumbs')
                    @endif
                    <h1 class="page-header-title fs-2">
                        @yield('header_title')
                    </h1>
                    @if($showDescription && View::hasSection('description'))
                    <p class="text-gray-700 fs-14 mb-0 mt-2">@yield('description')</p>
                    @endif
                </div>
                <div class="col-lg-5 text-end">
                    <div class="search-options">
                        @themeInclude('partials.search.search-form', [
                        'id' => 'pageHeaderSearch',
                        'wrapper_class' => 'search-input',
                        'input_class' => 'form-control form-control-lg rounded-end-0 fs-16',
                        'btn_class' => 'btn btn-lg btn-primary rounded-start-0 fs-16',
                        'btn_icon' => false,
                        'btn_text' => translate('Search'),
                        'show_backdrop' => true
                        ])
                    </div>
                </div>
            </div>
            @elseif($headerStyle === 'centered')
            <div class="d-flex flex-column align-items-center text-center">
                @if($showBreadcrumbs)
                @yield('breadcrumbs')
                @endif
                <h1 class="page-header-title fs-2 w-100">@yield('header_title')</h1>
                @if($showDescription && View::hasSection('description'))
                <p class="text-gray-700 fs-14 mb-0 mt-2">@yield('description')</p>
                @endif
            </div>
            @elseif($headerStyle === 'gradient')
            <div class="d-flex flex-column align-items-center text-center">
                @if($showBreadcrumbs)
                @yield('breadcrumbs')
                @endif
                <h1 class="page-header-title display-5 fw-bolder text-uppercase">
                    @yield('header_title')
                </h1>
                @if($showDescription && View::hasSection('description'))
                <p class="opacity-75 lead mt-2">@yield('description')</p>
                @endif
            </div>
            @else {{-- minimal --}}
            <div class="py-0">
                @if($showBreadcrumbs)
                @yield('breadcrumbs')
                @endif
                <h1 class="page-header-title fs-3 mb-0">
                    @yield('header_title')
                </h1>
                @if($showDescription && View::hasSection('description'))
                <p class="text-gray-700 fs-14 mb-0 mt-1">@yield('description')</p>
                @endif
            </div>
            @endif
        </div>
    </header>
    @endif

    <main class="layout-single-main py-4">
        <div class="{{ $containerClass }}">
            <div class="row g-4">
                <div class="{{ $hasSidebar ? 'col-lg-8 col-xxl-9' : 'col-12' }} {{ $leftSidebar ? 'order-lg-2' : '' }}">
                    <div class="single-content">
                        @yield('main')
                    </div>
                </div>

                @if($hasSidebar)
                <div class="col-lg-4 col-xxl-3 {{ $leftSidebar ? 'order-lg-1' : '' }}">
                    <x-widget name="{{ $sidebarType }}" />
                </div>
                @endif
            </div>
        </div>
    </main>
@endsection
