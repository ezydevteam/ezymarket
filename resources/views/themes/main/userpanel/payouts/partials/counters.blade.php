<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div
            class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 userpanel-stat-card transition-base">
            <div
                class="icon-circle icon-circle-md bg-primary-subtle text-primary mx-auto mb-3 shadow-sm border border-primary-subtle">
                <i class="bi bi-wallet2 fs-3"></i>
            </div>
            <div class="d-flex align-items-center justify-content-center gap-3 mb-1">
                <h3 class="fw-black mb-0 balance-amount">
                    <span class="amount-masked">{{ str_repeat('•', 6) }}</span>
                    <span class="amount-real d-none">{{ getAmount(authUser()->balance) }}</span>
                    <span role="button" class="balance-toggle fs-5"
                        id="balanceToggle" title="{{ translate('Toggle Balance Visibility') }}">
                        <i class="bi bi-eye"></i>
                    </span>
                </h3>
            </div>
            <p class="text-muted small mb-0 text-uppercase ls-1 fw-bold">{{ translate('Wallet Balance') }}</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div
            class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 userpanel-stat-card transition-base">
            <div
                class="icon-circle icon-circle-md bg-warning-subtle text-warning mx-auto mb-3 shadow-sm border border-warning-subtle">
                <i class="bi bi-hourglass-split fs-3"></i>
            </div>
            <h3 class="fw-black mb-1">{{ getAmount($counters['pending_payouts'] ?? 0) }}</h3>
            <p class="text-muted small mb-0 text-uppercase ls-1 fw-bold">{{ translate('Pending Payouts') }}</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div
            class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 userpanel-stat-card transition-base">
            <div
                class="icon-circle icon-circle-md bg-success-subtle text-success mx-auto mb-3 shadow-sm border border-success-subtle">
                <i class="bi bi-bank fs-3"></i>
            </div>
            <h3 class="fw-black mb-1">{{ getAmount($counters['total_payouts'] ?? 0) }}</h3>
            <p class="text-muted small mb-0 text-uppercase ls-1 fw-bold">{{ translate('Total Payouts') }}</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div
            class="card card-body border-0 shadow-sm rounded-4 p-4 text-center h-100 userpanel-stat-card transition-base border-start border-4 border-info">
            <div class="icon-circle icon-circle-md bg-info-subtle text-info mx-auto mb-3 shadow-sm border border-info-subtle">
                <i class="bi bi-graph-up-arrow fs-3"></i>
            </div>
            <h3 class="fw-black mb-1">{{ getAmount($counters['total_earnings'] ?? 0) }}</h3>
            <p class="text-muted small mb-0 text-uppercase ls-1 fw-bold">{{ translate('Total Earnings') }}</p>
        </div>
    </div>
</div>
