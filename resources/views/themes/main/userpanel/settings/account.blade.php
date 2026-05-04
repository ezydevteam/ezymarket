@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Account'))

@section('content')
    <div class="ajax-tabs">
        @themeInclude('userpanel.settings.includes.tabs-nav')
        <div class="ajax-tabs-content">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body px-4">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-4 pb-2 border-bottom-dashed">
                        <div>
                            <h5 class="card-title mb-0">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-person-gear fs-5"></i>
                                </span>
                                {{ translate('Account details') }}
                            </h5>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-3" form="accountUpdateForm">
                                <i class="bi bi-save me-1"></i>
                                {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('user.settings.account.update') }}" method="POST" class="ajax-form" id="accountUpdateForm">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label fw-medium">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" class="form-control"
                                    value="{{ $user->firstname }}" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label fw-medium">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" class="form-control"
                                    value="{{ $user->lastname }}" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label fw-medium">{{ translate('Username') }}</label>
                                <input type="text" name="username" class="form-control bg-light" value="{{ $user->username }}"
                                    disabled>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6">
                                <label class="form-label fw-medium">{{ translate('Email address') }}
                                    <span class="text-danger">*</span>
                                    <span class="text-gray-600 fs-12 fw-normal">({{ translate('Primary') }})</span>
                                </label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6">
                                <label class="form-label fw-medium">{{ translate('Phone Number') }}</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ $user->phone ?? '' }}" placeholder="+1234567890">
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-medium">{{ translate('Address line 1') }} <span class="text-danger">*</span></label>
                                <input type="text" name="address_line_1" class="form-control"
                                    value="{{ $user->address['line_1'] ?? '' }}" required>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-medium">{{ translate('Address line 2') }}</label>
                                <input type="text" name="address_line_2" class="form-control"
                                    value="{{ $user->address['line_2'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-medium">{{ translate('City') }} <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control"
                                    value="{{ $user->address['city'] ?? '' }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-medium">{{ translate('State') }} <span class="text-danger">*</span></label>
                                <input type="text" name="state" class="form-control"
                                    value="{{ $user->address['state'] ?? '' }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-medium">{{ translate('Postal code') }} <span class="text-danger">*</span></label>
                                <input type="text" name="zip" class="form-control"
                                    value="{{ $user->address['zip'] ?? '' }}" required>
                            </div>
                            <div class="{{ $user->isSeller() ? 'col-md-6' : 'col-12' }}">
                                <label class="form-label fw-medium">{{ translate('Country') }} <span class="text-danger">*</span></label>
                                <select name="country" class="form-select selectpicker" data-live-search="true"
                                    placeholder="{{ translate('Select Country') }}" data-size="10" required>
                                    @foreach (countries() as $countryCode => $countryName)
                                        <option value="{{ $countryCode }}" @selected($countryCode == ($user->address['country'] ?? ''))>
                                            {{ $countryName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($user->isSeller())
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Seller Type') }}</label>
                                    <select name="seller_type" class="form-select selectpicker">
                                        <option value="exclusive" @selected($user->isExclusiveSeller())>
                                            {{ translate('Exclusive') }}
                                        </option>
                                        <option value="non_exclusive" @selected(!$user->isExclusiveSeller())>
                                            {{ translate('Non Exclusive') }}
                                        </option>
                                    </select>
                                    <div class="form-text text-gray-600">
                                        {{ translate('You will be awarded an exclusive Seller badge') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

