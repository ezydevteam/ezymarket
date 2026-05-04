@extends('admin.layouts.app')
@section('section', translate('Products'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.products.partials.counters')

    <x-datatable
        id="productsTable"
        :items="$productsCount"
        :title="translate('Manage Products')"
        :description="translate('Review, approve, and manage marketplace products')"
        :columns="$columns"
        :ajax-url="route('admin.products.index')"
        :filters="$filters"
        :server-side="true"
        :search-placeholder="translate('Search Product...')"
        :custom-buttons="[
            $trashedCount > 0 ? [
                'text' => translate('View Trash'),
                'link' => route('admin.products.trash.index'),
                'icon' => 'bi-trash',
                'class' => 'btn btn-outline-secondary'
            ] : null
        ]"
        :bulk-actions="[
            [
                'text' => translate('Bulk Approve'),
                'icon' => 'bi-check-circle',
                'url' => route('admin.products.bulk-approve'),
                'method' => 'POST',
                'confirm' => translate('Are you sure you want to approve the selected products?'),
                'className' => 'dropdown-item text-success'
            ],
            ['className' => 'dropdown-divider'],
            [
                'text' => translate('Delete Selected'),
                'icon' => 'bi-trash',
                'url' => route('admin.products.bulk-delete'),
                'method' => 'DELETE',
                'confirm' => translate('Are you sure you want to delete the selected products?'),
                'className' => 'dropdown-item text-danger'
            ]
        ]"
        emptyTitle="No Products Found"
        emptyDesc="No products found. Please check back later."
        emptyIcon="bi-box-seam"
        emptyIconColor="orange"
    >
    </x-datatable>
@endsection
