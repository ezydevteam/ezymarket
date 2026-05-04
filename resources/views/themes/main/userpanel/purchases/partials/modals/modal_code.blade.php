<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-bold">{{ translate('Product Purchase Code') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <p class="text-gray-700 mb-3">{{ translate('Use this code for support verification with the seller.') }}</p>
    <div class="input-group">
        <input id="purchaseCode" type="text" class="form-control form-control-md bg-light"
               value="{{ $purchase->code }}" readonly>
        <button class="btn btn-primary btn-copy px-3" data-clipboard-target="#purchaseCode"
            title="{{ translate('Click to Copy') }}">
            <i class="bi bi-copy"></i>
        </button>
    </div>
</div>
