<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            data-action="{{ route('admin.records.support-earnings.details.modal', $earning->id) }}"
            data-bs-toggle="modal"
            data-bs-target="#{{ request()->query('id') == $earning->id ? 'earningDetailsModal-' . $earning->id : 'earningDetailsModal' }}"
            icon="bi bi-eye">
            {{ translate('View Details') }}
        </x-dropdown.item>

        <x-dropdown.item type="divider" />

        <a href="{{ route('admin.records.purchases.index', ['id' => $earning->purchase_id]) }}" class="dropdown-item">
            <i class="bi bi-bag-check me-2"></i>{{ translate('View Purchase') }}
        </a>
    </x-dropdown>
</div>
