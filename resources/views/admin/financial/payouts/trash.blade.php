@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Trashed Payouts'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="payoutsTrashTable"
        :items="$trashedCount"
        :ajax-url="route('admin.financial.payouts.trash.index')"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :title="translate('Trashed Payouts')"
        :description="translate('Manage payout requests deleted by administrators.')"
        :empty-title="translate('Payout Trash is empty')"
        :empty-desc="translate('Payout requests deleted by administrators will appear here.')"
        :empty-btn-text="translate('Back to Payouts')"
        :empty-btn-link="route('admin.financial.payouts.index')"
        :empty-btn-icon="'bi-arrow-left'"
        :empty-icon="'bi-trash'"
        :custom-buttons="[
            [
                'text' => translate('Active Payouts'),
                'link' => route('admin.financial.payouts.index'),
                'icon' => 'bi-arrow-counterclockwise',
                'class' => 'btn btn-outline-primary'
            ]
        ]"
        :bulk-actions="[
            [
                'text' => translate('Restore Selected'),
                'icon' => 'bi-arrow-counterclockwise text-success',
                'url' => route('admin.financial.payouts.trash.bulk-restore'),
                'method' => 'POST',
                'confirm' => translate('Are you sure you want to restore the selected payouts?'),
            ],
            ['type' => 'divider'],
            [
                'text' => translate('Permanently Delete'),
                'icon' => 'bi-trash',
                'class' => 'dropdown-item text-danger',
                'method' => 'DELETE',
                'url' => route('admin.financial.payouts.bulk-delete'),
                'confirm' => translate('Are you sure you want to permanently delete the selected payouts? This action cannot be undone.'),
            ]
        ]"
    />

    <x-modal id="detailsModal" :header="false" />
@endsection
