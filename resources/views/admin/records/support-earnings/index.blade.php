@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Support Earnings'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.support-earnings.partials.counters')

    <x-datatable
        id="supportEarningsTable"
        :ajax-url="route('admin.records.support-earnings.index', request()->query())"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :items="$earningsCount"
        :title="translate('Support Earning Records')"
        :description="translate('Track and audit all earnings generated from support extensions and renewals.')"
        :search-placeholder="translate('Search Earnings...')"
        empty-title="No Earnings Found"
        empty-desc="Support earnings will appear here as sellers earn from support services."
        empty-icon="bi-life-preserver"
    />

    @if(request()->filled('id'))
        <x-modal
            id="earningDetailsModal-{{ request()->query('id') }}"
            :header="false"
            autoOpen="true"
            :data-action="route('admin.records.support-earnings.details.modal', request()->query('id'))"
        />
    @else
        <x-modal id="earningDetailsModal" :header="false" />
    @endif
@endsection
