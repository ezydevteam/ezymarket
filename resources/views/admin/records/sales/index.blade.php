@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Sales Record'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.sales.partials.counters')

    <x-datatable
        id="salesTable"
        :ajax-url="route('admin.records.sales.index', request()->query())"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :items="$salesCount"
        :title="translate('Sales Records')"
        :description="translate('Monitor and manage all product sales across the platform.')"
        :search-placeholder="translate('Search Sales...')"
        :bulk-actions="[
            [
                'text' => translate('Cancel Selected'),
                'icon' => 'bi-x-circle text-warning',
                'url' => route('admin.records.sales.bulk-cancel'),
                'confirm' => translate('Are you sure you want to cancel the selected sales?'),
            ],
            ['type' => 'divider'],
            [
                'text' => translate('Delete Selected'),
                'icon' => 'bi-trash',
                'class' => 'dropdown-item text-danger',
                'method' => 'DELETE',
                'url' => route('admin.records.sales.bulk-delete'),
                'confirm' => translate('Are you sure you want to delete the selected sales records? This action cannot be undone.'),
            ]
        ]"
        empty-title="No Sales Records Found"
        empty-desc="Sales records will appear here as customers purchase products."
        empty-icon="bi-cart-x"
    />

    @if(request()->filled('id'))
        <x-modal
            id="saleDetailsModal-{{ request()->query('id') }}"
            :header="false"
            autoOpen="true"
            :data-action="route('admin.records.sales.details.modal', request()->query('id'))"
        />
    @else
        <x-modal id="saleDetailsModal" :header="false" />
    @endif
@endsection
