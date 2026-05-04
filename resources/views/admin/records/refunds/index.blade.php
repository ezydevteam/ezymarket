@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Refunds'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.refunds.partials.counters')

    <x-datatable
        id="refundsTable"
        :items="$refundsCount"
        :server-side="true"
        :ajax-url="route('admin.records.refunds.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Refund Records')"
        :description="translate('Manage and audit all refund requests between buyers and sellers.')"
        :search-placeholder="translate('Search Refunds...')"
        empty-title="No refunds found!"
        empty-desc="Refund requests will appear here when buyers initiate them for their purchases."
        empty-icon="bi-arrow-return-left"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.records.refunds.bulk-delete'),
            'confirm' => translate('Are you sure you want to delete the selected records?'),
        ]"
    />

    <x-modal id="earningDetailsModal" :header="false" />
@endsection
