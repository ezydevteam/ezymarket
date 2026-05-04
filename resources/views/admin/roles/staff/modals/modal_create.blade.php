<x-modal :content-only="true" :title="translate('Create New Staff')" :icon="'bi-person-plus'">

    <form id="createAdminStaffForm" action="{{ route('admin.roles.staff.store') }}" class="ajax-form" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="firstname" class="form-label">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="firstname" name="firstname" required>
            </div>
            <div class="col-md-6">
                <label for="lastname" class="form-label">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="lastname" name="lastname" required>
            </div>
            <div class="col-md-6">
                <label for="username" class="form-label">{{ translate('Username') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" required minlength="6">
                <small class="text-muted">{{ translate('Minimum 6 characters') }}</small>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">{{ translate('Email') }} <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Role') }} <span class="text-danger">*</span></label>
                <select name="role" id="roleSelect" class="form-select selectpicker" title="{{ translate('Choose role') }}"
                    data-conditional-toggle="#categoriesField" data-conditional-value="reviewer" required>
                    @foreach ($roles as $roleValue => $roleData)
                        <option value="{{ $roleValue }}"
                            @selected(old('role') == $roleValue)>
                            {{ $roleData['label'] }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ translate('Select the appropriate role based on responsibilities.') }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select selectpicker" required>
                    <option value="1" @selected(old('status', '1') == '1')>{{ translate('Active') }}</option>
                    <option value="0" @selected(old('status') == '0')>{{ translate('Inactive') }}</option>
                </select>
                <div class="form-text">{{ translate('Active accounts can login, inactive accounts are suspended.') }}</div>
            </div>
            <div class="col-12 d-none" id="categoriesField">
                <label class="form-label">{{ translate('Assigned Categories') }} <span class="text-danger">*</span></label>
                <select name="categories[]" id="categoriesSelect" class="form-select selectpicker" data-live-search="true"
                    data-size="7" multiple title="{{ translate('Choose categories') }}">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old("categories.{$category->id}") == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ translate('Reviewers must be assigned to specific product categories.') }}</div>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">{{ translate('Password') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control generate-password-input" name="password" required minlength="8" data-auto-generate="true">
                    <button type="button" class="btn btn-light generate-password-btn">
                        <i class="bi bi-arrow-clockwise"></i> {{ translate('Generate') }}
                    </button>
                </div>
                <small class="text-muted">{{ translate('Minimum 8 characters') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Confirm Password') }} <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="{{ translate('Confirm password') }}" required minlength="8">
            </div>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-cancel text-uppercase flex-fill" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="createAdminStaffForm" id="createAdminStaffBtn" class="btn btn-primary text-uppercase flex-fill">
            <i class="bi bi-check2-circle me-2"></i>{{ translate('Create') }}
        </button>
    </x-slot>
</x-modal>
