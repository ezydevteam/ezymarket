@extends('themes.main.userpanel.layout')
@section('title', translate('Refunds'))
@section('breadcrumbs', Breadcrumbs::render('user.refund.index'))
@section('container', 'userpanel-container-xl')

@section('content')

    @if ($hasRecords)
        @section('header_title', translate('Refund Requests'))
        @section('description', translate('Manage your refund requests and track their review process.'))
        @section('header_actions')
            <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal"
                data-bs-target="#createRefundModal" data-action="{{ route('user.refund.modal.create') }}">
                <i class="bi bi-plus-lg me-1"></i>
                {{ translate('Create Request') }}
            </button>
        @endsection

        <x-datatable id="refundsTable" :columns="$columns" :ajax-url="route('user.refund.index')" :filters="$filters"
            :server-side="true" data-export="true" :bulk-actions="[]" search-placeholder="{{ translate('Search Refunds...') }}">
            <thead>
                <tr>
                    <th>{{ translate('Product') }}</th>
                    <th>{{ translate('Refund Request') }}</th>
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
            'title' => translate('No Refund Requests Yet!'),
            'description' => translate(
            'Your refund requests will appear here once you submit one for a purchased product.'
            ),
            'icon' => 'arrow-counterclockwise',
            'btn_text' => translate('Explore Marketplace'),
            'btn_link' => route('home'),
            'modal_id' => 'createRefundModal',
            'modal_btn_text' => translate('Create Refund'),
            'modal_action' => route('user.refund.modal.create'),
        ])

    @endif

    @if (@settings('actions')->refunds)
        <x-modal id="createRefundModal" :header="false" />
    @endif
@endsection
