@extends(request()->ajax() ? 'admin.layouts.ajax' : 'admin.layouts.app')
@section('section', translate('Products'))
@section('title', translate('Product Details'))
@section('header_title', translate('Product Details'))
@section('description', translate('Viewing product details for :name', ['name' => $product->name]))
@section('back', route('admin.products.index'))
@section('container', 'container-max-xxl')

@section('content')
    <div class="product-show-wrapper">
        @include('admin.products.includes.header')

        <div class="ajax-tabs">
            @include('admin.products.includes.nav-tabs')

            <div class="ajax-tabs-content pt-4" id="ajax-tabs-content">
                @include($activePartial)
            </div>
        </div>
    </div>
@endsection

@push('styles_libs')
    <link rel="stylesheet" href="{{ asset('vendor/libs/plyr/plyr.min.css') }}">
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/geochart/geochart.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/wavesurfer/wavesurfer.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/plyr/plyr.min.js') }}"></script>
    <script src="{{ asset_with_version('vendor/admin/js/chart.js') }}"></script>
@endpush

