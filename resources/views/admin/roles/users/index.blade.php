@extends('admin.layouts.app')
@section('section', translate('Roles'))
@section('title', translate('Users'))
@section('container', 'container-max-xxl')

@section('content')
    @include('admin.roles.users.partials.counters')

    <x-datatable
        id="usersTable"
        :items="$userCount"
        :columns="$columns"
        :ajax-url="route('admin.roles.users.index')"
        :filters="$filters"
        :server-side="true"
        :title="translate('All Users')"
        :description="translate('Manage all users, roles, and verification statuses')"
        :search-placeholder="translate('Search User')"
        :custom-buttons="[
            [
                'text' => translate('Create User'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary',
                'type' => 'modal',
                'target' => '#createUserModal',
                'action' => route('admin.roles.users.create.modal')
            ],
            $trashedCount > 0 ? [
                'text' => translate('View Trash'),
                'link' => route('admin.roles.users.trash.index'),
                'icon' => 'bi-trash',
                'class' => 'btn btn-outline-secondary'
            ] : null
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.roles.users.bulk-delete'),
            'confirm' => translate('Are you sure you want to delete the selected users?'),
        ]"
        :empty-title="translate('No Users Found')"
        :empty-desc="translate('Maintain a healthy user ecosystem by managing accounts effectively.')"
        :empty-icon="'bi-people'"
        :empty-btn-modal="'#createUserModal'"
        :empty-btn-modal-text="translate('Create User')"
        :empty-btn-modal-action="route('admin.roles.users.create.modal')"
    >
    </x-datatable>

    <x-modal id="createUserModal" :header="false" size="lg" />
@endsection

