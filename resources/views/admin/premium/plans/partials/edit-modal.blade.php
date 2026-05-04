<x-modal id="editPlanModal-{{ $premiumPlan->id }}" :title="translate('Edit Plan #:id', ['id' => $premiumPlan->id])"
    icon="bi bi-pencil-square" size="lg" scrollable="true" autoOpen="true">
    <form id="editPlanForm-{{ $premiumPlan->id }}" action="{{ route('admin.premium.plans.update', $premiumPlan->id) }}"
        method="POST">
        @csrf
        @method('PUT')

        {{-- Premium Membership Stats Alert --}}
        @if($premiumPlan->premiums_count > 0)
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-people-fill fs-4 me-3"></i>
            <div>
                <strong>{{ $premiumPlan->premiums_count }}</strong> {{ translate('active membership(s)') }}
                <br><small>{{ translate('Changes affect new memberships only') }}</small>
            </div>
        </div>
        @endif

        {{-- Plan Status Section --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between gap-2 p-3 border rounded bg-light">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><i class="bi bi-battery-charging me-2"></i>{{ translate('Active Plan') }}</h6>
                        <small class="text-muted">{{ translate('Enable for memberships') }}</small>
                    </div>
                    <div class="ezydev-switch-wrapper-lg">
                        <input type="hidden" name="status" value="0">
                        <input id="status-{{ $premiumPlan->id }}" class="ezydev-switch-input" type="checkbox"
                            name="status" value="1" {{ $premiumPlan->isActive() ? 'checked' : '' }}>
                        <label class="ezydev-switch-label" for="status-{{ $premiumPlan->id }}">
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
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between gap-2 p-3 border rounded bg-light">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><i class="bi bi-star me-2"></i>{{ translate('Featured') }}</h6>
                        <small class="text-muted">{{ translate('Highlight this plan') }}</small>
                    </div>
                    <div class="ezydev-switch-wrapper-lg">
                        <input type="hidden" name="featured" value="0">
                        <input id="featured-{{ $premiumPlan->id }}" class="ezydev-switch-input featured-toggle"
                            type="checkbox" name="featured" value="1" {{ $premiumPlan->isFeatured() ? 'checked' : '' }}>
                        <label class="ezydev-switch-label" for="featured-{{ $premiumPlan->id }}">
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

        {{-- Basic Information --}}
        <div class="mb-4">
            <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <i class="bi bi-info-circle me-1"></i>{{ translate('Basic Information') }}
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ translate('Plan Name') }} <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input type="text" name="name" class="form-control" value="{{ $premiumPlan->name }}"
                            placeholder="{{ translate('e.g., Basic, Pro, Enterprise') }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ translate('Description') }}</label>
                    <div class="input-group">
                        <span class="input-group-text align-items-start pt-2"><i class="bi bi-text-left"></i></span>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="{{ translate('Brief description of the package...') }}">{{ $premiumPlan->description }}</textarea>
                    </div>
                </div>
                <div class="col-12 featured-label-section"
                    style="display: {{ $premiumPlan->isFeatured() ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">
                        {{ translate('Featured Label') }}
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-badge-tm"></i></span>
                        <input type="text" name="featured_label" class="form-control"
                            value="{{ $premiumPlan->featured_label }}"
                            placeholder="{{ translate('e.g., Most Popular, Best Value, Top Choice') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing & Billing --}}
        <div class="mb-4">
            <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <i class="bi bi-cash-stack me-1"></i>{{ translate('Billing & Pricing') }}
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        {{ translate('Billing Interval') }}
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <select class="form-select" disabled>
                            @foreach (\App\Enums\PremiumPlanInterval::cases() as $interval)
                            <option value="{{ $interval->value }}" @selected($premiumPlan->interval === $interval)>
                                {{ $interval->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-orange"><i class="bi bi-exclamation-circle me-1"></i>{{ translate('Cannot be
                        changed') }}</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ translate('Price') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                        <input name="price" class="form-control input-price" placeholder="0.00"
                            value="{{ number_format($premiumPlan->price, 2) }}">
                    </div>
                    <small class="text-muted">{{ translate('Leave empty for free') }}</small>
                </div>
            </div>
        </div>

        {{-- Revenue & Limits --}}
        <div class="mb-4">
            <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <i class="bi bi-graph-up me-1"></i>{{ translate('Revenue & Limits') }}
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ translate('Seller Earning') }} <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-percent"></i></span>
                        <input type="number" name="seller_earning_percentage" class="form-control" placeholder="0"
                            step="any" min="0" max="100" value="{{ $premiumPlan->seller_earning_percentage ?? 0 }}"
                            required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">{{ translate('Revenue share (0-100%)') }}</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ translate('Daily Downloads') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-download"></i></span>
                        <input type="number" name="downloads" class="form-control" placeholder="0" min="1"
                            value="{{ $premiumPlan->downloads }}">
                        <span class="input-group-text">{{ translate('per day') }}</span>
                    </div>
                    <small class="text-muted">{{ translate('Empty = unlimited') }}</small>
                </div>
            </div>
        </div>

        {{-- Custom Features --}}
        <div class="mb-3">
            <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <i class="bi bi-list-check me-1"></i>{{ translate('Custom Features') }}
            </h6>
            <div class="row g-2 custom-features">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-primary btn-sm add-custom-feature">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Feature') }}
                    </button>
                </div>
                @if ($premiumPlan->custom_features)
                @foreach ($premiumPlan->custom_features as $key => $customFeature)
                <div class="col-12 custom-feature-box-{{ $key }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-check2"></i></span>
                        <input type="text" name="custom_features[]" class="form-control" value="{{ $customFeature }}"
                            placeholder="{{ translate('Feature description...') }}" required>
                        <button class="btn btn-danger custom-feature-remove" type="button" data-id="{{ $key }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>{{ translate('Cancel') }}
        </button>
        <button type="submit" form="editPlanForm-{{ $premiumPlan->id }}" id="editPlanBtn-{{ $premiumPlan->id }}"
            class="btn btn-primary ms-3">
            <i class="bi bi-check-circle me-1"></i>{{ translate('Update Plan') }}
        </button>
    </x-slot>
</x-modal>
