<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.purchases.details.modal', $purchase->id) }}"
            data-bs-toggle="modal"
            data-bs-target="#{{ request()->query('purchase') == $purchase->id ? 'purchaseDetailsModal-' . $purchase->id : 'purchaseDetailsModal' }}"
            icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>

        <x-dropdown.item type="divider" />

        <a href="{{ route('admin.financial.transactions.index', ['trx' => $purchase->sale?->transaction_id]) }}" class="dropdown-item">
            <i class="bi bi-receipt me-2"></i>{{ translate('View Transaction') }}
        </a>
    </x-dropdown>
</div>
