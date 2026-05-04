<x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
    <x-dropdown.item
        href="{{ $category->view_link }}"
        target="_blank"
        icon="bi bi-eye"
        iconClass="me-2">
        {{ translate('Preview') }}
    </x-dropdown.item>
    <x-dropdown.item
        href="{{ route('admin.products.categories.edit', $category->id) }}"
        icon="bi bi-pencil-square"
        iconClass="me-2">
        {{ translate('Edit Details') }}
    </x-dropdown.item>
    <x-dropdown.item
        href="{{ route('admin.products.categories.sub-categories.index', 'category=' . $category->id) }}"
        icon="bi bi-folder-plus"
        iconClass="me-2">
        {{ translate('Sub Categories') }}
    </x-dropdown.item>
    <x-dropdown.item type="divider" />
    <x-dropdown.item
        type="button"
        icon="bi bi-trash"
        class="text-danger action-confirm"
        data-action="{{ route('admin.products.categories.destroy', $category->id) }}"
        data-method="DELETE"
        data-confirm="{{ translate('Are you sure want to delete this category? This action can not be undone.') }}">
        {{ translate('Delete') }}
    </x-dropdown.item>
</x-dropdown>
