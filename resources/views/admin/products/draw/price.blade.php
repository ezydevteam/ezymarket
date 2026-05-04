<div class="product-price-info">
    <div class="price-item mb-2">
        <p class="text-muted small mb-0 uppercase fw-bold ls-1">{{ translate('Regular') }}</p>
        @if($product->isOnDiscount())
            <span class="text-decoration-line-through text-muted small me-1">{{ getAmount($product->price->regular) }}</span>
            <span class="text-success fw-bold">{{ getAmount($product->price->discount_regular) }}</span>
        @else
            <span class="fw-bold">{{ getAmount($product->price->regular) }}</span>
        @endif
    </div>
    
    @if($product->hasExtendedPrice())
    <div class="price-item">
        <p class="text-muted small mb-0 uppercase fw-bold ls-1">{{ translate('Extended') }}</p>
        @if($product->isExtendedOnDiscount())
            <span class="text-decoration-line-through text-muted small me-1">{{ getAmount($product->price->extended) }}</span>
            <span class="text-success fw-bold">{{ getAmount($product->discount->price->extended) }}</span>
        @else
            <span class="fw-bold">{{ getAmount($product->price->extended) }}</span>
        @endif
    </div>
    @endif
</div>
