<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-gear fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Account Details') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Manage user account and contact information') }}</p>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary px-4" form="updateAccountForm">
                    <i class="bi bi-save me-1"></i>
                    {{ translate('Save Changes') }}
                </button>
            </div>
        </div>

        <form id="updateAccountForm" action="{{ route('admin.roles.users.update', $user->id) }}" method="POST"
            class="ajax-form">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('First Name') }} <span
                            class="text-danger">*</span></label>
                    <input type="text" name="firstname" class="form-control" value="{{ $user->firstname }}" required>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Last Name') }} <span
                            class="text-danger">*</span></label>
                    <input type="text" name="lastname" class="form-control" value="{{ $user->lastname }}" required>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Username') }} <span
                            class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="is_id_verified" value="0">
                        <input id="idVerifiedSwitch" class="form-check-input verification-switch" type="checkbox"
                            name="is_id_verified" value="1" {{ $user->isIdVerified() ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="idVerifiedSwitch">
                            <span class="switch-label">{{ $user->isIdVerified() ? translate('Marked as Identity
                                Verified') : translate('Mark as Identity Verified') }}</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('E-mail Address') }} <span
                            class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ hideInDemo($user->email) }}"
                        required>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="email_status" value="0">
                        <input id="emailVerifiedSwitch" class="form-check-input verification-switch" type="checkbox"
                            name="email_status" value="1" {{ $user->isEmailVerified() ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="emailVerifiedSwitch">
                            <span class="switch-label">{{ $user->isEmailVerified() ? translate('Marked as Email
                                Verified') : translate('Mark as Email Verified') }}</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label fw-bold">{{ translate('Address line 1') }}</label>
                    <input type="text" name="address_line_1" class="form-control"
                        value="{{ $user->address['line_1'] ?? '' }}">
                </div>
                <div class="col-lg-12">
                    <label class="form-label fw-bold">{{ translate('Address line 2') }}</label>
                    <input type="text" name="address_line_2" class="form-control"
                        placeholder="{{ translate('Address line 2') }}" value="{{ $user->address['line_2'] ?? '' }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">{{ translate('City') }}</label>
                    <input type="text" name="city" class="form-control" value="{{ $user->address['city'] ?? '' }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">{{ translate('State') }}</label>
                    <input type="text" name="state" class="form-control" value="{{ $user->address['state'] ?? '' }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">{{ translate('Postal code') }}</label>
                    <input type="text" name="zip" class="form-control" value="{{ $user->address['zip'] ?? '' }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Country') }}</label>
                    <select name="country" class="form-select selectpicker"
                        placeholder="{{ translate('Select Country') }}" data-live-search="true" data-size="10">
                        @foreach (countries() as $countryCode => $countryName)
                        <option value="{{ $countryCode }}" @selected(($user->address['country'] ?? '') == $countryCode)>
                            {{ $countryName }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ $user->phone ?? '' }}"
                        placeholder="+1234567890">
                </div>
                @if ($user->isSeller())
                <div class="col-lg-12">
                    <label class="form-label fw-bold">{{ translate('Seller Type') }}</label>
                    <select name="seller_type" class="form-select selectpicker"
                        placeholder="{{ translate('Select Seller Type') }}" data-live-search="true" data-size="5">
                        <option value="exclusive" @selected($user->isExclusiveSeller())>
                            {{ translate('Exclusive') }}
                        </option>
                        <option value="non_exclusive" @selected(!$user->isExclusiveSeller())>
                            {{ translate('Non Exclusive') }}
                        </option>
                    </select>
                    <div class="form-text small text-muted">
                        {{ translate('The user will be awarded an exclusive Seller badge') }}
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>
