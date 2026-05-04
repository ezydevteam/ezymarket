<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            href="{{ $user->profile_link }}"
            target="_blank"
            icon="bi bi-box-arrow-up-right"
            iconClass="me-2">
            {{ translate('View Profile') }}
        </x-dropdown.item>
        <x-dropdown.item
            href="{{ route('admin.roles.users.edit', $user->id) }}"
            icon="bi bi-eye"
            iconClass="me-2">
            {{ translate('View Details') }}
        </x-dropdown.item>
        @if(authAdmin()->canManageSystem())
        <x-dropdown.item
            href="{{ route('admin.roles.users.login', $user->id) }}"
            target="_blank"
            icon="bi bi-box-arrow-right"
            iconClass="me-2">
            {{ translate('Login as User') }}
        </x-dropdown.item>
        @if ($user->isSeller())
            <x-dropdown.item type="divider" />
            @if ($user->isFeaturedSeller())
                <x-dropdown.item
                    type="button"
                    icon="bi bi-award-fill"
                    color="danger"
                    class="action-confirm"
                    data-method="POST"
                    data-action="{{ route('admin.roles.users.featured.remove', $user->id) }}"
                    data-confirm="{{ translate('Are you sure you want to remove featured status?') }}">
                    {{ translate('Remove Featured') }}
                </x-dropdown.item>
            @else
                <x-dropdown.item
                    type="button"
                    icon="bi bi-award"
                    color="success"
                    class="action-confirm"
                    data-method="POST"
                    data-action="{{ route('admin.roles.users.featured', $user->id) }}"
                    data-confirm="{{ translate('Are you sure you want to make this user featured?') }}">
                    {{ translate('Make Featured') }}
                </x-dropdown.item>
            @endif
        @endif
        <x-dropdown.item type="divider" />
        <x-dropdown.item
            type="button"
            icon="bi bi-trash"
            color="danger"
            class="action-confirm"
            data-method="DELETE"
            data-action="{{ route('admin.roles.users.destroy', $user->id) }}"
            data-confirm="{{ translate('Are you sure you want to delete this user?') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
        @endif
    </x-dropdown>
</div>
