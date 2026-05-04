@extends('admin.layouts.app')
@section('section', translate('Products'))
@section('title', translate('Updated Products'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="productUpdatesTable"
        :items="$updatesCount"
        :title="translate('Updated Products')"
        :description="translate('Review, approve, and manage updated products')"
        :columns="$columns"
        :ajax-url="route('admin.products.updated.index')"
        :filters="$filters"
        :server-side="true"
        export="true"
        :bulkActions="[
            [
                'text' => translate('Approve Selected'),
                'icon' => 'bi-check-circle',
                'iconClass' => 'text-success',
                'url' => route('admin.products.updated.bulk-approve'),
                'method' => 'POST',
                'confirm' => translate('Are you sure you want to approve the selected updates?')
            ],
            [
                'text' => translate('Reject Selected'),
                'icon' => 'bi-x-circle',
                'iconClass' => 'text-orange',
                'url' => route('admin.products.updated.bulk-reject'),
                'method' => 'POST',
                'prompt' => translate('Please enter rejection reason:'),
                'promptField' => 'reason',
                'confirm' => translate('Are you sure you want to reject the selected updates?')
            ],
            ['className' => 'dropdown-divider'],
            [
                'text' => translate('Delete Selected'),
                'icon' => 'bi-trash',
                'iconClass' => 'text-danger',
                'url' => route('admin.products.updated.bulk-delete'),
                'method' => 'DELETE',
                'confirm' => translate('Are you sure you want to delete the selected updates?'),
                'className' => 'dropdown-item text-danger'
            ]
        ]"
        emptyTitle="No Updated Products Found"
        emptyDesc="No updated products found. Please check back later."
        emptyIcon="bi-clock-history"
        emptyIconColor="orange"
    >
    </x-datatable>
@endsection
