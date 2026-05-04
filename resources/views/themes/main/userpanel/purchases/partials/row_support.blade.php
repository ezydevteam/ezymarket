<div class="text-center">
    @php $product = $purchase->product; @endphp
    @if ($purchase->support_expiry_at)
        @if ($purchase->isSupportActive())
            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-medium border-0">
                <i class="bi bi-patch-check me-1 small"></i>{{ translate('Active until') }} {{ dateFormat($purchase->support_expiry_at) }}
            </span>
        @else
            <div class="d-flex flex-column align-items-center gap-1">
                <span class="small fw-medium text-danger">
                    {{ translate('Expired on') }} {{ dateFormat($purchase->support_expiry_at) }}
                </span>
                <button type="button" class="btn btn-outline-danger rounded-pill py-0 fw-normal fs-12 export-ignore"
                    data-bs-toggle="modal" data-bs-target="#supportModal"
                    data-action="{{ route('user.purchase.modal.support', $purchase->id) }}">
                    <i class="bi bi-arrow-repeat me-1 small"></i>{{ translate('Renew Now') }}
                </button>
            </div>
        @endif
    @else
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium export-ignore"
            data-bs-toggle="modal" data-bs-target="#supportModal"
            data-action="{{ route('user.purchase.modal.support', $purchase->id) }}">
            <i class="bi bi-cart-plus me-1 small"></i>{{ translate('Buy Support') }}
        </button>
    @endif
</div>
