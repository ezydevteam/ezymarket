<x-modal
    id="editCategoryModal-{{ $category->id }}"
    size="md"
    :title="translate('Edit Ticket Category')"
    :icon="'bi bi-pencil-square'"
>
    <form id="editCategoryForm-{{ $category->id }}" action="{{ route('admin.tickets.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" value="{{ $category->name }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select form-select-lg selectpicker" required>
                    <option value="1" {{ $category->isActive() ? 'selected' : '' }}>{{ translate('Active') }}</option>
                    <option value="0" {{ !$category->isActive() ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                </select>
            </div>
        </div>
        <div class="modal-footer mt-4">
            <button type="button" class="btn btn-md btn-cancel" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" form="editCategoryForm-{{ $category->id }}" class="btn btn-md btn-primary ms-3" id="editCategoryBtn-{{ $category->id }}">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Update Category') }}
            </button>
        </div>
    </form>
</x-modal>
