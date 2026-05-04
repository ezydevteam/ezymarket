@extends(request()->ajax() ? 'admin.layouts.ajax' : 'admin.layouts.app')
@section('section', translate('Products'))
@section('title', translate('Update Review: :name', ['name' => $productUpdate->name]))
@section('header_title', translate('Update Review'))
@section('description', translate('Reviewing changes for :name', ['name' => $productUpdate->name]))
@section('back', route('admin.products.updated.index'))
@section('container', 'container-max-xxl')

@section('content')
    <div class="product-update-show-wrapper">
        @include('admin.products.updated.includes.header')

        <div class="ajax-tabs">
            @include('admin.products.updated.includes.nav-tabs')

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
    <script src="{{ asset('vendor/libs/wavesurfer/wavesurfer.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/plyr/plyr.min.js') }}"></script>
@endpush
