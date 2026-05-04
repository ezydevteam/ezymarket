@if ($update->regular_price || $update->extended_price)
    <div class="d-flex flex-column gap-1">
        @if ($update->regular_price)
            <div class="d-flex align-items-center justify-content-between gap-3">
                <span class="text-muted small">{{ translate('Regular') }}</span>
                <div>
                    <span class="text-decoration-line-through text-muted small me-1">
                        {{ getAmount($update->product->price->regular) }}
                    </span>
                    <i class="bi bi-arrow-right small text-muted mx-1"></i>
                    <span class="text-success">
                        {{ getAmount($update->price->regular) }}
                    </span>
                </div>
            </div>
        @endif
        @if ($update->extended_price)
            <div class="d-flex align-items-center justify-content-between gap-3">
                <span class="text-muted small">{{ translate('Extended') }}</span>
                <div>
                    <span class="text-decoration-line-through text-muted small me-1">
                        {{ getAmount($update->product->price->extended) }}
                    </span>
                    <i class="bi bi-arrow-right small text-muted mx-1"></i>
                    <span class="text-success">
                        {{ getAmount($update->price->extended) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
@else
    <span class="text-muted">{{ translate('No price changes') }}</span>
@endif
