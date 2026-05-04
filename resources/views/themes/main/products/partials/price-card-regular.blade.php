@php
$regularTabLabel = $widgetSettings['regular_tab_label'] ?: translate('Regular');
$showExtraFeatures = $widgetSettings['show_extra_features'] ?? true;
$showBuyNowButton = $widgetSettings['show_buy_now_button'] ?? true;
$productInfoRaw = $widgetSettings['product_info'] ?? '';
$productInfoItems = $productInfoRaw ? array_map('trim', explode(',', $productInfoRaw)) : [];
@endphp

<div class="modern-price-card mb-0">
    <div class="d-flex align-items-start justify-content-between mb-3">
        <div>
            <h6 class="mb-0 fw-semibold">{{ $regularTabLabel }}</h6>
            <div class="text-muted small">{{ translate($regularPriceLabel) }}</div>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-primary mb-0 d-flex align-items-center justify-content-end gap-2">
                @if ($product->isOnDiscount())
                <span class="text-decoration-line-through text-gray-700 fs-4 fw-normal">
                    {{ getAmount($product->price->regular, 2, '.', '', true) }}
                </span>
                @endif
                {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular : $product->price->regular,
                2, '.', '', true) }}
            </h2>
        </div>
    </div>

    @if (@$settings->product->support_status && $product->isSupported())
    @php
    $freePackage = freeSupportPackage();
    $paidPackage = $product->supportPackage;
    $defaultSupportId = $freePackage?->id ?? '';
    @endphp
    <div class="list mb-4">
        @if (count($productInfoItems) > 0)
        @foreach ($productInfoItems as $info)
        <div class="list-product small text-gray-200 mb-0">
            <i class="bi bi-check-circle text-success me-2"></i>{{ $info }}
        </div>
        @endforeach
        @else
        <div class="list-product small text-gray-200 mb-0">
            <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Quality checked by :site',
            ['site' => @$settings->general->site_name ?? 'Ezymarket'])}}
        </div>
        <div class="list-product small text-gray-200 mb-0">
            <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Future updates') }}
        </div>
        @endif

        @if ($freePackage)
        <div class="list-product small text-gray-200 mb-0">
            <i class="bi bi-check-circle text-success me-2"></i>{{ translate(':support from :seller',
            ['support' => $freePackage->title, 'seller' => $product->seller->username]) }}
        </div>
        @endif

        @if ($paidPackage)
        <div class="d-flex flex-column gap-2 mt-2">
            @php
            $finalPriceReg = $product->isOnDiscount() ? $product->discount->price->regular : $product->price->regular;
            $supportPriceRegValue = $paidPackage->calculatePrice($finalPriceReg);
            $supportPriceReg = getAmount($supportPriceRegValue);
            @endphp
            <div class="form-check modern-support-checkbox-regular">
                <input class="form-check-input border-secondary" type="checkbox" value="{{ $paidPackage->id }}"
                    id="reg-supp-reg-0">
                <label class="form-check-label d-flex align-items-center justify-content-between gap-1"
                    for="reg-supp-reg-0">
                    <div>{{ $paidPackage->title }}</div>
                    <div class="fw-semibold">
                        @if ($product->isOnDiscount())
                        <span class="text-decoration-line-through text-gray-700 fw-normal me-1">
                            {{ getAmount($paidPackage->calculatePrice($product->price->regular)) }}
                        </span>
                        @endif
                        <span class="text-dark">{{ $supportPriceReg }}</span>
                    </div>
                </label>
            </div>
        </div>
        @endif

        @if ($showExtraFeatures && count($regularExtraFeatures))
        <div class="list-product mt-2 border-top pt-2">
            <button class="btn btn-sm p-0 product-feature-btn fw-500">
                <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
            </button>
        </div>
        <div class="card-v product-features-box border rounded-3 bg-light-subtle p-0 mt-2 d-none">
            <div class="card-body px-3 py-2">
                <ul class="product-extra-features mb-0 small">
                    @foreach ($regularExtraFeatures as $feature)
                    <li class="extra-features-list">{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
    @elseif(@$settings->product->support_status)
    <div class="list mb-4">
         <div class="list-product small text-muted mb-0 mt-2">
            <i class="bi bi-x-circle text-danger me-2"></i>{{ translate('No Support Available') }}
        </div>
    </div>
    @endif

    <div class="d-flex flex-column flex-sm-row gap-3">
        <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form flex-fill" method="POST"
            data-default-support="{{ $defaultSupportId ?? '' }}">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="license_type" value="1" class="license-type">
            <input type="hidden" name="support" id="regularAddCartSupport" value="{{ $defaultSupportId ?? '' }}">
            <button class="btn {{ $addCartBtnStyle ?? 'btn-outline-primary' }} btn-modern rounded-3 py-2 w-100"
                @disabled(authUser()?->id ==
                $product->seller_id)>
                @if($addCartBtnIcon ?? null)<i class="bi {{ $addCartBtnIcon }} me-2"></i>@endif{{ translate('Add to
                Cart') }}
            </button>
        </form>
        @if ($showBuyNowButton)
        <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
            class="form-needs-login-modal buy-now-form flex-fill" method="POST" id="regularBuyNowForm"
            data-default-support="{{ $defaultSupportId ?? '' }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="license_type" value="1">
            <input type="hidden" name="support" class="buy-now-support-input" id="regularBuyNowSupportInput"
                value="{{ $defaultSupportId ?? '' }}">
            <button class="btn {{ $buyNowBtnStyle ?? 'btn-primary' }} btn-modern rounded-3 py-2 w-100"
                name="regular_license" @disabled(authUser()?->id == $product->seller_id)>
                @if($buyNowBtnIcon ?? null)<i class="bi {{ $buyNowBtnIcon }} me-1"></i>@endif{{ translate('Buy Now') }}
            </button>
        </form>
        @endif
    </div>
</div>
