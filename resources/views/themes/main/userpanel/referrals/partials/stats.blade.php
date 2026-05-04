<div class="col-12">
    <div class="row g-4">
        <div class="col-md-4">
            <div
                class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-all hover-translate-y">
                <div class="icon-circle icon-circle-lg bg-success-subtle text-success mx-auto mb-3">
                    <i class="bi bi-person-check-fill fs-3"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ numberFormat($referrals->total() ?? 0) }}</h3>
                <p class="text-muted small mb-0">{{ translate('Total Referrals') }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div
                class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-all hover-translate-y">
                <div class="icon-circle icon-circle-lg bg-primary-subtle text-primary mx-auto mb-3">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ getAmount($referrals->sum('earnings') ?? 0) }}</h3>
                <p class="text-muted small mb-0">{{ translate('Total Earnings') }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div
                class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-all hover-translate-y">
                <div class="icon-circle icon-circle-lg bg-warning-subtle text-warning mx-auto mb-3">
                    <i class="bi bi-percent fs-3"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ @$settings->referral->percentage }}%</h3>
                <p class="text-muted small mb-0">{{ translate('Commission Rate') }}</p>
            </div>
        </div>
    </div>
</div>
