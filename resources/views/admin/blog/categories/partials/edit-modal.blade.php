<x-modal
    id="editCategoryModal-{{ $category->id }}"
    size="md"
    :title="translate('Edit Blog Category')"
    :icon="'bi bi-pencil-square'"
    :autoOpen="true"
>
    <form id="editCategoryForm-{{ $category->id }}"
        action="{{ route('admin.blog.categories.update', $category->id) }}"
        method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" value="{{ $category->name }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Slug') }} <span class="text-danger">*</span></label>
                <input type="text" name="slug" class="form-control form-control-lg" value="{{ $category->slug }}" required>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-md btn-cancel flex-fill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
            </button>
            <button type="submit" form="editCategoryForm-{{ $category->id }}" class="btn btn-md btn-primary flex-fill ms-3" id="editCategoryBtn-{{ $category->id }}">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Update') }}
            </button>
        </x-slot>
    </form>
</x-modal>
