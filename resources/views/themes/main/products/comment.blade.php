@extends('themes.main.layouts.single')
@section('noindex', true)
@section('header_title', 'Comment for: ' . $product->name)
@section('title', $product->name)
@section('header_style', 'minimal')
@section('breadcrumbs', Breadcrumbs::render('products.comments.comment', $product, $comment))
@section('container', 'container container-boxed')

@section('main')
<div class="product-comment">
    <livewire:comments.comment-replies
        :comment="$comment"
        wire:key="{{ hash_encode($comment->id) }}"
    />
</div>
<livewire:comments.comment-report />
@endsection
