<div class="d-flex align-items-center gap-3">
    <div class="bg-light p-2 rounded">
        <i class="bi bi-receipt text-primary fs-5"></i>
    </div>
    <div>
        <h6 class="mb-0 text-dark" title="{{ translate('Transaction ID') }}">#{{ $trx->id }}</h6>
        @if($trx->payment_id)
            <p class="text-muted small mb-0" title="{{ translate('Payment ID') }}">{{ $trx->payment_id }}</p>
        @elseif($trx->paymentGateway)
             <p class="text-muted small mb-0" title="{{ translate('Payment Gateway') }}">{{ $trx->paymentGateway->name }}</p>
        @endif
    </div>
</div>
