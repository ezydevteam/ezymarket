@extends('themes.main.layouts.single')
@section('noindex', true)
@section('header_title', 'Review for: ' . $product->name)
@section('title', $product->name)
@section('header_style', 'minimal')
@section('breadcrumbs', Breadcrumbs::render('products.reviews.review', $product, $review))
@section('container', 'container container-boxed')

@section('main')
<div class="product-review">
    @themeInclude('partials.product-review', [
        'product' => $product,
        'review' => $review,
    ])
</div>
@endsection

