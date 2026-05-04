@extends('themes.main.userpanel.layout')
@section('section', translate('Transactions'))
@section('title', translate('Transaction details #:number', ['number' => $trx->id]))
@section('back', route('user.transaction.index'))
@section('container', 'userpanel-container-sm')
@section('header_actions')
@if ($trx->isPaid())
    <div class="col-auto">
        <a href="{{ route('user.transaction.invoice', $trx->id) }}"
            target="_blank" class="btn btn-primary">
            <i class="bi bi-journal-text me-1"></i>
            {{ translate('Invoice') }}
        </a>
    </div>
@endif
@endsection

@section('content')
@themeInclude('userpanel.partials.restored-notice', ['model' => $trx, 'type' => 'transaction'])

<div class="userpanel-card card-v border-0 shadow-sm overflow-hidden p-0">
    <!-- Header/Status Summary -->
    <div class="p-4 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col">
                <div class="text-gray-700 small text-uppercase mb-1 fw-semibold tracking-wider">
                    {{ translate('Transaction ID') }}
                </div>
                <h4 class="mb-0 fw-bold">#{{ $trx->id }}</h4>
            </div>
            <div class="col-auto text-end">
                @if ($trx->isPending())
                <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2">
                    <i class="bi bi-clock-history me-1"></i> {{ $trx->status_name }}
                </span>
                @elseif($trx->isPaid())
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i> {{ $trx->status_name }}
                </span>
                @elseif($trx->isCancelled())
                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                    <i class="bi bi-x-circle me-1"></i> {{ $trx->status_name }}
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="p-4 border-bottom">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="text-muted small mb-1">{{ translate('Transaction Date') }}</div>
                <div class="fw-medium">{{ dateFormat($trx->created_at) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small mb-1">{{ translate('Transaction Type') }}</div>
                <div class="fw-medium">{{ $trx->type_name }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small mb-1">{{ translate('Payment Method') }}</div>
                <div class="fw-medium">{{ $trx->paymentGateway ? $trx->paymentGateway->name : 'N/A' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small mb-1">{{ translate('Reference') }}</div>
                <div class="fw-medium text-truncate">#{{ $trx->id }}</div>
            </div>
            @if ($trx->isCancelled() && $trx->reason)
            <div class="col-12">
                <div class="p-3 bg-danger-subtle rounded-3 text-danger small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ translate('Cancellation Reason') }}:</strong> {{ $trx->reason }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Order Summary / Detailed Items -->
    <div class="p-4 border-bottom bg-light-subtle">
        <h6 class="fw-bold text-uppercase small text-gray-700 mb-3 tracking-wider">
            {{ translate('Order Summary') }}
        </h6>
        <div class="list-group list-group-flush bg-transparent">
            @if ($trx->isTypePurchase())
            @foreach ($trx->trxProducts as $trxProduct)
            @php
            $product = $trxProduct->product;
            $licenseType = $trxProduct->isRegularLicense()
            ? translate('Regular License')
            : translate('Extended License');
            @endphp
            <div class="list-group-item px-0 py-3 bg-transparent border-0 border-bottom border-light">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="fw-bold text-dark mb-1">{{ $product->name }}</div>
                        <div class="text-muted small">
                            <span class="badge bg-light text-dark border fw-normal py-1 px-2">{{ $licenseType }}</span>
                            <span class="mx-2 text-opacity-25 opacity-25">|</span>
                            {{ getAmount($trxProduct->price) }} x {{ $trxProduct->quantity }}
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <span class="fw-bold">{{ getAmount($trxProduct->total) }}</span>
                    </div>
                </div>
                @if ($trxProduct->support)
                <div class="mt-3 ps-3 border-start border-primary border-2">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-dark small fw-medium">
                                {{ translate('Support :label', ['label' => $trx->isTypeSupportPurchase() ? 'Purchase' :
                                'Extend']) }}:
                                {{ $trxProduct->support->name }}
                            </div>
                            <div class="text-muted small italic">
                                {{ getAmount($trxProduct->support->price) }} x {{ $trxProduct->support->quantity }}
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <span class="small fw-semibold">{{ getAmount($trxProduct->support->total) }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
            @elseif($trx->isTypeSupportPurchase() || $trx->isTypeSupportExtend())
            <div class="list-group-item px-0 py-3 bg-transparent border-0 border-bottom border-light">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="fw-bold text-dark mb-1">
                            {{ translate('Support :label', ['label' => $trx->isTypeSupportPurchase() ? 'Purchase' :
                            'Extend']) }}:
                            {{ $trx->support->name }}
                        </div>
                        <div class="text-muted small italic">
                            {{ getAmount($trx->support->price) }} x {{ $trx->support->quantity }}
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <span class="fw-bold">{{ getAmount($trx->support->total) }}</span>
                    </div>
                </div>
            </div>
            @elseif($trx->isTypeDeposit())
            <div class="list-group-item px-0 py-3 bg-transparent border-0 border-bottom border-light">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="fw-bold text-dark mb-1">{{ translate('Deposit to wallet') }}</div>
                        <div class="text-muted small">
                            {{ translate('Funds added via :gateway', ['gateway' => $trx->paymentGateway ?
                            $trx->paymentGateway->name : 'Gateway']) }}
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <span class="fw-bold">{{ getAmount($trx->amount) }}</span>
                    </div>
                </div>
            </div>
            @elseif($trx->isTypeSubscription())
            <div class="list-group-item px-0 py-3 bg-transparent border-0 border-bottom border-light">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="fw-bold text-dark mb-1">
                            {{ translate('Premium Membership - :package_name', ['package_name' => $trx->package->name])
                            }}
                        </div>
                        <div class="text-muted small italic">
                            {{ translate('Billing interval: :interval', ['interval' =>
                            $trx->package->getIntervalName()]) }}
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <span class="fw-bold">{{ getAmount($trx->amount) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="p-4">
        <div class="row justify-content-end">
            <div class="col-md-6 col-lg-5">
                @if ($trx->hasFees() || $trx->hasTax())
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ translate('Subtotal') }}</span>
                    <span class="fw-medium">{{ getAmount($trx->amount) }}</span>
                </div>
                @if ($trx->hasTax())
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ translate(':tax_name (:tax_rate%)', [
                        'tax_name' => $trx->tax->name,
                        'tax_rate' => $trx->tax->rate,
                        ]) }}</span>
                    <span class="fw-medium">{{ getAmount($trx->tax->amount) }}</span>
                </div>
                @endif
                @if ($trx->hasFees())
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-light">
                    <span class="text-muted">{{ translate('Processing Fees') }}</span>
                    <span class="fw-medium">{{ getAmount($trx->fees) }}</span>
                </div>
                @endif
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">{{ translate('Total Paid') }}</h5>
                    <h4 class="mb-0 fw-bold text-success">{{ getAmount($trx->total) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($trx->isTypePurchase() && $trx->isPaid())
<div class="text-center mt-5">
    <a class="btn btn-primary btn-modern px-5 py-3 shadow-sm rounded-pill" href="{{ route('user.purchase.index') }}"
        role="button">
        <i class="bi bi-download fs-5 me-2"></i>
        <span class="fw-semibold">{{ translate('Go to Downloads') }}</span>
    </a>
</div>
@endif
@endsection
