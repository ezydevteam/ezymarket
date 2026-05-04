<div class="product-price-list d-flex flex-column gap-2 py-1">
    {{-- Free Status Badge --}}
    @if ($product->isFree())
        <div class="d-flex align-items-center">
            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small border border-success-subtle">
                <i class="bi bi-gift me-1"></i> {{ translate('FREE') }}
            </span>
        </div>
    @endif

    @if ($product->isPurchasingEnabled())
        {{-- Regular license Price --}}
        <div class="price-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-2 py-0.5 xsmall fw-medium uppercase letter-spacing-1">
                    {{ translate('REGULAR') }}
                </span>
            </div>
            <div class="ms-auto text-end">
                @if ($product->isOnDiscount())
                    <div class="text-gray-700 text-decoration-line-through fs-13 me-1 mb-0.5">
                        {{ getAmount($product->price->regular) }}
                    </div>
                    <div class="fw-bold text-success fs-14">
                        {{ getAmount($product->discount->price->regular) }}
                    </div>
                @else
                    <div class="fw-bold text-dark fs-14">
                        {{ getAmount($product->price->regular) }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Extended license Price --}}
        @if ($product->hasExtendedPrice())
            <div class="price-item d-flex align-items-center justify-content-between mt-1 pt-1 border-top border-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 xsmall fw-medium uppercase letter-spacing-1">
                        {{ translate('EXTENDED') }}
                    </span>
                </div>
                <div class="ms-auto text-end">
                    @if ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                        <div class="text-gray-700 text-decoration-line-through fs-13 me-1 mb-0.5">
                            {{ getAmount($product->price->extended) }}
                        </div>
                        <div class="fw-bold text-success fs-14">
                            {{ getAmount($product->discount->price->extended) }}
                        </div>
                    @else
                        <div class="fw-bold text-primary fs-14">
                            {{ getAmount($product->price->extended) }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
