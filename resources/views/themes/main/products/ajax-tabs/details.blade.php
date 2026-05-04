@php $displayLayout = $data->display_layout ?? 'default'; @endphp

@if ($displayLayout === 'fullwidth_title')
@themeInclude('products.partials.product-preview')
@endif

<div class="product-single-paragraph">
    {!! $product->description !!}
</div>

@push('schema')
{!! schema($__env, 'product', ['product' => $product]) !!}
@endpush
