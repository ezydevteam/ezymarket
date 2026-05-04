@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Purchases'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.purchases.partials.counters')

    <x-datatable
        id="purchasesTable"
        :ajax-url="route('admin.records.purchases.index', request()->query())"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :items="$purchasesCount"
        :title="translate('Purchase Records')"
        :description="translate('Monitor and manage all customer purchases across the platform.')"
        :search-placeholder="translate('Search Purchases...')"
        empty-title="No Purchase Records Found"
        empty-desc="Purchase records will appear here as customers buy products."
        empty-icon="bi-bag-x"
    />

    @if(request()->filled('id'))
        <x-modal
            id="purchaseDetailsModal-{{ request()->query('id') }}"
            :header="false"
            autoOpen="true"
            :data-action="route('admin.records.purchases.details.modal', request()->query('id'))"
        />
    @else
        <x-modal id="purchaseDetailsModal" :header="false" />
    @endif
@endsection

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/clipboard/clipboard.min.js') }}"></script>
@endpush

