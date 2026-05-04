<x-modal :content-only="true" :title="translate('Create Sub Category')" icon="bi bi-plus-circle" :scrollable="true">
    <form id="createSubCategoryForm" class="ajax-form"
        action="{{ route('admin.products.categories.sub-categories.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Main Category') }}
                    <span class="text-danger">*</span>
                </label>
                <select name="category" class="form-select form-select-md selectpicker"
                    data-placeholder="{{ translate('Select main category') }}" data-live-search="true" data-size="8">
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category')==$category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="form-label">
                    {{ translate('Sub Category Name') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" id="create_slug" class="form-control form-control-md"
                    placeholder="{{ translate('e.g., WordPress Themes, Logo Templates') }}"
                    data-slug="{{ route('admin.products.categories.sub-categories.slug') }}" value="{{ old('name') }}" autofocus />
            </div>
            <div class="col-lg-6">
                <label class="form-label">
                    {{ translate('URL Slug') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="slug" id="show_slug" class="form-control form-control-md"
                    placeholder="{{ translate('auto-generated-from-name') }}" value="{{ old('slug') }}" />
            </div>
        </div>

        <hr class="my-4" />

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Meta Title') }}</label>
                <input type="text" name="title" class="form-control form-control-md"
                    placeholder="{{ translate('Custom title for search engines') }}" value="{{ old('title') }}"/>
                <div class="form-text">
                    {{ translate('Recommended length: 50-60 characters. Leave empty to use sub-category name.') }}
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Meta Description') }}</label>
                <textarea name="description" class="form-control" rows="4"
                    placeholder="{{ translate('Brief description for search engine results') }}">{{ old('description') }}</textarea>
                <div class="form-text">
                    {{ translate('Recommended length: 120-150 characters. Briefly describe what products are in this
                    sub-category.') }}
                </div>
            </div>
        </div>

    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-cancel text-uppercase flex-fill" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="createSubCategoryForm" id="createSubCategoryBtn"
            class="btn btn-primary text-uppercase flex-fill">
            <i class="bi bi-check2-circle me-2"></i>{{ translate('Create') }}
        </button>
        </x-slot>
</x-modal>
