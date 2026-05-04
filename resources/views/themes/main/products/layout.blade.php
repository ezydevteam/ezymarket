@extends('themes.main.layouts.app')

@if ($product->gallery)
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/jquery/fancybox/jquery.fancybox.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/jquery/fancybox/jquery.fancybox.min.js') }}"></script>
    @endpush
@endif

@section('body_class', 'product-page')

@section('body_content')
    @php
    $data = (object)($productPageData ?? []);
    $seller = $product->seller;
    $productSettings = $settings->product;
    @endphp

    <x-advertisement alias="product_page_top" class="container mt-4" />

    {{-- HERO HEADER LAYOUT --}}
    @if ($data->display_layout === 'hero_header')
    @themeInclude('products.partials.hero-header')
    @endif

    {{-- MODERN SPLIT LAYOUT --}}
    @if ($data->display_layout === 'modern_split' || $data->display_layout === 'fullwidth_title')
    <section
        class="product-modern-split pt-4 {{ $data->display_layout === 'fullwidth_title' ? 'bg-light-subtle border-bottom' : '' }}">
        <div class="{{ $data->container_class }}">
            <div
                class="row g-4 {{ $data->display_layout === 'modern_split' ? 'align-items-center' : 'align-items-start' }}">
                @if ($data->display_layout === 'fullwidth_title')
                <div class="col-12">
                    @themeInclude('products.partials.product-meta')
                    @themeInclude('products.partials.product-tabs', ['render_part' => 'nav'])
                </div>
                @elseif ($data->display_layout === 'modern_split')
                <div class="col-lg-5">
                    @themeInclude('products.partials.product-preview')
                </div>
                <div class="col-lg-7">
                    <div class="modern-split-info h-100 d-flex flex-column">
                        @themeInclude('products.partials.product-meta')
                        @themeInclude('products.partials.price-card-modern')
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- MAIN CONTENT + SIDEBAR SECTION --}}
    <section
        class="section forced-start {{ ($data->display_layout === 'hero_header' || $data->display_layout === 'modern_split') ? 'pb-3' : 'pt-4 pb-3' }}">
        <div class="{{ $data->container_class }}">
            <div class="section-body">
                <div class="row g-4">

                    {{-- Main Content Area --}}
                    <div class="{{ $data->main_col_class }}" id="productContentSection">

                        @if ($data->display_layout === 'modern_split' || $data->display_layout === 'hero_header')
                        {{-- These layouts render preview/meta above. Only show tabs here. --}}
                        @themeInclude('products.partials.product-tabs')

                        @elseif ($data->display_layout === 'minimalist')
                        @themeInclude('products.partials.product-meta')
                        @themeInclude('products.partials.product-preview')
                        @themeInclude('products.partials.product-tabs')

                        @elseif ($data->display_layout === 'gallery_focus')
                        <div class="position-relative">
                            @themeInclude('products.partials.product-preview')
                        </div>
                        <div class="gallery-focus-meta-wrap mb-4">
                            <div class="gallery-focus-meta-card text-center">
                                @themeInclude('products.partials.product-meta')
                            </div>
                        </div>
                        @themeInclude('products.partials.product-tabs')

                        @elseif ($data->display_layout === 'fullwidth_title')
                        {{-- Full Width Title --}}
                        @themeInclude('products.partials.product-tabs', ['render_part' => 'content'])

                        @else
                        {{-- Default --}}
                        @themeInclude('products.partials.product-meta')
                        @themeInclude('products.partials.product-preview')
                        @themeInclude('products.partials.product-tabs')
                        @endif

                        <x-advertisement alias="product_page_center" class="container my-3" />
                    </div>

                    {{-- Sidebar --}}
                    @if($data->sidebar_position !== 'no_sidebar')
                    <div class="{{ $data->sidebar_col_class }}" id="productSidebarSection">
                        <x-widget name="single-product-sidebar" :context="$product" />
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </section>

    {{-- SELLER'S OTHER PRODUCTS --}}
    @if ($data->seller_more_products)
    @themeInclude('products.partials.product-swiper', [
    'products' => $sellerProducts,
    'title' => translate('More Products By :seller', ['seller' => $seller->username]),
    'view_link' => $seller->portfolio_link,
    ])
    @endif

    {{-- RELATED PRODUCTS --}}
    @if ($data->related_products)
    @themeInclude('products.partials.product-swiper', [
    'products' => $relatedProducts,
    'title' => translate('Related Products'),
    'view_link' => $product->subCategory ? $product->subCategory->view_link : $product->category->view_link,
    ])
    @endif

    {{-- FOOTER AD --}}
    <x-advertisement alias="product_page_bottom" class="container mb-5" />
@endsection

@push('footer_content')
    @themeInclude('partials.modals.product-modal', ['product' => $product])
    @livewire('comments.comment-report', [], key('comment-report-layout-'.$product->id))
    <script src="{{ theme_assets_with_version('assets/js/product-page.js') }}"></script>
@endpush
