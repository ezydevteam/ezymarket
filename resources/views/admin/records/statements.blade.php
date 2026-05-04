@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Statements'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.records.statements.partials.counters')

    <x-datatable
        id="statementsTable"
        :items="$statementsCount"
        :server-side="true"
        :ajax-url="route('admin.records.statements.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Financial Statements')"
        :description="translate('Manage and audit all financial credit and debit records across the platform.')"
        :search-placeholder="translate('Search Statements...')"
        empty-title="No statements found!"
        empty-desc="Financial statements will appear here when transactions are processed."
        empty-icon="bi-receipt"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.records.statements.bulk-delete'),
            'confirm' => translate('Are you sure you want to delete the selected records?'),
        ]"
    />

    <x-modal id="earningDetailsModal" :header="false" />
@endsection
