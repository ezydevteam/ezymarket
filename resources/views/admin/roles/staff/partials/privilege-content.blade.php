<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-folder-check fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Category Privileges') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Assign specific product categories to this reviewer')
                        }}</p>
                </div>
            </div>
            <button type="submit" form="staffPrivilegeForm" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-save me-2"></i>{{ translate('Save Changes') }}
            </button>
        </div>

        <form action="{{ route('admin.roles.staff.privilege.update', $staff) }}" id="staffPrivilegeForm"
            class="ajax-form" method="POST">
            @csrf
            <div class="selectpicker-checkbox-wrapper p-3 bg-light-subtle rounded-4 border">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                    <div class="selectpicker-checkbox-btn">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                            id="category_{{ $category->id }}" class="selectpicker-checkbox-input"
                            @checked(in_array($category->id, $staffCategoryIds ?? []))>
                        <label class="selectpicker-checkbox-label rounded-pill px-3 py-2"
                            for="category_{{ $category->id }}">
                            {{ $category->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <p class="text-muted mb-0 mt-4">
                <span class="status-badge bg-primary-subtle text-primary fw-medium me-1 selected-count">
                    {{ count($staffCategoryIds ?? []) }}
                </span>
                {{ translate('categories selected.') }}
            </p>
        </form>
    </div>
</div>
