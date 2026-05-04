<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#detailsModal"
            data-action="{{ route('admin.financial.payouts.details.modal', $payout->id) }}"
            icon="bi bi-eye">
            {{ translate($payout->isPending() ? 'Take Action' : 'View Details') }}
        </x-dropdown.item>
        
        @if($isTrash)
            <x-dropdown.item
                type="button"
                data-action="{{ route('admin.financial.payouts.restore', $payout->id) }}"
                icon="bi bi-arrow-counterclockwise"
                class="text-success action-confirm"
                data-method="POST"
                data-text="{{ translate('Are you sure you want to restore this payout request?') }}">
                {{ translate('Restore') }}
            </x-dropdown.item>
            <x-dropdown.item
                type="button"
                data-action="{{ route('admin.financial.payouts.trash.permanently-delete', $payout->id) }}"
                icon="bi bi-trash"
                class="text-danger action-confirm"
                data-method="DELETE"
                data-text="{{ translate('Are you sure you want to permanently delete this payout request? This action cannot be undone.') }}">
                {{ translate('Permanently Delete') }}
            </x-dropdown.item>
        @else
            <x-dropdown.item type="divider" />
            <x-dropdown.item
                type="button"
                data-action="{{ route('admin.financial.payouts.destroy', $payout->id) }}"
                icon="bi bi-trash"
                class="text-danger action-confirm"
                data-method="DELETE"
                data-text="{{ translate('Are you sure you want to delete this payout request?') }}">
                {{ translate('Delete') }}
            </x-dropdown.item>
        @endif
    </x-dropdown>
</div>
