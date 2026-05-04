<div class="section">
    <div class="row g-4">
        <!-- Billing & Payment (Left Column on Desktop) -->
        <div class="col-12 col-xl-7 order-2 order-xl-1">
            <form id="checkoutForm" action="{{ route('checkout.process', hash_encode($trx->id)) }}" method="POST">
                @csrf

                <!-- Payment Methods Section -->
                <div class="card p-4 mb-4 rounded-4 border">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">{{ translate('Payment Method') }}</h5>
                        <div class="text-primary small">
                            <i class="bi bi-shield-lock me-1"></i>{{ translate('Secure encryption') }}
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach ($paymentGateways as $paymentGateway)
                            <div class="col-6 col-md-4">
                                <label class="payment-option-wrapper w-100 h-100 hover-bg-light cursor-pointer"
                                    for="{{ $paymentGateway->alias }}" role="button">
                                    <input class="form-check-input d-none" type="radio" name="payment_method"
                                        wire:model.live="payment_method"
                                        value="{{ $paymentGateway->alias }}" id="{{ $paymentGateway->alias }}"
                                        @checked($payment_method == $paymentGateway->alias)>

                                    <div class="payment-option-card p-3 border rounded text-center h-100 d-flex flex-column align-items-center justify-content-center position-relative transition-ease-out {{ $payment_method == $paymentGateway->alias ? 'border-primary bg-primary-subtle' : '' }}">
                                        <div class="payment-logo mb-2">
                                            <img src="{{ $paymentGateway->logo_url }}" alt="{{ $paymentGateway->name }}"
                                                class="img-fluid" style="max-height: 30px;">
                                        </div>
                                        <span class="small fw-semibold d-block text-truncate w-100">{{ $paymentGateway->name }}</span>

                                        @if ($paymentGateway->isAccountBalance())
                                            <div class="badge bg-success-subtle text-success mt-1 fw-normal">
                                                {{ getAmount(authUser()->balance) }}
                                            </div>
                                        @endif

                                        @if($payment_method == $paymentGateway->alias)
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <i class="bi bi-check-circle-fill text-primary small"></i>
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Billing Address Section -->
                <div class="card p-4 rounded-4 border">
                    <h5 class="fw-bold mb-4">{{ translate('Billing Details') }}</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('First Name') }}</label>
                            <input type="text" class="form-control" value="{{ authUser()->firstname }}" disabled>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('Last Name') }}</label>
                            <input type="text" class="form-control" value="{{ authUser()->lastname }}" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('Address line 1') }}</label>
                            <input type="text" wire:model="address_line_1" name="address_line_1" class="form-control" required placeholder="{{ translate('House / Road / Area') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('Address line 2') }}</label>
                            <input type="text" wire:model="address_line_2" name="address_line_2" class="form-control" placeholder="{{ translate('Apartment, suite, unit etc. (optional)') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('City') }}</label>
                            <input type="text" wire:model="city" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('State') }}</label>
                            <input type="text" wire:model="state" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('Postal code') }}</label>
                            <input type="text" wire:model="zip" name="zip" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ translate('Country') }}</label>
                            <select wire:model.live="country" name="country" class="form-select" required>
                                <option value="">-- {{ translate('Select Country') }} --</option>
                                @foreach (countries() as $countryCode => $countryName)
                                    <option value="{{ $countryCode }}" @selected($countryCode == $country)>{{ $countryName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-4 order-3 d-xl-none">
                <button form="checkoutForm" class="btn btn-primary btn-lg w-100 rounded-pill fw-semibold checkout-button">
                    {{ translate('Complete Purchase') }}<i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

        <!-- Order Summary (Right Column on Desktop) -->
        <div class="col-12 col-xl-5 order-1 order-xl-2">
            <div class="modern-card-2 p-4 position-sticky" style="top: 2rem;">
                <h5 class="fw-bold mb-4">{{ translate('Checkout Summary') }}</h5>

                <div class="order-items mb-4">
                    @if ($trx->isTypePurchase())
                        @foreach ($trx->trxProducts as $trxProduct)
                            @php $product = $trxProduct->product; @endphp
                            <div class="item-row d-flex py-3 border-bottom-dashed">
                                <div class="item-thumb image-fluid me-3 flex-shrink-0">
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                                </div>
                                <div class="item-details flex-grow-1">
                                    <h6 class="mb-1 fw-medium">
                                        <a href="{{ $product->view_link }}" class="text-dark fs-15 hover-underline">
                                            {{ truncateText($product->name, 45) }}
                                        </a>
                                    </h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 small">
                                        <div>
                                            <span class="text-muted">{{ translate('License') }}:</span>
                                            {{ $trxProduct->isRegularLicense() ? translate('Regular') : translate('Extended') }}
                                        </div>
                                        <span class="text-muted">&bull;</span>
                                        <div>
                                            <span class="text-muted">{{ translate('Qty') }}:</span>
                                            {{ $trxProduct->quantity }}
                                        </div>
                                        @if ($trxProduct->support)
                                            <span class="text-muted">&bull;</span>
                                            <div class="text-success">
                                                <span class="text-muted">{{ translate('Support') }}:</span>
                                                {{ $trxProduct->support->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="item-price text-end ms-1">
                                    <span class="fw-bold d-block">{{ getAmount($trxProduct->price) }}</span>
                                    @if ($trxProduct->support)
                                        <span class="text-success small d-block">
                                            + {{ getAmount($trxProduct->support->total) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                @if ($trx->isTypeSupportPurchase() || $trx->isTypeSupportExtend())
                    <div class="order-product-support mb-4">
                        @php $product = $trx->purchase->product; @endphp
                        <div class="item-row d-flex py-3 border-bottom-dashed">
                            <div class="item-thumb image-fluid me-3 flex-shrink-0">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                            </div>
                            <div class="item-details flex-grow-1">
                                <h6 class="mb-1 fw-medium">
                                    <a href="{{ $product->view_link }}" class="text-dark fs-15 hover-underline">
                                        {{ truncateText($product->name, 45) }}
                                    </a>
                                </h6>
                                <div class="text-muted small">
                                    {{ translate('by') }} <a href="{{ $product->seller->view_link }}" class="text-reset hover-primary-underline">
                                        {{ $product->seller->username }}
                                    </a>
                                </div>
                            </div>
                            <div class="item-price text-end text-success text-uppercase small ms-1">
                                <i class="bi bi-bag-check me-1"></i>{{ translate('Purchased') }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="order-totals bg-light p-3 rounded mb-4">
                    @if ($trx->isTypePurchase())
                        <div class="d-flex justify-content-between fw-medium mb-2">
                            <span class="small text-uppercase">{{ translate('Product Price') }}</span>
                            <span>{{ getAmount($summary['product_total']) }}</span>
                        </div>

                        @if ($summary['support_total'] > 0)
                            <div class="d-flex justify-content-between fw-medium mb-2">
                                <span class="small text-uppercase">{{ translate('Support Price') }}</span>
                                <span class="fw-medium">{{ getAmount($summary['support_total']) }}</span>
                            </div>
                        @endif
                    @elseif($trx->isTypeDeposit())
                        <div class="d-flex justify-content-between fw-bold py-2">
                            <span>{{ translate('Deposit Amount') }}</span>
                            <span>{{ getAmount($trx->amount) }}</span>
                        </div>
                    @elseif($trx->isTypeSupportPurchase())
                        <div class="d-flex justify-content-between fw-bold py-2">
                            <span>{{ translate('Support Purchase: :support', ['support' => $trx->support->name]) }}</span>
                            <span>{{ getAmount($trx->amount) }}</span>
                        </div>
                    @elseif($trx->isTypeSupportExtend())
                        <div class="d-flex justify-content-between fw-bold py-2">
                            <span>{{ translate('Support Extension: :support', ['support' => $trx->support->name]) }}</span>
                            <span>{{ getAmount($trx->amount) }}</span>
                        </div>
                    @else
                        <div class="d-flex justify-content-between fw-bold py-2">
                            <span>{{ translate('Transaction Amount') }}</span>
                            <span>{{ getAmount($trx->amount) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2 border-top pt-2">
                        <span class="small fw-semibold text-uppercase">{{ translate('Subtotal') }}</span>
                        <span class="fw-medium">{{ getAmount($summary['subtotal']) }}</span>
                    </div>

                    @if ($summary['tax'])
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ $summary['tax']['name'] }} ({{ $summary['tax']['rate'] }}%)</span>
                            <span class="fw-medium text-danger">+ {{ getAmount($summary['tax']['amount']) }}</span>
                        </div>
                    @endif

                    @if ($summary['gateway'])
                        <div class="d-flex justify-content-between fw-medium">
                            <span class="small text-uppercase">{{ translate('Processing Fees') }}</span>
                            <span class="fw-medium text-danger">+ {{ getAmount($summary['gateway']['amount']) }}</span>
                        </div>
                    @endif

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 fw-bold mb-0">{{ translate('Total') }}</span>
                        <span class="h4 fw-bold mb-0 text-primary">{{ getAmount($summary['total']) }}</span>
                    </div>
                </div>

                <button form="checkoutForm" class="btn btn-primary btn-lg w-100 rounded-pill fw-semibold d-none d-xl-block mb-4 checkout-button">
                    {{ translate('Complete Purchase') }}<i class="bi bi-arrow-right ms-2"></i>
                </button>

                <!-- SSL Trust Badge Mini -->
                <div class="ssl-shield-badge d-flex align-items-center justify-content-center p-3 border rounded bg-white">
                    <div class="shield-icon me-3">
                        <i class="bi bi-shield-check text-success fs-3"></i>
                    </div>
                    <div class="shield-text">
                        <h6 class="mb-0 fw-bold small text-uppercase ls-1">{{ translate('SSL Secure Checkout') }}</h6>
                        <p class="text-muted fs-12 mb-0">{{ translate('256-bit end-to-end encryption') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
