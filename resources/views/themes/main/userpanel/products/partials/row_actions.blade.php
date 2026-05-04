<div class="text-end">
    <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
        @if (!$product->isPending())
            <x-dropdown.item :href="route('user.product.edit', $product->id)" icon="bi-pencil-square">
                {{ translate('Edit Product') }}
            </x-dropdown.item>
        @endif

        @if ($product->isApproved() || $product->isRestricted())
            <x-dropdown.item :href="route('user.product.statistics', $product->id)" icon="bi-bar-chart-fill">
                {{ translate('Statistics') }}
            </x-dropdown.item>

            @if ($product->isMainFileExternal())
                <x-dropdown.item :href="$product->main_file['path'] ?? ''" target="_blank" icon="bi-download">
                    {{ translate('Download') }}
                </x-dropdown.item>
            @else
                <x-dropdown.item type="button" icon="bi-download"
                    data-action="{{ route('user.product.download', $product->id) }}" data-method="POST">
                    {{ translate('Download') }}
                </x-dropdown.item>
            @endif
        @endif

        <x-dropdown.item type="divider" />
        <x-dropdown.item type="button" color="danger" icon="bi-trash" class="action-confirm text-danger"
            data-action="{{ route('user.product.destroy', $product->id) }}" data-method="DELETE"
            data-text="{{ translate('Are you sure you want to delete this product?') }}">
            {{ translate('Delete') }}
        </x-dropdown.item>
    </x-dropdown>
</div>
