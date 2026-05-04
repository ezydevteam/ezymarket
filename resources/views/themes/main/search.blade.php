@extends('themes.main.layouts.single')
@section('header_title', $headerTitle)
@section('title', $sectionTitle)
@section('breadcrumbs', Breadcrumbs::render('products'))

@section('main')
    <x-advertisement alias="search_page_top" @class('mb-4') />

    @themeInclude('products.partials.listing-options', [
        'products' => $products,
        'hasFilters' => $hasFilters
    ])

    <div class="search-page-content">
        @themeInclude('partials.search.search-filters')
        @themeInclude('products.partials.listing', [
            'products' => $products
        ])
    </div>

    <x-advertisement alias="search_page_bottom" @class('mt-4') />
@endsection
