@extends('themes.main.layouts.single')
@section('noindex', true)
@section('section', translate('Payment Approval'))
@section('title', translate('Submit Payment Proof'))
@section('breadcrumbs', Breadcrumbs::render('checkout', $trx))
@section('header_style', 'no_header')
@section('container', 'container container-default')

@section('main')
<div class="section bank-payment-section">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-7">
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4">
                <div class="text-center mb-5">
                    <div class="icon-circle-xl bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="bi bi-bank fs-1"></i>
                    </div>
                    <h2 class="fw-bold mb-2">
                        {{ translate('Bank Transfer Payment') }}
                    </h2>
                    <p class="text-muted">
                        {{ translate('Please follow the instructions below to complete your transfer and submit your proof of payment.') }}
                    </p>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-primary rounded-pill me-2">1</span>
                        <h5 class="mb-0 fw-bold">{{ translate('Instructions') }}</h5>
                    </div>
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="payment-instructions text-secondary">
                            {!! $trx->paymentGateway->instructions !!}
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-primary rounded-pill me-2">2</span>
                        <h5 class="mb-0 fw-bold">{{ translate('Total Amount to Transfer') }}</h5>
                    </div>
                    <div class="p-4 bg-primary-light text-primary rounded-4 shadow-sm text-center">
                        <span class="text-uppercase fw-medium small opacity-75 d-block mb-1 ls-1">
                            {{ translate('Total Payable') }}
                        </span>
                        <h1 class="display-5 fw-bold mb-0">{{ getAmount($trx->total) }}</h1>
                    </div>
                </div>

                <div>
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-primary rounded-pill me-2">3</span>
                        <h5 class="mb-0 fw-bold">{{ translate('Upload Payment Proof') }}</h5>
                    </div>
                    <form action="{{ route('payments.manual.bankwire') }}" method="POST"
                        enctype="multipart/form-data" class="needs-validation">
                        @csrf
                        <input type="hidden" name="checkout_id" value="{{ hash_encode($trx->id) }}">

                        <div class="upload-zone p-4 bg-light rounded-4 border border-dashed border-secondary text-center mb-4 position-relative">
                            <input type="file" name="payment_proof" id="payment_proof" class="form-control d-none"
                                   accept="image/*, application/pdf" required>

                            <label for="payment_proof" class="mb-0 cursor-pointer w-100">
                                <i class="bi bi-cloud-arrow-up display-6 text-primary mb-3 d-block"></i>
                                <span class="d-block fw-medium mb-1" id="file-label"
                                    data-placeholder="{{ translate('Click to select or drag and drop your file here') }}">
                                    {{ translate('Click to select or drag and drop your file here') }}
                                </span>
                                <small class="text-muted d-block">
                                    {{ translate('Accepted formats: JPG, PNG, PDF (Max 2MB)') }}
                                </small>
                            </label>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm">
                                {{ translate('Submit Proof and Complete Order') }}
                            </button>
                            <a href="{{ route('checkout.index', hash_encode($trx->id)) }}"
                                class="btn btn-link link-secondary text-decoration-none hover-underline">
                                {{ translate('Cancel and Change Payment Method') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
