@extends('themes.main.layouts.app')
@section('body_class', 'layout-profile')
@php
    $containerWidth = @$themeSettings->profile->container_width ?? 'default';

    match ($containerWidth) {
        'boxed' => $containerClass = 'container container-boxed',
        'fluid' => $containerClass = 'container-fluid',
        default => $containerClass = 'container container-default',
    };
@endphp

@section('body_content')
<div class="user-profile mb-5">
    @themeInclude('profile.includes.header')

    <div class="user-profile-content ajax-tabs">
        <div class="{{ $containerClass }}">
            <div class="row g-5 justify-content-center">
                <aside class="col-12 col-md-4 {{ $containerWidth === 'boxed' ? 'col-lg-4' : 'col-lg-3' }}">
                    @themeInclude('profile.includes.sidebar')
                </aside>

                <main class="col-12 col-md-8 {{ $containerWidth === 'boxed' ? 'col-lg-8' : 'col-lg-9' }}">
                    <div class="ajax-tabs-content">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>
@endsection
