<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.statements.details.modal', $statement->id) }}"
            data-bs-toggle="modal"
            data-bs-target="#earningDetailsModal"
            icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>

        <x-dropdown.item type="divider" />
        
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.statements.destroy', $statement->id) }}"
            icon="bi bi-trash"
            class="text-danger action-confirm"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure to delete this record?') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
