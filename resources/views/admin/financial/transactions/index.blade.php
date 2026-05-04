@extends('admin.layouts.app')
@section('section', translate('Financial'))
@section('title', translate('Transactions'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.financial.transactions.partials.counters')

    <x-datatable
        id="transactionsTable"
        :ajax-url="route('admin.financial.transactions.index')"
        :items="$trxCount"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :title="translate('All Transactions')"
        :description="translate('Manage and monitor all financial transactions.')"
        :search-placeholder="translate('Search Transaction')"
        :custom-buttons="[
            $trashedCount > 0 ? [
                'text' => translate('View Trash'),
                'link' => route('admin.financial.transactions.trash.index'),
                'icon' => 'bi-trash',
                'class' => 'btn btn-outline-secondary'
            ] : null
        ]"
        :bulk-actions="[
            [
                'text' => translate('Mark as Paid'),
                'icon' => 'bi-check-circle text-success',
                'url' => route('admin.financial.transactions.bulk-paid'),
                'confirm' => translate('Are you sure you want to mark the selected transactions as paid?'),
            ],
            [
                'text' => translate('Cancel Selected'),
                'icon' => 'bi-x-circle text-warning',
                'url' => route('admin.financial.transactions.bulk-cancel'),
                'input' => true,
                'prompt' => translate('Cancellation Reason'),
                'promptField' => 'rejection_reason',
                'confirm' => translate('Are you sure you want to cancel the selected transactions?'),
            ],
            ['type' => 'divider'],
            [
                'text' => translate('Delete Selected'),
                'icon' => 'bi-trash',
                'class' => 'dropdown-item text-danger',
                'method' => 'DELETE',
                'url' => route('admin.financial.transactions.bulk-delete'),
                'confirm' => translate('Are you sure you want to delete the selected transactions? This action cannot be undone.'),
            ]
        ]"
        :empty-title="translate('No Transactions Found')"
        :empty-desc="translate('Transactions will appear here once users make purchases.')"
        :empty-icon="'bi-receipt'"
    />

    <x-modal id="detailsModal" :header="false" />
@endsection
