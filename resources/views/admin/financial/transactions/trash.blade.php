@extends('admin.layouts.app')
@section('title', translate('Trashed Transactions'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="transactionsTrashTable"
        :ajax-url="route('admin.financial.transactions.trash.index')"
        server-side="true"
        :columns="$columns"
        :filters="$filters"
        :title="translate('Trashed Transactions')"
        :description="translate('Manage transactions deleted by administrators.')"
        :empty-title="translate('Trash is empty')"
        :empty-desc="translate('Transactions deleted by administrators will appear here.')"
        :empty-icon="'bi-trash'"
        :custom-buttons="[
            [
                'text' => translate('Active Transactions'),
                'link' => route('admin.financial.transactions.index'),
                'icon' => 'bi-arrow-counterclockwise',
                'class' => 'btn btn-outline-primary'
            ]
        ]"
        :bulk-actions="[
            [
                'text' => translate('Restore Selected'),
                'icon' => 'bi-arrow-counterclockwise text-success',
                'url' => route('admin.financial.transactions.trash.bulk-restore'),
                'confirm' => translate('Are you sure you want to restore the selected transactions?'),
            ],
            [
                'text' => translate('Permanently Delete'),
                'icon' => 'bi-trash text-danger',
                'url' => route('admin.financial.transactions.bulk-delete'),
                'confirm' => translate('Are you sure you want to permanently delete the selected transactions? This action cannot be undone.'),
                'method' => 'DELETE'
            ]
        ]"
    />

    <x-modal id="detailsModal" :header="false" />
@endsection
