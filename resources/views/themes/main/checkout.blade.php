@extends('themes.main.layouts.single')
@section('noindex', true)
@section('title', translate('Checkout'))
@section('header_style', 'no_header')
@section('container', 'container container-default')

@section('main')
    @if ($trx->isUnpaid())
        <livewire:checkout :trx="$trx" />
    @else
        <div class="row justify-content-center py-5">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="modern-card-2 p-5 text-center">
                    <div class="success-icon mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success icon-circle-xl">
                            <i class="bi bi-check2-circle display-3"></i>
                        </div>
                    </div>

                    @if ($trx->isTypePurchase())
                        <h2 class="fw-bold mb-3">{{ translate('Payment Successful!') }}</h2>
                        <p class="text-muted mb-4 fs-18 px-xl-5">
                            {{ translate('Thank you for your purchase. Your payment has been completed and your products are ready to download.') }}
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="{{ route('user.purchase.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                                <i class="bi bi-download me-2"></i>{{ translate('My Purchases') }}
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg rounded-pill px-4 fw-bold border">
                                {{ translate('Back to Home') }}
                            </a>
                        </div>
                    @elseif($trx->isTypeDeposit())
                        <h2 class="fw-bold mb-3">{{ translate('Deposit Successful!') }}</h2>
                        <p class="text-muted mb-4 fs-18 px-xl-5">
                            {{ translate('Your payment has been processed and and your wallet has been updated successfully.') }}
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="{{ route('user.wallet.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                                <i class="bi bi-wallet2 me-2"></i>{{ translate('View Wallet') }}
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg rounded-pill px-4 fw-bold border">
                                {{ translate('Back to Home') }}
                            </a>
                        </div>
                    @elseif($trx->isTypePremium())
                        <h2 class="fw-bold mb-3">{{ translate('Premium Activated!') }}</h2>
                        <p class="text-muted mb-4 fs-18 px-xl-5">
                            {{ translate('Welcome to premium! Your membership has been activated and you now have full access to all benefits.') }}
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="{{ route('user.settings.premium') }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                                <i class="bi bi-gem me-2"></i>{{ translate('My Membership') }}
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg rounded-pill px-4 fw-bold border">
                                {{ translate('Back to Home') }}
                            </a>
                        </div>
                    @elseif($trx->isTypeSupportPurchase() || $trx->isTypeSupportExtend())
                        <h2 class="fw-bold mb-3">{{ translate('Support Extended!') }}</h2>
                        <p class="text-muted mb-4 fs-18 px-xl-5">
                            {{ translate('Your support period has been updated successfully. You can now continue to receive assistance for your purchase.') }}
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="{{ route('user.purchase.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                                <i class="bi bi-patch-check me-2"></i>{{ translate('My Purchases') }}
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg rounded-pill px-4 fw-bold border">
                                {{ translate('Back to Home') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection

