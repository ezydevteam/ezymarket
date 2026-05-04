@extends('admin.layouts.app')
@section('title', translate('Categories'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="productCategoriesTable"
        table-class="sortable-table table-hover"
        :items="$categoriesCount"
        :title="translate('Product Categories')"
        :description="translate('Manage product categories')"
        :columns="$columns"
        :ajax-url="route('admin.products.categories.index')"
        :server-side="true"
        :search-placeholder="translate('Search categories...')"
        :sorting-route="route('admin.products.categories.sortable')"
        :order="[[1, 'asc']]"
        :custom-buttons="[
            [
                'text' => translate('Create Category'),
                'link' => route('admin.products.categories.create'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary'
            ]
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.products.categories.bulk-destroy'),
            'confirm' => translate('Are you sure you want to delete the selected categories?'),
        ]"
        empty-title="No Categories Found"
        empty-desc="Create your first category to get started."
        empty-icon="bi-folder"
        empty-btn-text="Create Category"
        :empty-btn-link="route('admin.products.categories.create')"
    >
    </x-datatable>
@endsection

@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
@endpush
