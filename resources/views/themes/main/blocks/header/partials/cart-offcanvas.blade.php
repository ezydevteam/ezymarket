<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $offcanvasId ?? 'offcanvasCart' }}"
    aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel">
            <i class="bi bi-cart3 me-1"></i>
            {{ translate('My Cart') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        @if(isset($cartProducts) && $cartProducts->count() > 0)
        <div class="list-group list-group-flush">
            @foreach($cartProducts as $item)
            <div class="list-group-item">
                <div class="d-flex gap-3 align-items-center">
                    <div class="flex-shrink-0">
                        <img src="{{ $item->product->thumbnail_url }}" class="image-fluid image-md rounded-3"
                            alt="{{ $item->product->name }}">
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="text-start mb-1 text-truncate">
                            <a href="{{ $item->product->view_link }}" class="text-reset fs-15 hover-underline">
                                {{ truncateText($item->product->name, 35) }}
                            </a>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                {{ $item->isRegularLicense() ? translate('Regular License') : translate('Extended
                                License') }}
                                @if($item->supportPackage)
                                <span class="ms-1">({{ $item->supportPackage->name }})</span>
                                @endif
                            </small>
                            <span class="fw-semibold">{{ getAmount($item->getTotalAmountWithSupport()) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 px-3">
            <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">{{ translate('Your cart is empty') }}</p>
            <div class="mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">{{ translate('Start Shopping')
                    }}</a>
            </div>
        </div>
        @endif
    </div>
    @if(isset($cartProducts) && $cartProducts->count() > 0)
    <div class="offcanvas-footer border-top p-3 bg-light">
        <div class="d-flex justify-content-between mb-3 align-items-center fw-bold">
            <span class="text-uppercase">{{ translate('Total') }}</span>
            <span class="fs-5">{{ getAmount($cartTotal ?? 0) }}</span>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('cart.index') }}" class="btn btn-outline-primary flex-fill">{{ translate('View Cart')
                }}</a>
            <form action="{{ route('cart.checkout') }}" method="POST" class="form-needs-login-modal flex-fill">
                @csrf
                <button class="btn btn-primary w-100">{{ translate('Checkout') }}</button>
            </form>
        </div>
    </div>
    @endif
</div>
