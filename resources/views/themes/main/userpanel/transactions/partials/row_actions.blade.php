<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            href="#"
            data-bs-toggle="modal"
            data-bs-target="#transactionDetailsModal"
            data-action="{{ route('user.transaction.show', $trx->id) }}"
            icon="bi bi-eye"
            iconClass="tme-2">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if($trx->isPaid())
            <x-dropdown.item
                href="{{ route('user.transaction.invoice', $trx->id) }}"
                target="_blank"
                icon="bi bi-file-earmark-pdf"
                iconClass="me-2">
                {{ translate('Invoice') }}
            </x-dropdown.item>
        @endif

        <x-dropdown.item type="divider" />

        <x-dropdown.item
            href="{{ route('user.transaction.destroy', $trx->id) }}"
            icon="bi bi-trash"
            color="danger"
            class="action-confirm"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure you want to delete this record?') }}">
            {{ translate('Delete Record') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
