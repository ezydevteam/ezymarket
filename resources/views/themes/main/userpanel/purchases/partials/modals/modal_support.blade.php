@php $isRenewal = $purchase->isSupportExpired(); @endphp
<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-bold">
        {{ $isRenewal ? translate('Renew Product Support') : translate('Purchase Product Support') }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
        <div class="flex-shrink-0">
            <img src="{{ $purchase->product->getThumbnail() }}"
                 alt="{{ $purchase->product->name }}"
                 class="rounded-2 shadow-sm"
                 style="width: 48px; height: 48px; object-fit: cover;">
        </div>
        <div class="flex-grow-1 ms-3 overflow-hidden">
            <h6 class="mb-0 text-truncate fw-semibold">{{ $purchase->product->name }}</h6>
            <span class="small text-muted">#{{ $purchase->id }}</span>
        </div>
    </div>

    <form action="{{ $isRenewal ? route('user.purchase.support.extend', $purchase->id) : route('user.purchase.support.purchase', $purchase->id) }}"
        method="POST">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-semibold text-gray-700 small mb-2">
                {{ translate('Select Support Package') }}
            </label>
            <select name="support" class="form-select form-select-md bg-light">
                @foreach ($supportPackages as $supportPackage)
                    <option value="{{ $supportPackage->id }}">
                        {{ $supportPackage->title }} ({{ $supportPackage->days }} {{ translate('Days') }})
                    </option>
                @endforeach
            </select>
            <p class="text-muted small mt-2 mb-0">
                {{ translate('Base price for support is calculated based on the product license price.') }}
            </p>
        </div>
        <button class="btn btn-md btn-primary w-100 rounded-pill py-2 fw-medium shadow-sm">
            <i class="bi bi-cart-plus me-2"></i>{{ translate('Continue to Checkout') }}
        </button>
    </form>
</div>
