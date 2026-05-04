<x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
    <x-dropdown.item
        href="{{ $subCategory->view_link }}"
        target="_blank"
        icon="bi bi-eye"
        class="me-2">
        {{ translate('Preview') }}
    </x-dropdown.item>
    <x-dropdown.item
        type="button"
        class="ajax-modal"
        data-action="{{ route('admin.products.categories.sub-categories.edit.modal', $subCategory->id) }}"
        data-bs-toggle="modal"
        data-bs-target="#editSubCategoryModal"
        icon="bi bi-pencil-square"
        iconClass="me-2">
        {{ translate('Edit Details') }}
    </x-dropdown.item>
    <x-dropdown.item type="divider" />
    <x-dropdown.item
        type="button"
        data-action="{{ route('admin.products.categories.sub-categories.destroy', $subCategory->id) }}"
        icon="bi bi-trash"
        class="text-danger action-confirm"
        data-method="DELETE"
        data-confirm="{{ translate('Are you sure want to delete this sub-category? This action can not be undone.') }}">
        {{ translate('Delete') }}
    </x-dropdown.item>
</x-dropdown>
