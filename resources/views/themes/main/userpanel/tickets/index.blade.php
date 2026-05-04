@extends('themes.main.userpanel.layout')
@section('title', translate('Tickets'))
@section('breadcrumbs', Breadcrumbs::render('user.ticket.index'))
@section('container', 'userpanel-container-xl')
@section('content')

@if ($hasRecords)
    @section('header_title', translate('Support Tickets'))
    @section('description', translate('Manage your support tickets and get assistance.'))
    @section('header_actions')
        <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
            data-bs-target="#createTicketModal" data-action="{{ route('user.ticket.modal.create') }}">
            <i class="bi bi-plus-circle me-2"></i>{{ translate('New Ticket') }}
        </button>
    @endsection


<x-datatable id="ticketsTable" :columns="$columns" :ajax-url="route('user.ticket.index')" :filters="$filters"
    :server-side="true" data-export="true" :bulk-actions="[]" search-placeholder="{{ translate('Search Tickets...') }}">
    <thead>
        <tr>
            <th>{{ translate('Subject') }}</th>
            <th class="text-center">{{ translate('Category') }}</th>
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
'title' => translate('No Support Tickets Yet!'),
'description' => translate(
'Need help? Create a ticket and our support team will assist you as soon as possible.'
),
'icon' => 'headset',
'btn_text' => translate('Back to Dashboard'),
'btn_url' => route('user.index'),
'modal_id' => 'createTicketModal',
'modal_btn_text' => translate('Create Ticket'),
'modal_action' => route('user.ticket.modal.create'),
])
@endif

<x-modal id="createTicketModal" :header="false" />
@endsection
