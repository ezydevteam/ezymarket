@extends('themes.main.layouts.single')
@section('header_title', $activeCategory->name)
@section('title', $sectionTitle)
@section('description', $activeCategory->description
    ?? translate('Browse the best products at affordable prices'))
@section('breadcrumbs', Breadcrumbs::render($breadcrumbData['alias'], ...$breadcrumbData['params']))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', $breadcrumbData['alias'], ...$breadcrumbData['params']))

@section('main')
    <x-advertisement alias="category_page_top" @class('mb-4') />

    @if ($totalProductsCount > 0 || $hasFilters)
        @themeInclude('products.partials.listing-options', [
            'products' => $products,
            'hasFilters' => $hasFilters,
            'category' => $category,
            'subCategory' => $subCategory
        ])
    @endif

    <div class="{{ $isSubCategory ? 'subcategory' : 'category' }}-page-content">
        @themeInclude('partials.search.search-filters')
        @themeInclude('products.partials.listing', [
            'products' => $products
        ])
    </div>

    <x-advertisement alias="category_page_bottom" @class('mt-4') />
@endsection
