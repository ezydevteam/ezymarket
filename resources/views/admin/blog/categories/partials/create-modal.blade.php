<x-modal
    id="createCategoryModal"
    size="md"
    :title="translate('New Blog Category')"
    :icon="'bi bi-plus-circle'"
>
    <form id="createCategoryForm"
        action="{{ route('admin.blog.categories.store') }}"
        method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" id="create_slug" class="form-control form-control-lg" required autofocus>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Slug') }} <span class="text-danger">*</span></label>
                <input type="text" name="slug" id="show_slug" class="form-control form-control-lg" required>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-md btn-cancel flex-fill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" class="btn btn-md btn-primary ms-3 flex-fill" id="createCategoryBtn" form="createCategoryForm">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Create') }}
            </button>
        </x-slot>
    </form>
</x-modal>
