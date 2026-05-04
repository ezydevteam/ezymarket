<form id="editSupportPackageForm"
    action="{{ route('admin.settings.support-packages.update', $supportPackage->id) }}"
    method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        {{-- Name --}}
        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Package Name') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                <input type="text" name="name" class="form-control" value="{{ $supportPackage->name }}" placeholder="{{ translate('e.g., Basic Support') }}" required>
            </div>
            <div class="form-text text-muted">{{ translate('Internal name for the package.') }}</div>
        </div>

        {{-- Title --}}
        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Public Title') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-type"></i></span>
                <input type="text" name="title" class="form-control" value="{{ $supportPackage->title }}" placeholder="{{ translate('e.g., 6 Months Support') }}" required>
            </div>
            <div class="form-text text-muted">{{ translate('Title displayed publicly to users.') }}</div>
        </div>

        {{-- Days --}}
        <div class="col-12">
            <label class="form-label fw-bold">{{ translate('Duration (Days)') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                <input type="number" name="days" class="form-control" value="{{ $supportPackage->days }}" placeholder="180" min="1" required>
                <span class="input-group-text">{{ translate('Days') }}</span>
            </div>
        </div>

        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                     <h6 class="fw-bold mb-3"><i class="bi bi-cash-coin me-2"></i>{{ translate('Pricing Configuration') }}</h6>

                    <div class="row g-3">
                        {{-- Rate Percentage --}}
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Percentage Fee') }}</label>
                            <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                <input type="number" name="rate_percentage" class="form-control" value="{{ $supportPackage->rate['percentage'] ?? 0 }}" placeholder="0" min="0" max="100">
                            </div>
                            <div class="form-text small">{{ translate('% of product price') }}</div>
                        </div>

                        {{-- Rate Fixed --}}
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Fixed Fee') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="rate_fixed" class="form-control" value="{{ $supportPackage->rate['fixed'] ?? 0 }}" placeholder="0.00" min="0" step="0.01">
                            </div>
                            <div class="form-text small">{{ translate('Flat extra fee') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
