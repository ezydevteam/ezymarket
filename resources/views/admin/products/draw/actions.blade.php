<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        <x-dropdown.item
            href="{{ route('admin.products.show', $product->id) }}"
            icon="bi bi-eye"
            iconClass="me-2">
            {{ translate('View Details') }}
        </x-dropdown.item>

        @if($product->isPendingReview())
            <x-dropdown.item
                href="{{ route('admin.products.actions.index', $product->id) }}"
                color="success"
                icon="bi bi-shield-check"
                iconClass="me-2">
                {{ translate('Review') }}
            </x-dropdown.item>
        @endif

        <x-dropdown.item type="divider" />

        <x-dropdown.item
            type="button"
            icon="bi bi-trash"
            color="danger"
            class="action-confirm"
            data-method="DELETE"
            data-action="{{ route('admin.products.soft-delete', $product->id) }}"
            data-confirm="{{ translate('Are you sure you want to delete this product?') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
