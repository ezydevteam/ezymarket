@extends(request()->ajax() ? 'themes.main.layouts.ajax' : 'themes.main.userpanel.layout')
@section('title', translate('Settings - Notification Preferences'))

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
                                    <i class="bi bi-bell fs-5"></i>
                                </span>
                                {{ translate('Notification Preferences') }}
                            </h5>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary px-3" form="preferencesForm">
                                <i class="bi bi-save me-1"></i>
                                {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>
                    <div class="notification-preferences-section">
                        <form action="{{ route('user.settings.notification.preferences.update') }}" class="ajax-form" id="preferencesForm">
                            @csrf
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                @foreach ($preferenceGroups as $groupKey => $groupData)
                                    <div class="col">
                                        <div class="notification-preferences-card p-3 border-0 rounded-4 shadow-sm h-100 bg-light">
                                            <div class="mb-3">
                                                <h6 class="fw-bold mb-1 d-flex align-items-center">
                                                    {{ translate($groupData['label']) }}
                                                </h6>
                                                <small class="text-gray-600">{{ translate($groupData['desc']) }}</small>
                                            </div>
                                            <div class="bg-white rounded-3 p-3 border">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                    @foreach (['in_app', 'email', 'push'] as $type)
                                                        <div class="form-check form-switch fs-14 mb-0">
                                                            <input class="form-check-input preference-switch" type="checkbox"
                                                                name="preferences[{{ $type }}][{{ $groupKey }}]" value="1"
                                                                id="{{ $type }}_{{ $groupKey }}"
                                                                {{ ($userPreferences[$type][$groupKey] ?? true) ? 'checked' : '' }}>
                                                            <label class="form-check-label text-gray-700 link" for="{{ $type }}_{{ $groupKey }}">
                                                                {{ $typeLabels[$type] }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
