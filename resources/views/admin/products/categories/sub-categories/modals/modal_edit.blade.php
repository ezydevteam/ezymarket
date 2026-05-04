<x-modal :content-only="true" :title="translate('Edit Sub Category')" icon="bi bi-pencil-square" :scrollable="true">
    <form id="editSubCategoryForm-{{ $subCategory->id }}" class="ajax-form"
        action="{{ route('admin.products.categories.sub-categories.update', $subCategory->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Main Category') }}
                </label>
                <select class="form-select form-select-md" disabled>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($category->id == $subCategory->category->id)>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <div class="form-text">
                    {{ translate('The parent category cannot be changed.') }}
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Sub Category Name') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" class="form-control form-control-md" id="create_slug"
                    placeholder="{{ translate('Enter sub category name') }}" value="{{ $subCategory->name }}"
                    data-slug="{{ route('admin.products.categories.sub-categories.slug') }}"
                    required />
            </div>
            <div class="col-12">
                <label class="form-label">
                    {{ translate('URL Slug') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="slug" class="form-control form-control-md" id="show_slug"
                    placeholder="{{ translate('auto-generated-from-name') }}" value="{{ $subCategory->slug }}"
                    required />
            </div>
        </div>
        <hr class="my-4" />
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Meta Title') }}</label>
                <input type="text" name="title" class="form-control form-control-md"
                    placeholder="{{ translate('Custom title for search engines') }}"
                    value="{{ $subCategory->title }}" />
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Meta Description') }}</label>
                <textarea name="description" class="form-control" rows="4"
                    placeholder="{{ translate('Brief description for search engine results') }}">{{ $subCategory->description }}</textarea>
            </div>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-cancel text-uppercase flex-fill" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="editSubCategoryForm-{{ $subCategory->id }}"
            class="btn btn-primary text-uppercase flex-fill">
            <i class="bi bi-check2-circle me-2"></i>{{ translate('Save Changes') }}
        </button>
    </x-slot:footer>
</x-modal>
