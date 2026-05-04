@extends('admin.layouts.app')
@section('section', translate('Support'))
@section('title', translate('Trashed Tickets'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.tickets.partials.counters')

    <x-datatable
        id="ticketsTrashTable"
        :items="$ticketsCount"
        :server-side="true"
        :ajax-url="route('admin.tickets.trash.index', request()->query())"
        :columns="$columns"
        :filters="$filters"
        :export="true"
        :title="translate('Administrative Trash')"
        :description="translate('Manage and restore support tickets that have been soft-deleted by administrators.')"
        :search-placeholder="translate('Search Trash...')"
        empty-title="Trash is empty!"
        empty-desc="Tickets deleted by administrators will appear here for restoration or permanent removal."
        empty-icon="bi-trash"
        :bulk-delete-btn="[
            'text' => translate('Permanently Delete'),
            'url' => route('admin.tickets.bulk-destroy'),
            'confirm' => translate('Are you sure you want to permanently delete the selected records? This action cannot be undone.'),
        ]"
    >
        <x-slot:extra_buttons>
            <button type="button" class="btn btn-success action-confirm" 
                data-action="{{ route('admin.tickets.trash.bulk-restore') }}"
                data-method="POST"
                data-bulk="true"
                data-table="#ticketsTrashTable"
                data-confirm="{{ translate('Are you sure you want to restore the selected tickets?') }}">
                <i class="bi bi-arrow-counterclockwise me-2"></i>{{ translate('Restore Selected') }}
            </button>
        </x-slot>
    </x-datatable>
@endsection
