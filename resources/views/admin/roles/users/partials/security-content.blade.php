<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom-dashed">
                    <span class="icon-circle icon-circle-sm bg-primary-subtle text-primary">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">{{ translate('Change Password') }}</h5>
                </div>
                <form id="updatePasswordForm"
                    action="{{ route('admin.roles.users.security.password.update', $user->id) }}" method="POST"
                    class="ajax-form">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="form-label fw-bold small uppercase ls-1">{{ translate('New Password') }} <span
                                class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" name="new-password" placeholder="********"
                                required>
                            <span class="position-absolute top-50 end-0 translate-middle-y cursor-pointer">
                                <i class="bi bi-eye text-muted password-toggle"></i>
                            </span>
                        </div>
                        <div class="form-text small">{{ translate('At least 8 characters long.') }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small uppercase ls-1">{{ translate('Confirm Password') }} <span
                                class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" name="new-password_confirmation"
                                placeholder="********" required>
                            <span class="position-absolute top-50 end-0 translate-middle-y cursor-pointer">
                                <i class="bi bi-eye text-muted password-toggle"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary fw-bold px-4">{{ translate('Update Password')
                        }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom-dashed">
                    <span class="icon-circle icon-circle-sm bg-warning-subtle text-warning">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                    <h5 class="fw-bold mb-0">{{ translate('Two-Factor Authentication') }}</h5>
                </div>
                <form action="{{ route('admin.roles.users.security.2fa.update', $user->id) }}" method="POST"
                    class="ajax-form">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <p class="text-muted fs-14 mb-3">
                            {{ translate('Require a second form of verification during login to enhance account
                            security.') }}
                        </p>
                        <div class="ezydev-switch-wrapper-xl">
                            <input type="hidden" name="google2fa_status" value="0">
                            <input id="google2fa_status" class="ezydev-switch-input" type="checkbox"
                                name="google2fa_status" value="1" {{ $user->has2fa() ? 'checked' : '' }}>
                            <label class="ezydev-switch-label mb-0" for="google2fa_status">
                                <span class="ezydev-switch-slider">
                                    <span class="ezydev-switch-button">
                                        <span class="ezydev-switch-on">{{ translate('Enabled') }}</span>
                                        <span class="ezydev-switch-off">{{ translate('Disabled') }}</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 rounded-4 mb-4 py-3 small">
                        <i class="bi bi-info-circle me-2"></i>{{ translate('User must have already set up 2FA in their
                        settings.') }}
                    </div>
                    <button type="submit" class="btn btn-primary fw-bold px-4">{{ translate('Save Preference')
                        }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="p-4">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-lock fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Login Activities') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('History of recent successful login attempts') }}</p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table ezydev-table datatable-small">
                <thead>
                    <tr>
                        <th>{{ translate('Location / IP') }}</th>
                        <th class="text-center">{{ translate('Browser / Device') }}</th>
                        <th class="text-center">{{ translate('Date & Time') }}</th>
                        <th width="60" class="text-end">{{ translate('Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loginLogs as $loginLog)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <div>
                                    <div class="fw-bold">{{ $loginLog->country ?: translate('Unknown') }}</div>
                                    <div class="text-muted small">{{ hideInDemo($loginLog->ip) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="fw-medium">{{ $loginLog->browser ?: translate('Unknown') }}</div>
                            <div class="text-muted small">{{ $loginLog->device_brand ?? translate('Unknown Device') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div>{{ dateFormat($loginLog->created_at, 'M d, Y') }}</div>
                            <div class="text-muted small">{{ dateFormat($loginLog->created_at, 'g:i A') }}</div>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-icon btn-soft-primary" data-bs-toggle="modal"
                                data-bs-target="#loginLogModal{{ $loginLog->id }}"
                                title="{{ translate('View Details') }}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">{{ translate('No recent login activities
                            found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loginLogs->hasPages())
        <div class="my-4 d-flex justify-content-center ajax-pagination">
            {{ $loginLogs->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Login Log Details Modals --}}
@foreach ($loginLogs as $loginLog)
<x-modal id="loginLogModal{{ $loginLog->id }}" :title="translate('Login Activity Details')" :scrollable="true"
    icon="bi bi-info-circle" bodyClass="px-0">

    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('IP Address') }}</span>
            <span class="fw-medium">{{ hideInDemo($loginLog->ip) }}</span>
        </div>

        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('Location') }}</span>
            <span class="fw-medium">{{ $loginLog->location ?: 'Unknown' }}, {{ $loginLog->country ?: 'Unknown' }}</span>
        </div>

        @if($loginLog->country_code)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('Country Code') }}</span>
            <span class="fw-medium">{{ $loginLog->country_code }}</span>
        </div>
        @endif

        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('Browser') }}</span>
            <span class="fw-medium">
                {{ $loginLog->browser ?: 'Unknown' }}
                @if($loginLog->browser_version)
                <small class="text-muted text-nowrap ms-1">(v{{ $loginLog->browser_version }})</small>
                @endif
            </span>
        </div>

        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('OS') }}</span>
            <span class="fw-medium">
                {{ $loginLog->os ?: 'Unknown' }}
                @if($loginLog->os_version)
                <small class="text-muted text-nowrap ms-1">(v{{ $loginLog->os_version }})</small>
                @endif
            </span>
        </div>

        @if($loginLog->device_brand)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('Device') }}</span>
            <span class="fw-medium">{{ $loginLog->device_brand }} {{ $loginLog->device_model }}</span>
        </div>
        @endif

        @if($loginLog->is_bot)
        <div class="list-group-item d-flex justify-content-between align-items-center list-group-item-danger">
            <span class="text-danger small uppercase fw-bold ls-1">{{ translate('Bot Detected') }}</span>
            <span class="fw-bold text-danger"><i class="bi bi-robot me-1"></i>{{ translate('Yes') }}</span>
        </div>
        @endif

        <div class="list-group-item d-flex justify-content-between align-items-center border-bottom-0">
            <span class="text-muted small uppercase fw-bold ls-1">{{ translate('Activity Date') }}</span>
            <span class="fw-medium text-end">
                <div>{{ dateFormat($loginLog->created_at, 'd M, Y') }}</div>
                <div class="small text-muted">{{ dateFormat($loginLog->created_at, 'g:i:s A') }}</div>
            </span>
        </div>
    </div>
</x-modal>
@endforeach
