@extends('admin.layouts.app')
@section('section', translate('Roles'))
@section('title', translate('Trashed Users'))

@section('content')
    <x-datatable
        id="trashedUsersTable"
        :items="$users"
        export="true"
        :title="translate('Trashed Users')"
        :description="translate('Restore or permanently delete trashed users')"
        :search-placeholder="translate('Search User')"
        :custom-buttons="[
            [
                'text' => translate('Active Users'),
                'icon' => 'bi-person-check',
                'class' => 'btn btn-outline-primary',
                'link' => route('admin.roles.users.index')
            ]
        ]"
        :empty-title="translate('No Trashed Users Found')"
        :empty-desc="translate('No users have been trashed yet.')"
        :empty-icon="'bi-trash'"
    >
        <thead>
            <tr>
                <th>{{ translate('ID') }}</th>
                <th>{{ translate('User Details') }}</th>
                <th class="text-center no-sort">{{ translate('Seller') }}</th>
                <th class="text-center">{{ translate('Deleted By') }}</th>
                <th class="text-center">{{ translate('Deleted Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>#{{ $user->id }}</td>
                    <td>
                       <x-user :user="$user" avatar-size="sm" />
                    </td>
                    <td class="text-center">
                        <span class="status-badge bg-{{ $user->isSeller() ? 'success' : 'dark' }}-subtle text-{{ $user->isSeller() ? 'success' : 'dark' }}">
                            {{ $user->isSeller() ? translate('Yes') : translate('No') }}
                        </span>
                    </td>
                    @php
                        $color_label = $user->deletedByAdmin && $user->deletedByAdmin->role === \App\Enums\Admin\AdminRole::ADMIN
                            ? 'primary' : ($user->deletedByAdmin && $user->deletedByAdmin->role === \App\Enums\Admin\AdminRole::MANAGER
                            ? 'orange' : 'danger');
                    @endphp
                    <td class="text-center">
                        <span class="status-badge bg-{{ $color_label }}-subtle text-{{ $color_label }}">
                            {{ $user->deletedByAdmin ? $user->deletedByAdmin->role_label : translate('Self-deleted') }}
                        </span>
                    </td>
                    <td class="text-center text-gray-800">
                        {{ dateFormat($user->deleted_at ?? null) }}
                        <p class="text-gray-600 fs-13 mb-0">{{ timeAgo($user->deleted_at ?? null) }}</p>
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    type="button"
                                    data-action="{{ route('admin.roles.users.trash.restore', $user->id) }}"
                                    icon="bi bi-arrow-counterclockwise"
                                    color="success"
                                    class="action-confirm"
                                    data-method="POST"
                                    data-text="{{ translate('Are you sure you want to restore this user?') }}">
                                    {{ translate('Restore') }}
                                </x-dropdown.item>
                                @if(authAdmin()->isAdmin())
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    data-action="{{ route('admin.roles.users.trash.permanently-delete', $user->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-text="{{ translate('Are you sure you want to permanently delete this user? This action cannot be undone!') }}">
                                    {{ translate('Delete Permanently') }}
                                </x-dropdown.item>
                                @endif
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection
