@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings'))
@section('menu', translate('Profile'))
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
                                    <i class="bi bi-person-badge fs-5"></i>
                                </span>
                                {{ translate('Profile Details') }}
                            </h5>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-3" form="profileUpdateForm">
                                <i class="bi bi-save me-1"></i>
                                {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('user.settings.profile.update') }}" method="POST" enctype="multipart/form-data"
                        class="ajax-form" id="profileUpdateForm">
                        @csrf
                        {{-- Visual Identity Section --}}
                        <div class="row g-4 mb-5">
                            <div class="col-lg-5">
                                <div class="p-3 border rounded-4 bg-light h-100">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="icon-circle icon-circle-sm bg-white text-primary flex-shrink-0 shadow-sm">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold">{{ translate('Avatar') }}</h6>
                                    </div>
                                    <div class="text-center mb-3 py-2">
                                        <img id="image-preview-1" class="image-fluid image-xl border p-1 rounded bg-white shadow-sm"
                                             src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}">
                                    </div>
                                    <input type="file" name="avatar" class="form-control form-control-sm image-input" data-id="1"
                                        accept="image/png, image/jpg, image/jpeg">
                                    <div class="form-text text-center mt-2 fs-12">
                                        {{ translate('Allowed types: JPG, PNG. Size: 140x140px (5MB)') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="p-3 border rounded-4 bg-light h-100">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-circle icon-circle-sm bg-white text-primary flex-shrink-0 shadow-sm">
                                            <i class="bi bi-image"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold">{{ translate('Profile Cover') }}</h6>
                                    </div>
                                    <div class="text-center mb-3">
                                        <img id="image-preview-2" class="border p-1 rounded-3 bg-white shadow-sm w-100 object-fit-cover"
                                            src="{{ $user->profile_cover_url ?: asset('default/cover.png') }}" alt="{{ $user->full_name }}"
                                            style="height: 100px;">
                                    </div>
                                    <input type="file" name="cover" class="form-control form-control-sm image-input"
                                        data-id="2" accept="image/png, image/jpg, image/jpeg">
                                    <div class="form-text text-center mt-2 fs-12">
                                        {{ translate('Allowed types: JPG, PNG. Size: 1920x180px (10MB)') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Profile Content Section --}}
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold"><i class="bi bi-card-text me-1"></i> {{ translate('Heading') }}</label>
                                <input type="text" name="heading" class="form-control" value="{{ $user->basic_info['heading'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold"><i class="bi bi-card-text me-1"></i> {{ translate('Biography') }}</label>
                                <textarea name="bio" class="ckeditor">{{ $user->basic_info['bio'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- Basic Information Section --}}
                        <div class="border-top-dashed pt-4 mb-4">
                            <h6 class="fw-bold mb-4">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-person-lines-fill"></i>
                                </span>
                                {{ translate('Basic Information') }}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Birth Date') }}</label>
                                    <input type="date" name="basic_info[birth_date]" class="form-control"
                                        value="{{ $user->basic_info['birth_date'] ?? '' }}">
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Gender') }}</label>
                                    <select name="basic_info[gender]" class="form-select selectpicker" placeholder="{{ translate('Select Gender') }}">
                                        <option value="male" @selected(($user->basic_info['gender'] ?? '') == 'male')>{{ translate('Male') }}</option>
                                        <option value="female" @selected(($user->basic_info['gender'] ?? '') == 'female')>{{ translate('Female') }}</option>
                                        <option value="other" @selected(($user->basic_info['gender'] ?? '') == 'other')>{{ translate('Other') }}</option>
                                        <option value="prefer_not_to_say" @selected(($user->basic_info['gender'] ?? '') == 'prefer_not_to_say')>{{ translate('Prefer not to say') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Hobby') }}</label>
                                    <input type="text" name="basic_info[hobby]" class="form-control"
                                        placeholder="{{ translate('Reading') }}" value="{{ $user->basic_info['hobby'] ?? '' }}">
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Profession') }}</label>
                                    <input type="text" name="basic_info[profession]" class="form-control"
                                        placeholder="{{ translate('Software Developer') }}" value="{{ $user->basic_info['profession'] ?? '' }}">
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Company') }}</label>
                                    <input type="text" name="basic_info[company]" class="form-control"
                                        placeholder="{{ translate('Company Name') }}" value="{{ $user->basic_info['company'] ?? '' }}">
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Website') }}</label>
                                    <input type="url" name="basic_info[website]" class="form-control"
                                        placeholder="https://example.com" value="{{ $user->basic_info['website'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ translate('Business Email') }}</label>
                                    <input type="email" name="basic_info[business_email]" class="form-control"
                                        value="{{ $user->basic_info['business_email'] ?? '' }}" placeholder="business@example.com">
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Timezone') }}</label>
                                    <select name="basic_info[timezone]" class="form-select selectpicker"
                                        placeholder="{{ translate('Select Timezone') }}" data-live-search="true" data-size="10">
                                        @foreach (timezones() as $timezoneKey => $timezoneValue)
                                            <option value="{{ $timezoneKey }}" @selected($timezoneKey == ($user->basic_info['timezone'] ?? ''))>
                                                {{ $timezoneValue }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Nationality') }}</label>
                                    <select name="basic_info[nationality]" class="form-select selectpicker"
                                        placeholder="{{ translate('Select Nationality') }}" data-live-search="true" data-size="10">
                                        @foreach (nationalities() as $code => $nationality)
                                            <option value="{{ $code }}" @selected(($user->basic_info['nationality'] ?? '') == $code)>{{ $nationality }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-medium">{{ translate('Language') }}</label>
                                    <select name="basic_info[language]" class="form-select selectpicker"
                                        placeholder="{{ translate('Select Language') }}" data-live-search="true" data-size="10">
                                        @foreach (languages() as $languageKey => $languageValue)
                                            <option value="{{ $languageKey }}" @selected($languageKey == ($user->basic_info['language'] ?? ''))>
                                                {{ $languageValue }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Social Links Section --}}
                        <div class="border-top-dashed pt-4 mb-2">
                            <h6 class="fw-bold mb-4">
                                <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-share"></i>
                                </span>
                                {{ translate('Social Profiles') }}
                            </h6>
                            <div class="row g-4">
                                @php $socials = getSocialPlatforms(); @endphp
                                @foreach ($socials as $social)
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label fw-medium">{{ translate($social['label']) }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0 bg-light">
                                                <i class="bi {{ $social['icon'] }} fs-5"></i>
                                            </span>
                                            <input type="text" name="social_links[{{ $social['name'] }}]"
                                                class="form-control border-start-0 ps-2"
                                                placeholder="{{ translate('Username') }}"
                                                value="{{ $user->basic_info[$social['name']] ?? '' }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
