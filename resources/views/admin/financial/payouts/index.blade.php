@extends('admin.layouts.full')
@section('section', translate('Financial'))
@section('title', translate('Payouts'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.financial.payouts.partials.counters')

    <x-datatable
        id="payoutsTable"
        :items="$payoutsCount"
        :ajax-url="route('admin.financial.payouts.index')"
        :server-side="true"
        :columns="$columns"
        :filters="$filters"
        :title="translate('Payout Requests')"
        :description="translate('Manage and monitor all seller payout requests.')"
        :search-placeholder="translate('Search Payouts')"
        :custom-buttons="[
            $trashedCount > 0 ? [
                'text' => translate('View Trash'),
                'link' => route('admin.financial.payouts.trash.index'),
                'icon' => 'bi-trash',
                'class' => 'btn btn-outline-secondary'
            ] : null
        ]"
        :bulk-actions="[
            [
                'text' => translate('Approve Selected'),
                'icon' => 'bi-check-circle text-success',
                'url' => route('admin.financial.payouts.bulk-approve'),
                'input' => true,
                'prompt' => translate('Admin Note'),
                'promptField' => 'admin_note',
                'confirm' => translate('Are you sure you want to approve the selected payouts?'),
            ],
            [
                'text' => translate('Complete Selected'),
                'icon' => 'bi-check2-square text-primary',
                'url' => route('admin.financial.payouts.bulk-completed'),
                'input' => true,
                'prompt' => translate('Admin Note'),
                'promptField' => 'admin_note',
                'confirm' => translate('Are you sure you want to complete the selected payouts?'),
            ],
            [
                'text' => translate('Return Selected'),
                'icon' => 'bi-arrow-return-left text-info',
                'url' => route('admin.financial.payouts.bulk-return'),
                'input' => true,
                'prompt' => translate('Admin Note'),
                'promptField' => 'admin_note',
                'confirm' => translate('Are you sure you want to return the selected payouts?'),
            ],
            [
                'text' => translate('Cancel Selected'),
                'icon' => 'bi-x-circle text-orange',
                'url' => route('admin.financial.payouts.bulk-cancel'),
                'input' => true,
                'prompt' => translate('Admin Note'),
                'promptField' => 'admin_note',
                'confirm' => translate('Are you sure you want to cancel the selected payouts?'),
            ],
            ['type' => 'divider'],
            [
                'text' => translate('Delete Selected'),
                'icon' => 'bi-trash',
                'class' => 'dropdown-item text-danger',
                'method' => 'DELETE',
                'url' => route('admin.financial.payouts.bulk-delete'),
                'confirm' => translate('Are you sure you want to delete the selected payouts? This action cannot be undone.'),
            ]
        ]"
        :empty-title="translate('No Payouts Found')"
        :empty-desc="translate('Payout requests will appear here when sellers request their earnings.')"
        :empty-icon="'bi-wallet2'"
    />

    <x-modal id="detailsModal" :header="false" />
@endsection
