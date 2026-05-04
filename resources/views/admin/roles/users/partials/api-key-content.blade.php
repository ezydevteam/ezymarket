<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-filetype-key fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('API Authentication') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Manage secure access keys for application
                        integrations') }}</p>
                </div>
            </div>
            <a href="{{ route('api.docs') }}" class="btn btn-outline-primary fw-bold" target="_blank">
                <i class="bi bi-code-slash me-1"></i>
                {{ translate('API Docs') }}
            </a>
        </div>

        @if ($user->api_key)
        <div class="mb-4">
            <label class="form-label fw-bold small uppercase ls-1 text-muted">{{ translate('Current API Key') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                <input id="apiKey" type="text" class="form-control border-start-0 fw-medium h-auto"
                    value="{{ $user->api_key }}" readonly>
                <button class="btn btn-primary px-4 fw-bold btn-copy" data-clipboard-target="#apiKey">
                    <i class="bi bi-files me-2"></i>{{ translate('Copy Key') }}
                </button>
            </div>
            <div class="form-text mt-2 text-danger small">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ translate('Treat this key like a password. Do not
                share it in public places.') }}
            </div>
        </div>
        @else
        <div class="py-4 text-center bg-light-subtle rounded-4 border border-dashed mb-4">
            <i class="bi bi-shield-slash text-muted" style="font-size: 2.5rem;"></i>
            <h6 class="mt-3 text-muted">{{ translate('No API Key has been generated for this user yet.') }}</h6>
        </div>
        @endif

        <div class="pt-2">
            <form action="{{ route('admin.roles.users.api-key.generate', $user->id) }}" method="POST" class="ajax-form">
                @csrf
                @if ($user->api_key)
                <button type="submit" class="btn bg-danger-subtle text-danger fw-bold action-confirm"
                    data-confirm="{{ translate('Generating a new API key will invalidate the current one. Continue?') }}">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    {{ translate('Regenerate API Key') }}
                </button>
                @else
                <button type="submit" class="btn bg-success-subtle text-success fw-bold px-4">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ translate('Generate First API Key') }}
                </button>
                @endif
            </form>
        </div>
    </div>
</div>
