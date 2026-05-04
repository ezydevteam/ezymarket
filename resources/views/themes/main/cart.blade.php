@extends('themes.main.layouts.single')
@section('noindex', true)
@section('title', translate('My Cart'))
@section('header_style', 'no_header')
@section('container', 'container container-default')

@section('main')
@if ($cartCount > 0)
<div class="section">
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="section-header d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-cart-check-fill text-primary me-2"></i>{{ translate('My Cart') }}
                    <span class="text-muted fw-normal fs-15 ms-2">
                        ({{ $cartCount }} {{ translate(':label', ['label' => $cartCount > 1 ? 'items' : 'item']) }})
                    </span>
                </h4>
                <a href="{{ route('products.index') }}"
                    class="btn btn-link text-primary p-0 fw-medium text-decoration-none hover-underline">
                    <i class="bi bi-arrow-left me-1"></i>{{ translate('Continue Browsing') }}
                </a>
            </div>

            <div class="cart-items-container">
                @foreach ($cartProducts as $cartProduct)
                @php
                    $product = $cartProduct->product;
                    $freePackage = freeSupportPackage();
                    $paidPackage = $product->supportPackage;
                @endphp
                <div class="card p-3 p-md-4 mb-4 product-cart-item border transition-all">
                    <div class="row g-3 g-md-4 align-items-center">
                        <!-- Image -->
                        <div class="col-auto">
                            <a href="{{ $product->view_link }}" class="d-block product-img-wrapper">
                                <div class="product-img product-img-md rounded-4 overflow-hidden shadow-sm">
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                        class="img-fluid" />
                                </div>
                            </a>
                        </div>

                        <!-- Details -->
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="pe-3">
                                    <a href="{{ $product->view_link }}"
                                        class="h6 mb-1 d-block text-reset hover-primary">
                                        {{ $product->name }}
                                    </a>
                                    <div class="small text-muted d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle fs-11"></i>
                                        <a href="{{ $product->seller?->profile_link }}"
                                            class="text-reset fs-13 hover-primary-underline"
                                            title="{{ translate('View Seller Profile') }}">
                                            {{ $product->seller?->username }}
                                        </a>
                                        <span class="dot-seperator"></span>
                                        <span class="badge bg-light text-muted rounded-pill border">
                                            {{ $cartProduct->license_type?->label() }}
                                        </span>
                                    </div>
                                </div>
                                <form action="{{ route('cart.remove-product', $cartProduct->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-light btn-sm shadow-sm icon-circle hover-shadow"
                                        title="{{ translate('Remove') }}" type="submit">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </form>
                            </div>

                            <hr class="my-3">

                            <div class="row g-3 align-items-end">
                                <!-- Controls -->
                                <div class="col-12 col-md-8">
                                    <form action="{{ route('cart.update-product', $cartProduct->id) }}" method="POST"
                                        class="cart-update-form">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-4">
                                                <label class="form-label small text-muted fw-medium mb-1">
                                                    {{ translate('License') }}
                                                </label>
                                                <select name="license_type"
                                                    class="form-select form-select-sm rounded-3 border-light-subtle bg-light-subtle">
                                                    <option value="1" @selected($cartProduct->isRegularLicense())>
                                                        {{ translate('Regular') }}
                                                    </option>
                                                    <option value="2" @selected($cartProduct->isExtendedLicense())>
                                                        {{ translate('Extended') }}
                                                    </option>
                                                </select>
                                            </div>
                                            @if (@$settings->product->support_status && $product->isSupported())
                                            <div class="col-6 col-sm-4">
                                                <label class="form-label small text-muted fw-medium mb-1">
                                                    {{ translate('Support') }}
                                                </label>
                                                <select name="support"
                                                    class="form-select form-select-sm rounded-3 border-light-subtle bg-light-subtle">
                                                    @if ($freePackage)
                                                    <option value="{{ $freePackage->id }}"
                                                        @selected($freePackage->id == $cartProduct->support_package_id || !$cartProduct->support_package_id)>
                                                        {{ $freePackage->title }} ({{ translate('Included') }})
                                                    </option>
                                                    @else
                                                    <option value="" @selected(!$cartProduct->support_package_id)>
                                                        {{ translate('No Free Support') }}
                                                    </option>
                                                    @endif

                                                    @if ($paidPackage)
                                                    <option value="{{ $paidPackage->id }}"
                                                        @selected($paidPackage->id == $cartProduct->support_package_id)>
                                                        {{ $paidPackage->title }}
                                                    </option>
                                                    @endif
                                                </select>
                                            </div>
                                            @endif
                                            <div class="col-6 col-sm-2">
                                                <label class="form-label small fw-medium text-muted mb-1">
                                                    {{ translate('Qty') }}
                                                </label>
                                                <input type="number" name="quantity" min="1" max="50"
                                                    class="form-control form-control-sm rounded-3 border-light-subtle bg-light-subtle"
                                                    value="{{ $cartProduct->quantity }}">
                                            </div>
                                            <div class="col-6 col-sm-2 d-flex align-items-end">
                                                <button
                                                    class="btn btn-outline-primary btn-sm rounded-3 border-light-subtle">
                                                    {{ translate('Update') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Price -->
                                <div class="col-12 col-md-4 text-start text-md-end">
                                    <div class="price-stack">
                                        @php
                                        $unitPrice = $cartProduct->getUnitPrice();
                                        $selectedSupport = $cartProduct->supportPackage;
                                        $supportPrice = ($selectedSupport && !$selectedSupport->isFree())
                                        ? $selectedSupport->calculatePrice($unitPrice)
                                        : 0;
                                        $itemTotal = $cartProduct->getTotalAmountWithSupport();
                                        @endphp

                                        <div class="h5 fw-bold mb-0 text-primary">
                                            {{ getAmount($itemTotal, 2) }}
                                        </div>
                                        <div class="small text-gray-700 mt-1">
                                            @if($supportPrice > 0)
                                            <abbr title="{{ translate('Support package price') }}">
                                                + {{ getAmount($supportPrice, 2) }}
                                            </abbr>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $cartProducts->links() }}
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="col-12 col-xl-4">
            <div class="cart-order-summary">
                <div class="modern-card-2 p-4 border-0 shadow-sm overflow-visible">
                    <h5 class="fw-bold mb-4">{{ translate('Order Summary') }}</h5>

                    <div class="summary-details mb-4">
                        <div class="d-flex justify-content-between mb-3">
                            {{ translate('Subtotal') }}
                            <span class="fw-medium text-dark">
                                {{ getAmount($cartTotal, 2) }}
                            </span>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold mb-0">{{ translate('Total') }}</span>
                            <span class="h4 fw-bold mb-0 text-primary">
                                {{ getAmount($cartTotal, 2) }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('cart.checkout') }}" class="form-needs-login-modal cart-checkout-form mt-4"
                        method="POST">
                        @csrf
                        <button class="btn btn-primary btn-lg btn-modern w-100 rounded-pill shadow-sm fw-semibold">
                            <span>{{ translate('Proceed to Checkout') }}</span>
                            <i class="bi bi-arrow-right ms-2 fs-5"></i>
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <form action="{{ route('cart.empty') }}" method="POST">
                            @csrf
                            <button
                                class="btn btn-link text-danger w-100 text-decoration-none small action-confirm p-0">
                                <i class="bi bi-trash3 me-1"></i> {{ translate('Clear entire cart') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 text-center">
                        <div class="bg-white rounded-4 border border-dotted text-center p-3">
                            <div class="small fw-medium mb-2">
                                {{ translate('Need help with your order?') }}
                            </div>
                            <a href="{{ route('contact.index') }}" class="text-primary small fw-medium hover-underline">
                                {{ translate('Contact Support Team') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="py-5">
    <div class="modern-card-2 p-5 text-center border-0 shadow-sm max-w-600 mx-auto rounded-5 overflow-hidden">
        <div class="mb-4">
            <div class="empty-cart-icon-wrapper scale-up">
                <i class="bi bi-cart-x display-1"></i>
            </div>
        </div>
        <h3 class="fw-bold mb-3">{{ translate('Your Cart is Empty') }}</h3>
        <p class="text-muted mb-5 px-md-5">
            {{ translate('Your cart is currently empty. Explore our amazing products and find something you love
            today!') }}
        </p>
        <a href="{{ route('products.index') }}"
            class="btn btn-primary btn-lg btn-modern rounded-pill shadow fw-semibold">
            <i class="bi bi-bag-plus me-3"></i>{{ translate('Browse Products') }}
        </a>
    </div>
</div>
@endif
@endsection
