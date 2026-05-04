@extends('themes.main.layouts.app')

@section('theme_head')
@section('title', $product->name)
@section('noindex', true)
@themeInclude('includes.head')
<style>
    :root {
        --preview-nav-height: 60px;
        --preview-bg: #1e293b;
        --preview-accent: var(--bs-primary);
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        background-color: var(--preview-bg) !important;
        font-family: 'Outfit', 'Inter', system-ui, -apple-system, sans-serif;
        height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .preview-nav {
        height: var(--preview-nav-height) !important;
        background: rgba(15, 23, 42, 0.85) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .preview-nav.toggle {
        transform: translateY(-100%) !important;
    }

    .preview-nav .logo img {
        max-height: 40px;
        width: auto;
        transition: opacity 0.3s;
    }

    .preview-nav-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.05);
        padding: 6px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .preview-nav-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.25rem;
    }

    .preview-nav-action.active {
        background: var(--preview-accent);
        color: #fff;
        box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.3);
    }

    .btn-buy {
        font-weight: 600;
        padding: 8px 24px;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .preview-btn {
        position: fixed !important;
        top: var(--preview-nav-height) !important;
        right: 30px !important;
        width: 44px !important;
        height: 32px !important;
        background: rgba(15, 23, 42, 0.85) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-top: none !important;
        border-radius: 0 0 12px 12px !important;
        color: #fff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1001 !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .preview-nav.toggle~.preview-btn {
        transform: translateY(calc(-1 * var(--preview-nav-height))) !important;
    }

    .preview-btn i {
        transition: transform 0.4s !important;
        font-size: 1.2rem !important;
        pointer-events: none;
    }

    .preview-nav.toggle~.preview-btn i {
        transform: rotate(180deg) !important;
    }

    .preview-body {
        position: absolute !important;
        top: var(--preview-nav-height) !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        margin-top: 0 !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .preview-body.toggle {
        top: 0 !important;
    }

    .preview-body iframe {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        flex-grow: 1 !important;
        transition: all 0.4s ease !important;
    }

    .preview-body.tablet iframe {
        max-width: 768px !important;
        height: calc(100% - 40px) !important;
        margin: 20px auto !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
    }

    .preview-body.mobile iframe {
        max-width: 375px !important;
        height: calc(100% - 40px) !important;
        margin: 20px auto !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
    }

    @media (max-width: 991px) {
        .preview-desktop {
            display: none !important;
        }

        :root {
            --preview-nav-height: 60px;
        }
    }
</style>
@endsection

@section('theme_header', '')
@section('theme_footer', '')
@section('theme_config', '')
@push('theme_scripts', '')

@section('body_content')
<div class="preview-nav">
    <div class="container-fluid h-100">
        <div class="d-flex align-items-center justify-content-between h-100 px-lg-3">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ getSiteLogo('logo_dark') }}" alt="{{ getSiteName() }}" />
            </a>

            <div class="preview-nav-actions">
                <div class="preview-nav-action preview-desktop active" title="Desktop View">
                    <i class="bi bi-laptop"></i>
                </div>
                <div class="preview-nav-action preview-tablet d-none d-md-flex" title="Tablet View">
                    <i class="bi bi-tablet"></i>
                </div>
                <div class="preview-nav-action preview-mobile d-none d-md-flex" title="Mobile View">
                    <i class="bi bi-phone"></i>
                </div>
            </div>

            <a href="{{ $product->view_link }}" class="btn btn-primary btn-buy">
                <i class="bi bi-cart-check me-2"></i>
                <span class="d-none d-lg-inline">{{ translate("Buy Now") }}</span>
                <span class="d-inline d-lg-none">{{ translate("Buy") }}</span>
            </a>
        </div>
    </div>
</div>

<div class="preview-btn">
    <i class="bi bi-chevron-up"></i>
</div>

<div class="preview-body">
    <iframe src="{{ $product->demo_link }}"></iframe>
</div>

@endsection

@push('footer_content')
<script src="{{ asset('vendor/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codebay/toastr/js/toastr.min.js') }}"></script>
<script src="{{ theme_assets_with_version('assets/js/app.js') }}"></script>
<script>
    $(document).ready(function () {
        // Direct toggle handler for absolute reliability
        $(document).on('click', '.preview-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.preview-nav').toggleClass('toggle');
            $('.preview-body').toggleClass('toggle');
        });
    });
</script>
@endpush
