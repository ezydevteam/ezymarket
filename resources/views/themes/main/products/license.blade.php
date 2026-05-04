@extends('themes.main.layouts.app')

@section('theme_head')
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="robots" content="noindex, nofollow" />
<title>{{ translate('License Certificate for :product_name', ['product_name' => $product->name]) }}</title>
<link rel="icon" href="{{ asset($themeSettings->general->favicon) }}">
@endsection

@section('theme_styles')
@bootstrap
@themeColors
@themeCustomStyle
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/app.css') }}" />
@endsection

@section('body_class', 'license-certificate-page')

{{-- Disable standard layouts for a clean printable page --}}
@section('theme_header', '')
@section('theme_footer', '')
@section('theme_config', '')

@section('body_content')
<div class="license-container">
    <div class="cert-header d-flex justify-content-between align-items-center">
        <div>
            <span class="cert-subtitle">{{ translate('Verification Document') }}</span>
            <h1 class="cert-title">{{ translate('License Certificate') }}</h1>
        </div>
        <div class="text-end">
            <img src="{{ getSiteLogo() }}" alt="{{ getSiteName() }}" height="45px">
        </div>
    </div>

    <div class="cert-body">
        <div class="mb-5">
            <p class="fs-5 text-gray-700 mb-4">
                @if($licenseType === 'premium')
                {{ translate('This document certifies the product purchase and usage rights granted under the
                following license:') }}
                @else
                {{ translate('This document certifies the product usage rights granted under the following
                license:') }}
                @endif
            </p>
            <div class="license-type-badge">
                {{ strtoupper(translate($licenseType === 'premium' ? 'Premium License' : 'Free License')) }}
            </div>
        </div>

        <div class="cert-meta">
            <div class="meta-item">
                <span class="meta-label">{{ translate("Licensor's Seller") }}</span>
                <div class="meta-value">
                    {{ $product->seller->full_name }}
                    <small class="text-muted d-block">&#64;{{ $product->seller->username }}</small>
                </div>
            </div>

            <div class="meta-item">
                <span class="meta-label">{{ translate('Licensee') }}</span>
                <div class="meta-value">{{ authUser()->full_name }}</div>
                <small class="text-muted d-block">&#64;{{ authUser()->username }}</small>
            </div>

            <div class="meta-item">
                <span class="meta-label">{{ translate('Product Information') }}</span>
                <div class="meta-value">{{ $product->name }}</div>
                <small class="text-gray-700">ID: {{ $product->id }}</small>
            </div>

            <div class="meta-item">
                <span class="meta-label">{{ translate('Issue Date') }}</span>
                <div class="meta-value">{{ dateFormat(now()) }}</div>
            </div>

            <div class="meta-item mt-3" style="grid-column: span 2;">
                <span class="meta-label">{{ translate('Product URL') }}</span>
                <div class="meta-value text-break">
                    <a href="{{ $product->view_link }}" class="text-accent hover-underline">
                        {{ $product->view_link }}
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-5 p-4 rounded-3 border bg-light">
            <h5 class="fw-bold mb-2">{{ translate('Legal Information') }}</h5>
            <p class="mb-0 small text-gray-700 lh-base">
                @if($licenseType === 'premium')
                {{ translate("The details of this license certificate are associated with the transaction and the
                account of the licensee mentioned above. This document is a valid proof of purchase as long as the
                product is available on our platform.") }}
                @else
                {{ translate("The details of this license certificate are associated with the user account of the
                licensee mentioned above. This document is a valid proof of usage rights as long as the product is
                available on our platform.") }}
                @endif
            </p>
        </div>
    </div>

    <div class="cert-footer">
        @if (@$settings->actions->contact_page)
        <p class="small text-muted mb-1">
            {{ translate('Questions? Reach out at') }}
            <a href="{{ route('contact.index') }}" class="fw-bold">{{ url('/contact') }}</a>
        </p>
        @endif
        <p class="mb-0 fw-bold">{{ getSiteName() }}</p>
    </div>
</div>

<div class="print-btn-wrapper mb-5">
    <button class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" onclick="window.print()">
        <i class="bi bi-printer me-2"></i>
        {{ translate('Print Certificate') }}
    </button>
</div>
@endsection

@section('theme_scripts')
<script src="{{ asset('vendor/libs/jquery/jquery.min.js') }}"></script>
@endsection
