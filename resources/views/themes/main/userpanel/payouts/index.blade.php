@extends('themes.main.userpanel.layout')
@section('title', translate('Payouts'))
@section('breadcrumbs', Breadcrumbs::render('user.payout'))
@section('container', 'userpanel-container-xl')

@section('content')
@themeInclude('userpanel.payouts.partials.counters')

@if ($hasRecords)
    @section('header_title', translate('Payout History'))
    @section('description', translate('Keep track of your payout requests and financial records.'))
    @section('header_actions')
        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
            data-bs-target="#payoutModal" data-action="{{ route('user.payout.modal.payout') }}">
            <i class="bi bi-wallet2 me-2"></i>{{ translate('Request Payout') }}
        </button>
    @endsection

    <x-datatable id="payoutsTable" :columns="$columns" :ajax-url="route('user.payout.index')" :filters="$filters"
        :server-side="true" data-export="true" :bulk-actions="[]" search-placeholder="{{ translate('Search Payouts...') }}">
        <thead>
            <tr>
                <th>{{ translate('Payout Method') }}</th>
                <th class="text-center">{{ translate('Amount') }}</th>
                <th class="text-center">{{ translate('Fees') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            {{-- DataTables will load data via AJAX --}}
        </tbody>
    </x-datatable>
@else
    @themeInclude('userpanel.partials.empty', [
        'title' => translate('No Payouts Yet!'),
        'description' => translate(
            'Withdraw your earnings directly to your chosen payment method. It\'s fast, simple, and secure.'
        ),
        'icon' => 'bank',
        'btn_text' => translate('Back to Dashboard'),
        'btn_url' => route('user.index'),
        'modal_id' => 'payoutModal',
        'modal_btn_text' => translate('Request My First Payout'),
        'modal_action' => route('user.payout.modal.payout'),
    ])
@endif


{{-- Payout Logic Shell --}}
<x-modal id="payoutModal" :header="false" />

{{-- Payout Details Modal --}}
<x-modal id="payoutDetailsModal" :header="false" />
@endsection
