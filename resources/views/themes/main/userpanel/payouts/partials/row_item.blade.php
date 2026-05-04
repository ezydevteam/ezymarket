<div class="d-flex align-items-center gap-3">
    <div class="bg-light p-2 rounded">
        <i class="bi bi-wallet2 text-primary fs-5"></i>
    </div>
    <div class="min-w-0">
        <div class="fw-semibold mb-1">{{ $payout->method ?: translate('N/A') }}</div>
        <small class="text-muted">{{ hideInDemo(truncateText($payout->account, 25)) }}</small>
    </div>
</div>
