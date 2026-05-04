<x-modal :content-only="true" :title="translate('Create New User')" :icon="'bi-person-plus'" :scrollable="true">

    <form id="createUserForm" action="{{ route('admin.roles.users.store') }}" class="ajax-form" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="firstname" class="form-label">{{ translate('First Name') }} <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" id="firstname" name="firstname" required>
            </div>
            <div class="col-md-6">
                <label for="lastname" class="form-label">{{ translate('Last Name') }} <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" id="lastname" name="lastname" required>
            </div>
            <div class="col-12">
                <label for="username" class="form-label">{{ translate('Username') }} <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" required minlength="6">
                <small class="text-muted">{{ translate('Minimum 6 characters, letters, numbers, dashes and underscores
                    only') }}</small>
            </div>
            <div class="col-12">
                <label for="email" class="form-label">{{ translate('Email') }} <span
                        class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="col-12">
                <label for="password" class="form-label">{{ translate('Password') }} <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control generate-password-input" name="password" required
                        minlength="8" data-auto-generate="true">
                    <button type="button" class="btn btn-light generate-password-btn">
                        <i class="bi bi-arrow-clockwise"></i> {{ translate('Generate') }}
                    </button>
                </div>
                <small class="text-muted">{{ translate('Minimum 8 characters') }}</small>
            </div>
            <div class="col-12">
                <div class="row align-items-center">
                    <div class="col-7">
                        <label class="form-label fs-6 mb-0 fw-semibold">{{ translate('Create as Seller') }}</label>
                        <p class="text-muted small mb-0">{{ translate('Enable seller privileges for this user') }}</p>
                    </div>
                    <div class="col-5 text-end">
                        <div class="ezydev-switch-wrapper-lg">
                            <input type="checkbox" class="ezydev-switch-input" id="seller" name="seller" value="1">
                            <label class="ezydev-switch-label" for="seller">
                                <span class="ezydev-switch-slider">
                                    <span class="ezydev-switch-button">
                                        <span class="ezydev-switch-on">{{ translate('Yes') }}</span>
                                        <span class="ezydev-switch-off">{{ translate('No') }}</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill text-uppercase" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="createUserForm" class="btn btn-primary flex-fill text-uppercase">
            <i class="bi bi-check2-circle me-2"></i>{{ translate('Create') }}
        </button>
        </x-slot>
</x-modal>
