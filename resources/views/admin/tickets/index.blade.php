@extends('admin.layouts.app')
@section('section', translate('Support'))
@section('title', translate('Tickets'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.tickets.partials.counters')

    <x-datatable
        id="ticketsTable"
        :items="$ticketsCount"
        :server-side="true"
        :ajax-url="route('admin.tickets.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Support Tickets')"
        :description="translate('Manage and respond to all user support requests and inquiries.')"
        :search-placeholder="translate('Search Tickets...')"
        :custom-buttons="[
            [
                'text' => translate('Create Ticket'),
                'link' => route('admin.tickets.create.modal'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary',
                'type' => 'modal',
                'target' => '#createTicketModal',
                'action' => route('admin.tickets.create.modal')
            ]
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.tickets.bulk-destroy'),
            'confirm' => translate('Are you sure you want to delete the selected records?'),
        ]"
        empty-title="No tickets found!"
        empty-desc="Support tickets will appear here when users submit them for assistance."
        empty-icon="bi-ticket-perforated"
        :empty-btn-modal="'#createTicketModal'"
        :empty-btn-modal-text="translate('Create Ticket')"
        :empty-btn-modal-action="route('admin.tickets.create.modal')"
        :empty-btn-modal-icon="'bi-plus-lg'" />

    <x-modal id="createTicketModal" :header="false" />
@endsection
