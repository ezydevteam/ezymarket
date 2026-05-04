@extends('themes.main.layouts.single')
@section('noindex', true)
@section('section', translate('Payment'))
@section('title', translate('Complete Payment'))
@section('breadcrumbs', Breadcrumbs::render('checkout', $trx))
@section('header_style', 'no_header')
@section('container', 'container container-default')

@section('main')
    <div class="row justify-content-center py-5">
        <div class="col-12 col-md-10 col-lg-7">
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4">
                <div class="text-center mb-5">
                    <div class="icon-circle-xl bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="bi bi-credit-card fs-1"></i>
                    </div>
                    <h2 class="fw-bold mb-2">{{ translate('Complete Your Payment') }}</h2>
                    <p class="text-muted">{{ translate('You are paying via Razorpay. Click the button below to open the secure payment gateway.') }}</p>
                </div>

                <div class="p-4 bg-light rounded-4 border mb-5">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small text-uppercase fw-bold">{{ translate('SubTotal') }}</span>
                            <span class="fw-medium">{{ getAmount($trx->amount) }}</span>
                        </div>
                        
                        @if ($trx->hasTax())
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small text-uppercase fw-bold">
                                    {{ translate(':tax_name (:tax_rate%)', ['tax_name' => $trx->tax->name, 'tax_rate' => $trx->tax->rate]) }}
                                </span>
                                <span class="fw-medium text-danger">+ {{ getAmount($trx->tax->amount) }}</span>
                            </div>
                        @endif

                        @if ($trx->hasFees())
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small text-uppercase fw-bold">
                                    {{ translate(':payment_gateway Fees (:percentage%)', ['payment_gateway' => $trx->paymentGateway->name, 'percentage' => $trx->paymentGateway->fees]) }}
                                </span>
                                <span class="fw-medium text-danger">+ {{ getAmount($trx->fees) }}</span>
                            </div>
                        @endif

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold mb-0">{{ translate('Total Payable') }}</span>
                            <span class="h4 fw-bold mb-0 text-primary">{{ getAmount($trx->total) }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('payments.ipn.razorpay') }}" method="POST">
                    @csrf
                    <input type="hidden" name="trx_id" value="{{ hash_encode($trx->id) }}">
                    <script src="https://checkout.razorpay.com/v1/checkout.js"
                        @foreach ($data as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach></script>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('checkout.index', hash_encode($trx->id)) }}" class="btn btn-link link-secondary text-decoration-none hover-underline">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Cancel and Change Payment Method') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            "use strict";
            $(document).ready(function() {
                $('.razorpay-payment-button').addClass('btn btn-primary btn-lg rounded-pill fw-bold py-3 w-100 shadow-sm mt-2');
                $('.razorpay-payment-button').text("{{ translate('Pay Now') }}");
            });
        </script>
    @endpush
@endsection
