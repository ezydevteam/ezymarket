<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item type="button" data-bs-toggle="modal" data-bs-target="#detailsModal"
            data-action="{{ route('admin.financial.transactions.details.modal', $trx->id) }}" icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>
        @if ($isTrash)
            <x-dropdown.item type="button" data-action="{{ route('admin.financial.transactions.restore', $trx->id) }}"
                icon="bi bi-arrow-counterclockwise" class="text-success action-confirm" data-method="POST"
                data-text="{{ translate('Are you sure you want to restore this transaction?') }}">
                {{ translate('Restore') }}
            </x-dropdown.item>
            <x-dropdown.item type="divider" />
            <x-dropdown.item type="button"
                data-action="{{ route('admin.financial.transactions.trash.permanently-delete', $trx->id) }}"
                icon="bi bi-trash" class="text-danger action-confirm" data-method="DELETE"
                data-text="{{ translate('Are you sure you want to permanently delete this transaction? This action cannot be undone.') }}">
                {{ translate('Permanently Delete') }}
            </x-dropdown.item>
        @else
            <x-dropdown.item type="divider" />
            <x-dropdown.item type="button" data-action="{{ route('admin.financial.transactions.destroy', $trx->id) }}"
                icon="bi bi-trash" class="text-danger action-confirm" data-method="DELETE"
                data-text="{{ translate('Are you sure you want to delete this transaction?') }}">
                {{ translate('Delete') }}
            </x-dropdown.item>
        @endif
    </x-dropdown>
</div>
