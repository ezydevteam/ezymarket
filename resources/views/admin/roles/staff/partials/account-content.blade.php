<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-lines-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Account Settings') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Update your personal information and account status')
                        }}</p>
                </div>
            </div>
            <button type="submit" form="staffAccountForm" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-save me-2"></i>{{ translate('Save Changes') }}
            </button>
        </div>

        <form action="{{ route('admin.roles.staff.update', $staff->id) }}" id="staffAccountForm" class="ajax-form"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="p-4 bg-light-subtle rounded-4 border">
                        <div class="d-flex align-items-center gap-4">
                            <div class="position-relative">
                                <img id="image-preview-0" src="{{ $staff->avatar_url }}" alt="{{ $staff->username }}"
                                    class="image-fluid image-xl rounded border border-3 border-white shadow-sm">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Profile
                                    Picture') }}</label>
                                <input type="file" name="avatar" class="form-control image-input" data-id="0"
                                    accept="image/png, image/jpg, image/jpeg">
                                <div class="form-text mt-2 small">
                                    <i class="bi bi-info-circle me-1"></i>{{ translate('Recommended: JPG, PNG
                                    (120x120px, Max 2MB)') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('First Name')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                        <input type="text" name="firstname" class="form-control border-0"
                            placeholder="{{ translate('Enter first name') }}" value="{{ $staff->firstname }}" required>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Last Name')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-person-check"></i></span>
                        <input type="text" name="lastname" class="form-control border-0"
                            placeholder="{{ translate('Enter last name') }}" value="{{ $staff->lastname }}" required>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Username')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-at"></i></span>
                        <input type="text" name="username" class="form-control border-0 fw-medium"
                            placeholder="{{ translate('Enter username') }}" value="{{ $staff->username }}" required>
                    </div>
                    <div class="form-text small">{{ translate('This will be used for staff login.') }}</div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('E-mail Address')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-0"
                            placeholder="{{ translate('Enter email address') }}" value="{{ hideInDemo($staff->email) }}"
                            required>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Assigned Role')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-shield-check"></i></span>
                        <input type="text" class="form-control border-0 bg-light" value="{{ $staff->role->label() }}"
                            readonly disabled>
                    </div>
                    <div class="form-text small">{{ translate('Can not change the role of an existing staff after
                        creation.') }}</div>
                </div>

                <div class="col-lg-6">
                    <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Account Status')
                        }}</label>
                    <div class="input-group border">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-toggle-on"></i></span>
                        <select name="status" class="form-select border-0 selectpicker" required>
                            <option value="1" @selected($staff->status == true)>{{ translate('Active') }}</option>
                            <option value="0" @selected($staff->status == false)>{{ translate('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="form-text small">{{ translate('Inactive staff members cannot access the dashboard.') }}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
