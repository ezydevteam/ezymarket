@extends('themes.main.layouts.single')
@section('header_title', translate('All Products'))
@section('description', translate('Find the best deals on a wide range of products. Shop now for quality items at affordable prices.'))
@section('title', translate('All Products'))
@section('breadcrumbs', Breadcrumbs::render('products'))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'products'))

@section('main')
    <x-advertisement alias="search_page_top" @class('mb-4') />

    @if ($totalProductsCount > 0 || $hasFilters)
        @themeInclude('products.partials.listing-options', [
            'products' => $products,
            'hasFilters' => $hasFilters
        ])
    @endif

    <div class="product-page-content">
        @themeInclude('partials.search.search-filters')
        @themeInclude('products.partials.listing', [
            'products' => $products
        ])
    </div>

    <x-advertisement alias="search_page_bottom" @class('mt-4') />
@endsection
