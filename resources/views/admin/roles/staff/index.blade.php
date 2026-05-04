@extends('admin.layouts.app')
@section('section', translate('Roles'))
@section('title', translate('Staff Members'))
@section('container', 'container-max-xxl')

@section('content')
    <x-datatable
        id="adminStaffTable"
        :items="$staff"
        export="true"
        :title="translate('Staff Members')"
        :description="translate('Manage staff members, roles, and verification statuses')"
        :search-placeholder="translate('Search Staff Member')"
        :custom-buttons="[
            [
                'text' => translate('Create Staff'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary',
                'type' => 'modal',
                'target' => '#createAdminStaffModal',
                'action' => route('admin.roles.staff.create.modal')
            ]
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.roles.staff.bulk-delete'),
            'confirm' => translate('Are you sure you want to delete the selected staff members?'),
        ]"
        :empty-title="translate('No Staff Members Found')"
        :empty-desc="translate('Create your first staff member to get started')"
        :empty-icon="'bi-people'"
        :empty-btn-modal="'#createAdminStaffModal'"
        :empty-btn-modal-text="translate('Create Staff')"
        :empty-btn-modal-action="route('admin.roles.staff.create.modal')">
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Details') }}</th>
                <th class="text-center">{{ translate('Role') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staff as $member)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $member->id }}">
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <a class="image-fluid image-md rounded"
                                href="{{ route('admin.roles.staff.edit', $member->id) }}">
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->username }}" />
                            </a>
                            <div>
                                <a class="text-reset fw-medium hover-primary"
                                    href="{{ route('admin.roles.staff.edit', $member->id) }}">{{ $member->full_name }}</a>
                                <p class="text-muted small mb-0">{{ hideInDemo($member->email) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="status-badge bg-{{ $member->role->color() }}-subtle text-{{ $member->role->color() }}">
                            {{ $member->role_label }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge bg-{{ $member->status ? 'success' : 'danger' }}-subtle text-{{ $member->status ? 'success' : 'danger' }}">
                            {{ $member->status ? translate('Active') : translate('Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ dateFormat($member->created_at) }}
                        <p class="text-muted small mb-0">{{ timeAgo($member->created_at) }}</p>
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ route('admin.roles.staff.edit', $member->id) }}"
                                    icon="bi bi-eye"
                                    iconClass="me-2">
                                    {{ translate('View Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    type="button"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-action="{{ route('admin.roles.staff.destroy', $member->id) }}"
                                    data-method="DELETE"
                                    data-text="{{ translate('Are you sure you want to delete this staff? This action cannot be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    <x-modal id="createAdminStaffModal" :header="false" size="lg" />
@endsection
