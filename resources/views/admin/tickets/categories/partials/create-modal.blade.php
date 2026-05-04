<x-modal
    id="createCategoryModal"
    size="md"
    :title="translate('New Ticket Category')"
    :icon="'bi bi-folder-plus'"
>
    <form id="createCategoryForm" action="{{ route('admin.tickets.categories.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select form-select-lg selectpicker" required>
                    <option value="1" selected>{{ translate('Active') }}</option>
                    <option value="0">{{ translate('Inactive') }}</option>
                </select>
            </div>
        </div>
        <div class="modal-footer mt-4">
            <button type="button" class="btn btn-md btn-cancel" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" class="btn btn-md btn-primary ms-3" id="createCategoryBtn">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Create Category') }}
            </button>
        </div>
    </form>
</x-modal>
