@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Premium Earnings'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.premium-earnings.partials.counters')

    <x-datatable
        id="premiumEarningsTable"
        :items="$earningsCount"
        :server-side="true"
        :ajax-url="route('admin.records.premium-earnings.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Premium Earnings')"
        :description="translate('Premium earnings will appear here when users earn from premium products')"
        :search-placeholder="translate('Search Premium Earnings...')"
        empty-title="No premium earnings found!"
        empty-desc="Premium earnings will appear here when users earn from premium products"
        empty-icon="bi-award"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.records.premium-earnings.bulk-delete'),
            'confirm' => translate('Are you sure you want to delete the selected records?'),
        ]"
    />

    <x-modal id="earningDetailsModal" :header="false" />
@endsection
