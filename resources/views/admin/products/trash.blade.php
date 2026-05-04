@extends('admin.layouts.app')
@section('section', translate('Products'))
@section('title', translate('Trashed Products'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="trashedProductsTable"
        :items="$trashCount"
        :title="translate('Trashed Products')"
        :description="translate('Manage soft-deleted products, restore or delete them permanently.')"
        :columns="$columns"
        :ajax-url="route('admin.products.trash.index')"
        :server-side="true"
        :bulkActions="[
            [
                'text' => translate('Restore Selected'),
                'icon' => 'bi-arrow-counterclockwise',
                'iconClass' => 'text-success',
                'url' => route('admin.products.trash.bulk-restore'),
                'method' => 'POST',
                'confirm' => translate('Are you sure you want to restore the selected products?')
            ],
            [
                'text' => translate('Permanently Delete'),
                'icon' => 'bi-trash',
                'iconClass' => 'text-danger',
                'url' => route('admin.products.trash.bulk-permanently-delete'),
                'method' => 'DELETE',
                'confirm' => translate('Are you sure you want to permanently delete the selected products? This action cannot be undone!'),
                'className' => 'dropdown-item text-danger'
            ]
        ]"
        :customButtons="[
            [
                'text' => translate('Active Products'),
                'icon' => 'bi-box-seam',
                'url' => route('admin.products.index'),
                'class' => 'btn btn-outline-primary'
            ]
        ]"
        emptyTitle="No Trashed Products Found"
        emptyDesc="Your trash is currently empty. Deleted products will appear here."
        emptyIcon="bi-recycle"
        emptyIconColor="success"
    >
    </x-datatable>
@endsection
