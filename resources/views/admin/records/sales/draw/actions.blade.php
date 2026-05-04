<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.sales.details.modal', $sale->id) }}"
            data-bs-toggle="modal"
            data-bs-target="#{{ request()->query('sale') == $sale->id ? 'saleDetailsModal-' . $sale->id : 'saleDetailsModal' }}"
            icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if($sale->isActive())
            <x-dropdown.item
                type="button"
                data-action="{{ route('admin.records.sales.cancel', $sale->id) }}"
                icon="bi bi-x-circle"
                class="text-warning action-confirm"
                data-method="POST"
                data-text="{{ translate('Are you sure you want to cancel this sale? This will notify the buyer and seller.') }}">
                {{ translate('Cancel Sale') }}
            </x-dropdown.item>
        @endif

        <x-dropdown.item type="divider" />

        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.sales.destroy', $sale->id) }}"
            icon="bi bi-trash"
            class="text-danger action-confirm"
            data-method="DELETE"
            data-text="{{ translate('Are you sure you want to delete this sale record? This action cannot be undone.') }}">
            {{ translate('Delete Record') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
