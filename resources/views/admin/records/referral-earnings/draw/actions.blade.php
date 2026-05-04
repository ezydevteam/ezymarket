<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.referral-earnings.details.modal', $earning->id) }}"
            data-bs-toggle="modal"
            data-bs-target="#{{ request()->query('id') == $earning->id ? 'earningDetailsModal-' . $earning->id : 'earningDetailsModal' }}"
            icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if($earning->sale)
            <x-dropdown.item type="divider" />
            
            <a href="{{ route('admin.records.sales.index', ['id' => $earning->sale_id]) }}" class="dropdown-item">
                <i class="bi bi-bag-check me-2"></i>{{ translate('View Sale') }}
            </a>
        @endif

        <x-dropdown.item type="divider" />

        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.referral-earnings.destroy', $earning->id) }}"
            icon="bi bi-trash"
            class="text-danger action-confirm"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure to delete this record? This action cannot be undone.') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
