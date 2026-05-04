<x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
    <x-dropdown.item
        href="{{ route('admin.products.updated.show', $update->id) }}"
        icon="bi bi-eye"
        iconClass="me-2">
        {{ translate('View Details') }}
    </x-dropdown.item>

    @if ($update->main_file)
        <x-dropdown.item
            href="{{ $update->isMainFileExternal() ? ($update->main_file['path'] ?? '') : route('admin.products.updated.download', $update->id) }}"
            icon="bi bi-download"
            iconClass="me-2"
            :target="$update->isMainFileExternal() ? '_blank' : '_self'">
            {{ translate('Download File') }}
        </x-dropdown.item>
    @endif

    @if($update->product)
        <x-dropdown.item
            href="{{ route('admin.products.show', $update->product->id) }}"
            icon="bi bi-box-arrow-up-right"
            iconClass="me-2"
            target="_blank">
            {{ translate('Original Product') }}
        </x-dropdown.item>
    @endif

    <x-dropdown.item type="divider" />

    <x-dropdown.item
        type="button"
        icon="bi bi-trash"
        color="danger"
        class="action-confirm"
        data-method="DELETE"
        data-action="{{ route('admin.products.updated.destroy', $update->id) }}"
        data-text="{{ translate('Are you sure you want to delete this update?') }}">
        {{ translate('Delete') }}
    </x-dropdown.item>
</x-dropdown>
