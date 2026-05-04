<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            type="button"
            class="text-success"
            data-action="{{ route('admin.products.trash.restore', $product->id) }}"
            data-text="{{ translate('Are you sure you want to restore this product?') }}"
            data-method="POST"
            icon="bi-arrow-counterclockwise"
            iconClass="text-success me-2">
            {{ translate('Restore') }}
        </x-dropdown.item>

        @if(superAdmin())
            <x-dropdown.item type="divider" />
            <x-dropdown.item
                type="button"
                class="text-danger action-confirm"
                data-action="{{ route('admin.products.trash.permanently-delete', $product->id) }}"
                data-text="{{ translate('Are you sure want to delete this product? This action cannot be undone!') }}"
                data-method="DELETE"
                icon="bi-trash"
                iconClass="me-2">
                {{ translate('Delete Permanently') }}
            </x-dropdown.item>
        @endif
    </x-dropdown>
</div>
