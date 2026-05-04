@if (isPremiumAvailable())
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-gem fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Premium Plan') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Manage user premium plan') }}</p>
                </div>
            </div>
        </div>

        @if ($user->isPremiumMember())
        @php
        $premium = $user->premium;
        $daysRemaining = max(0, $premium->days_remaining ?? 0);
        $totalDays = $premium->created_at->diffInDays($premium->expiry_at);

        if ($premium->isExpired()) {
        $percentage = 100;
        } else {
        $percentage = $totalDays > 0 ? min(100, max(0, (($totalDays - $daysRemaining) / $totalDays) * 100)) : 0;
        }
        @endphp

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="mb-4">
                    <h6 class="mb-1">{{ translate('Current Plan is') }} <strong>{{ $premium->plan->name }}</strong></h6>
                    <small class="text-muted">{{ truncateText($premium->plan->description, 60) }}</small>
                </div>

                <div class="mb-4">
                    <h6 class="mb-1">
                        @if($premium->isExpired())
                        {{ translate('Expired on') }} <strong class="text-danger">{{
                            dateFormat($premium->expiry_at, 'M d, Y') }}</strong>
                        <p class="text-muted fw-light small mb-0 mt-1">{{ translate('Already sent a notification upon
                            expiration') }}</p>
                        @else
                        {{ translate('Active until') }} <strong class="text-success">{{
                            dateFormat($premium->expiry_at, 'M d, Y') }}</strong>
                        <p class="text-muted fw-light small mb-0 mt-1">{{ translate('May send a notification upon
                            expiration') }}</p>
                        @endif
                    </h6>
                </div>

                <div class="mb-4">
                    <h5 class="mb-2">
                        {{ getAmount($premium->plan->price) }} {{ translate('Per Month') }}
                        @if ($premium->plan->is_recommended)
                        <span class="badge bg-text-primary rounded-pill ms-1">{{ translate('Recommended') }}</span>
                        @endif
                    </h5>
                    <p class="text-muted mb-0">{{ translate('Standard plan for small to medium businesses') }}</p>
                </div>

                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#upgradePlanModal">
                        {{ translate('Upgrade Plan') }}
                    </button>
                    <button type="button" class="btn bg-danger-subtle text-danger action-confirm"
                        data-action="{{ route('admin.roles.users.premium.cancel', $user->id) }}"
                        data-method="POST"
                        data-text="{{ translate('Are you sure want to cancel this user\'s premium membership?') }}">{{
                        translate('Cancel Membership') }}</button>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="bg-light rounded-3 p-3">
                    @if ($daysRemaining <= 7)
                    <div class="alert alert-warning border-0 mb-3">
                        <h5 class="alert-heading text-danger mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ translate('We need your attention!') }}
                        </h5>
                        <p class="mb-0">{{ translate('Premium plan requires update') }}</p>
                    </div>
                    @endif

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">{{ translate('Remaining') }}</span>
                            <span class="fw-semibold">{{ $daysRemaining }} {{ translate('of') }} {{ $totalDays }} {{
                                translate('Days') }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%;"
                                aria-valuenow="{{ $daysRemaining }}" aria-valuemin="0" aria-valuemax="{{ $totalDays }}">
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0">{{ translate('Plan will expire on') }} <strong
                                class="text-danger">{{ timeAgo($premium->expiry_at) }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center">
            <p class="text-muted">{{ translate('This user does not have any active premium plan. Assign a new premium
                plan
                to give them access to premium features.') }}</p>
            <button type="button" class="btn btn-md btn-primary" data-bs-toggle="modal"
                data-bs-target="#assignPlanModal">
                {{ translate('Assign Plan') }}
            </button>
        </div>
        @endif
    </div>
</div>

{{-- Upgrade Plan Modal --}}
@if ($user->isPremiumMember())
<x-modal id="upgradePlanModal" title="{{ translate('Upgrade Premium Plan') }}" icon="bi-arrow-up-circle">
    <form id="upgradePlanForm" action="{{ route('admin.roles.users.premium.upgrade', $user->id) }}" class="ajax-form"
        method="POST">
        @csrf
        <label class="form-label">{{ translate('Choose Plan') }} <span class="text-danger">*</span></label>
        <div class="input-groups d-flex align-items-center gap-3">
            <select name="plan_id" class="form-select form-control-lg selectpicker" data-live-search="true"
                data-size="7" placeholder="{{ translate('Select Plan') }}" required>
                @foreach (\App\Models\Premium\PremiumPlan::where('is_active', true)->orderBy('sort_id')->get() as $premiumPlan)
                @if($premiumPlan->id != $premium->plan->id)
                <option value="{{ $premiumPlan->id }}">
                    {{ $premiumPlan->name }} - {{ $premiumPlan->price_label}}{{ translate('/month') }} {{
                    $premiumPlan->featured_badge ? '('. $premiumPlan->featured_badge .')' : '' }}
                </option>
                @endif
                @endforeach
            </select>
            <button type="submit" form="upgradePlanForm" data-spinner-text="false"
                class="btn btn-md btn-primary action-confirm"
                data-confirm="{{ translate('Are you sure you want to upgrade this user\'s subscription plan?') }}">
                {{ translate('Upgrade') }}
            </button>
        </div>
    </form>
    <div class="border-top mt-4 pt-3">
        <p class="mb-1">{{ translate('User Current Plan is ') }}<strong>{{ $premium->plan->name }}</strong></p>
        <div>
            <p class="text-primary fw-bolder fs-1 mb-0 d-inline"><sup>{{ currency_symbol() }}</sup>{{
                translate(':price', ['price' => $premium->plan->price]) }}</p><span>{{ translate('/month') }}</span>
        </div>
    </div>
</x-modal>
@else
<x-modal id="assignPlanModal" title="{{ translate('Assign New Plan') }}" icon="bi-plus-circle">
    <form id="assignPlanForm" action="{{ route('admin.roles.users.premium.assign', $user->id) }}" class="ajax-form"
        method="POST">
        @csrf
        <label class="form-label">{{ translate('Choose Plan') }} <span class="text-danger">*</span></label>
        <div class="input-groups d-flex align-items-center gap-3">
            <select name="plan_id" class="form-select form-control-lg selectpicker" data-live-search="true"
                data-size="7" placeholder="{{ translate('Select Plan') }}" required>
                @foreach (\App\Models\Premium\PremiumPlan::where('is_active', true)->orderBy('sort_id')->get() as $premiumPlan)
                <option value="{{ $premiumPlan->id }}">
                    {{ $premiumPlan->name }} - {{ $premiumPlan->price_label}}{{ translate('/month') }} {{
                    $premiumPlan->featured_badge ? '('. $premiumPlan->featured_badge .')' : '' }}
                </option>
                @endforeach
            </select>
            <button type="submit" form="assignPlanForm" data-spinner-text="false"
                class="btn btn-md btn-primary action-confirm"
                data-confirm="{{ translate('Are you sure you want to assign a premium plan to this user?') }}">
                {{ translate('Assign') }}
            </button>
        </div>
    </form>
    <div class="alert alert-info mt-4"><i class="bi bi-info-circle me-2"></i>{{ translate('Assigning a premium plan will
        grant the user access to premium features based on the selected plan.') }}</div>
</x-modal>
@endif
@endif
