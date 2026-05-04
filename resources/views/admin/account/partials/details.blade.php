<div class="card h-100">
    <div class="card-header"><i class="bi bi-person-add me-2"></i>{{ translate('Details') }}</div>
    <div class="card-body p-4">
        <form action="{{ route('admin.account.details') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Avatar') }}</label>
                    <div class="p-4 border bg-light rounded-2">
                        <div class="image-fluid image-xl my-3">
                            <img id="image-preview-0" class="border p-2 bg-light"
                                src="{{ $admin->avatar_url }}" alt="{{ $admin->username }}">
                        </div>
                        <input type="file" name="avatar" class="form-control image-input"
                            data-id="0" accept="image/png, image/jpg, image/jpeg">
                        <div class="form-text mt-2">
                            {{ translate('Allowed types (JPG,JPEG,PNG) Size 120x120px') }}</div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="firstname" class="form-control" placeholder="{{ translate('Enter first name') }}"
                            value="{{ old('firstname', $admin->firstname) }}" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                        <input type="text" name="lastname" class="form-control" placeholder="{{ translate('Enter last name') }}"
                            value="{{ old('lastname', $admin->lastname) }}" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Username') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-at"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="{{ translate('Enter username') }}"
                            value="{{ old('username', $admin->username) }}" minlength="5" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('E-mail Address') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="{{ translate('Enter email address') }}"
                            value="{{ old('email', $admin->email) }}" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Role') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <input type="text" class="form-control" value="{{ $admin->role_label }}" disabled>
                    </div>
                    <div class="form-text">{{ $admin->role->description() }}</div>
                </div>
            </div>
            <button class="btn btn-primary btn-md mt-4">{{ translate('Save Changes') }}</button>
        </form>
    </div>
</div>
