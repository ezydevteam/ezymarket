@extends('themes.main.userpanel.layout')
@section('title', translate('My Products'))
@section('breadcrumbs', Breadcrumbs::render('user.product'))

@section('content')
    @if ($hasRecords)
        @section('header_title', translate('Products'))
        @section('description', translate('Manage your products, updates, and sales performance.'))
        @section('header_actions')
            <a href="{{ route('user.product.create') }}" class="btn btn-primary px-4 py-2">
                <i class="bi bi-plus-circle me-2"></i>{{ translate('New Product') }}
            </a>
        @endsection

        <x-datatable id="productsTable" :columns="$columns" :ajax-url="route('user.product.index')" :filters="$filters"
            :server-side="true" data-export="true" :bulk-actions="[]" search-placeholder="{{ translate('Search Products...') }}">
            <thead>
                <tr>
                    <th>{{ translate('Product Details') }}</th>
                    <th class="text-center">{{ translate('Price') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
                    <th class="text-center">{{ translate('Published Date') }}</th>
                    <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- DataTables will load data via AJAX --}}
            </tbody>
        </x-datatable>
    @else
        @themeInclude('userpanel.partials.empty', [
            'title' => translate('No Products Yet!'),
            'description' => translate(
                'There are no products, you can start by clicking "New Product" button.'
            ),
            'icon' => 'box-seam',
            'btn_text' => translate('New Product'),
            'btn_url' => route('user.product.create'),
        ])
    @endif
@endsection
