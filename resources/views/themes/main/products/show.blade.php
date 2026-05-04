@extends('themes.main.products.layout')
@section('title', $product->name)
@section('og_image', $product->getImageLink())
@section('description', truncateText($product->description, 155, '...', true))
@section('keywords', $product->tags)
@section('breadcrumbs', Breadcrumbs::render('products.show', $product, (object)($productPageData ?? [])))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'products.show', $product,
(object)($productPageData ?? [])))
@section('content')
<div class="single-product-content">
    {!! $product->description !!}
</div>
@push('schema')
{!! schema($__env, 'product', ['product' => $product]) !!}
@endpush
@endsection
