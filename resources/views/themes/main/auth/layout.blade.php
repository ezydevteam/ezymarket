@extends('themes.main.layouts.app')
@section('body_class', 'auth-page')

@section('body_content')

@section('theme_header', '')
@section('theme_footer', '')

@php
$isRegister = request()->routeIs('register');
$colClass = $isRegister ? 'col-lg-8 col-xl-7 col-xxl-6' : 'col-lg-7 col-xl-6 col-xxl-5';
@endphp

<section class="section auth-section">
    <div class="container auth-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="row auth-wrapper justify-content-center w-100">
            <div class="col-12 col-md-9 {{ $colClass }}">
                @yield('content')
            </div>
        </div>
    </div>
</section>
@endsection
