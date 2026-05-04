<x-modal
    id="productReportSettingsModal"
    :title="translate('Product Report Settings')"
    icon="bi-gear"
    size="lg"
    :scrollable="true"
>
    <form id="productReportSettingsForm" action="{{ route('admin.reports.product-reports.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Product Auto Restriction Settings --}}
        <div class="card mb-3">
            <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="bi bi-database-exclamation me-2"></i>{{ translate('Product Auto Restriction') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-switch
                            name="auto_restrict"
                            id="settings-auto-restrict"
                            label="Auto Restriction Status"
                            onLabel="Enabled"
                            offLabel="Disabled"
                            :checked="$productReportSettings->auto_restrict ?? 1"
                        />
                        <div class="form-text small">{{ translate('Automatically restrict products when threshold is reached') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Restriction Threshold') }}</label>
                        <input type="number" name="restrict_threshold" class="form-control" value="{{ $productReportSettings->restrict_threshold ?? 10 }}" min="1" max="1000" required>
                        <div class="form-text small">{{ translate('Number of reports for auto-restriction') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Restriction Period (Days)') }}</label>
                        <input type="number" name="restrict_days" class="form-control" value="{{ $productReportSettings->restrict_days ?? 7 }}" min="1" max="365" required>
                        <div class="form-text small">{{ translate('Number of days for auto unrestriction') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Auto Deletion --}}
        <div class="card mb-3">
            <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="bi bi-trash me-2"></i>{{ translate('Product Auto Deletion') }}</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ translate('Auto-delete will permanently remove products from the system and cannot be undone') }}
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-switch
                            name="auto_delete"
                            id="settings-auto-delete"
                            label="Auto Deletion Status"
                            onLabel="Enabled"
                            offLabel="Disabled"
                            :checked="$productReportSettings->auto_delete ?? 0"
                        />
                        <div class="form-text small">{{ translate('Automatically delete products when threshold is reached') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ translate('Deletion Threshold') }}</label>
                        <input type="number" name="delete_threshold" class="form-control" value="{{ $productReportSettings->delete_threshold ?? 20 }}" min="1" max="1000" required>
                        <div class="form-text small">{{ translate('Number of reports for auto-deletion') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reporter Auto Restriction --}}
        <div class="card">
            <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="bi bi-person-exclamation me-2"></i>{{ translate('Reporter Auto Restriction') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-switch
                            name="restrict_reporter"
                            id="settings-restrict-reporter"
                            label="Reporter Restriction Status"
                            onLabel="Enabled"
                            offLabel="Disabled"
                            :checked="$productReportSettings->restrict_reporter ?? 0"
                        />
                        <div class="form-text small">{{ translate('Automatically restrict users who submit false reports') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Restriction Threshold') }}</label>
                        <input type="number" name="reporter_threshold" class="form-control" value="{{ $productReportSettings->reporter_threshold ?? 20 }}" min="1" max="1000" required>
                        <div class="form-text small">{{ translate('Number of false reports for auto-restriction') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Restriction Period (Days)') }}</label>
                        <input type="number" name="reporter_days" class="form-control" value="{{ $productReportSettings->reporter_days ?? 7 }}" min="1" max="365" required>
                        <div class="form-text small">{{ translate('Number of days for restriction') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>{{ translate('Close') }}</button>
        <button id="productReportSettingsSubmitBtn" form="productReportSettingsForm" class="btn btn-primary ms-2">
            <i class="bi bi-check-circle me-2"></i>{{ translate('Save Settings') }}
        </button>
    </x-slot>
</x-modal>
