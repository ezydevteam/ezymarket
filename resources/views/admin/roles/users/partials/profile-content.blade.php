<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Profile Details') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Personalize user public appearance and social
                        presence') }}</p>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4" form="profileUpdateForm">
                <i class="bi bi-save me-1"></i>
                {{ translate('Save Changes') }}
            </button>
        </div>

        <form id="profileUpdateForm" action="{{ route('admin.roles.users.profile.update', $user->id) }}" method="POST"
            enctype="multipart/form-data" class="ajax-form">
            @csrf
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="p-4 border border rounded-4 bg-light-subtle h-100">
                        <h6 class="fw-bold mb-3">{{ translate('Avatar Image') }}</h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="avatar-preview d-flex justify-content-center">
                                <img id="image-preview-0" class="rounded-pill border p-1 bg-white shadow-sm"
                                    src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" width="100" height="100"
                                    style="object-fit: cover;">
                            </div>
                            <div class="avatar-input">
                                <input type="file" name="avatar" class="form-control image-input" data-id="0"
                                    accept="image/png, image/jpg, image/jpeg">
                                <div class="form-text mt-2 small">
                                    {{ translate('Allowed types: JPG, JPEG, PNG. Recommended: 140x140px') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 border border rounded-4 bg-light-subtle h-100">
                        <h6 class="fw-bold mb-3">{{ translate('Profile Cover') }}</h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="cover-preview w-100">
                                <img id="image-preview-1" class="rounded-3 border p-1 bg-white shadow-sm w-100"
                                    src="{{ $user->profile_cover_url ?: asset('default/cover.png') }}"
                                    alt="{{ $user->full_name }}" height="100" style="object-fit: cover;">
                            </div>
                            <input type="file" name="cover" class="form-control image-input" data-id="1"
                                accept="image/png, image/jpg, image/jpeg">
                            <div class="form-text small">
                                {{ translate('Allowed types: JPG, JPEG, PNG. Recommended: 1920x180px') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">{{ translate('Profile Heading') }}</label>
                    <input type="text" name="heading" class="form-control"
                        value="{{ $user->basic_info['heading'] ?? '' }}"
                        placeholder="{{ translate('I am a creative designer...') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">{{ translate('About / Biography') }}</label>
                    <textarea name="bio" rows="4" class="ckeditor-sm ckeditor"
                        id="profile-bio-editor">{{ $user->basic_info['bio'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="card border-0 rounded-4 mb-4">
                <div class="card-header p-0 mb-4 border-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>{{ translate('Personal Info') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Timezone') }}</label>
                            <select name="basic_info[timezone]" class="form-select selectpicker"
                                placeholder="{{ translate('Select Timezone') }}" data-live-search="true" data-size="10">
                                @foreach(timezones() as $timezone => $label)
                                <option value="{{ $timezone }}" {{ ($user->basic_info['timezone'] ?? '') == $timezone ?
                                    'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Preferred Language') }}</label>
                            <select name="basic_info[language]" class="form-select selectpicker"
                                placeholder="{{ translate('Select Language') }}" data-live-search="true" data-size="10">
                                @foreach(languages() as $code => $name)
                                <option value="{{ $code }}" {{ ($user->basic_info['language'] ?? '') == $code ?
                                    'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Birth Date') }}</label>
                            <input type="date" name="basic_info[birth_date]" class="form-control"
                                value="{{ !empty($user->basic_info['birth_date']) ? \Carbon\Carbon::parse($user->basic_info['birth_date'])->format('Y-m-d') : '' }}"
                                placeholder="dd-mm-yyyy" autocomplete="off">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Gender') }}</label>
                            <select name="basic_info[gender]" class="form-select selectpicker">
                                <option value="">{{ translate('Select Gender') }}</option>
                                <option value="male" @selected(($user->basic_info['gender'] ?? '') == 'male')>{{
                                    translate('Male') }}</option>
                                <option value="female" @selected(($user->basic_info['gender'] ?? '') == 'female')>{{
                                    translate('Female') }}</option>
                                <option value="other" @selected(($user->basic_info['gender'] ?? '') == 'other')>{{
                                    translate('Other') }}</option>
                                <option value="prefer_not_to_say" @selected(($user->basic_info['gender'] ?? '') ==
                                    'prefer_not_to_say')>{{ translate('Prefer not to say') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Hobby') }}</label>
                            <input type="text" name="basic_info[hobby]" class="form-control"
                                placeholder="{{ translate('Reading, Music...') }}"
                                value="{{ $user->basic_info['hobby'] ?? '' }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Profession') }}</label>
                            <input type="text" name="basic_info[profession]" class="form-control"
                                placeholder="{{ translate('Software Developer') }}"
                                value="{{ $user->basic_info['profession'] ?? '' }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Company') }}</label>
                            <input type="text" name="basic_info[company]" class="form-control"
                                placeholder="{{ translate('Company Name') }}"
                                value="{{ $user->basic_info['company'] ?? '' }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Nationality') }}</label>
                            <select name="basic_info[nationality]" class="form-select selectpicker"
                                placeholder="{{ translate('Select Nationality') }}" data-live-search="true"
                                data-size="10">
                                @foreach (countries() as $countryCode => $countryName)
                                <option value="{{ $countryCode }}" @selected(($user->basic_info['nationality'] ?? '') ==
                                    $countryCode)>
                                    {{ $countryName }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Website') }}</label>
                            <input type="url" name="basic_info[website]" class="form-control"
                                placeholder="https://example.com" value="{{ $user->basic_info['website'] ?? '' }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">{{ translate('Business Email') }}</label>
                            <input type="email" name="basic_info[business_email]" class="form-control"
                                placeholder="business@example.com"
                                value="{{ $user->basic_info['business_email'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 rounded-4">
                <div class="card-header border-0 p-0 mb-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-share me-2"></i>{{ translate('Social Connections') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="row g-3">
                        @php $socials = getSocialPlatforms(); @endphp
                        @foreach ($socials as $social)
                        <div class="col-lg-4">
                            <label class="form-label small fw-bold">{{ translate($social['label']) }}</label>
                            <div class="input-group border">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi {{ $social['icon'] }} fs-6"></i></span>
                                <input type="text" name="social_links[{{ $social['name'] }}]"
                                    class="form-control border-0 ps-2" placeholder="{{ translate('Username') }}"
                                    value="{{ $user->basic_info[$social['name']] ?? '' }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
