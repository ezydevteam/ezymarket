<div class="modern-price-card h-100 mb-0">
    {{-- ================= REGULAR LICENSE PANE ================= --}}
    <div id="modern-regular-pane">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <div class="text-gray-700 small mb-1">{{ $product->regular_price_label ?? translate('Price') }}</div>
                <h2 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                    {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular :
                    $product->price->regular, 2, '.', '', true) }}
                    @if ($product->isOnDiscount())
                    <span class="text-decoration-line-through text-muted fs-5 fw-normal">
                        {{ getAmount($product->price->regular, 2, '.', '', true) }}
                    </span>
                    @endif
                </h2>
            </div>
            @if($product->hasExtendedPrice())
            <div class="text-end">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-light pointer user-select-none"
                    data-license-toggle="extended" style="transition: all 0.2s; cursor: pointer;">
                    <span class="fw-semibold text-dark small">{{ translate('Regular License') }}</span>
                    <i class="bi bi-chevron-expand text-muted small"></i>
                </div>
            </div>
            @endif
        </div>

        @if (@$settings->product->support_status && $product->isSupported())
        @php
        $freePackage = freeSupportPackage();
        $paidPackage = $product->supportPackage;
        $defaultSupportId = $freePackage?->id ?? '';
        @endphp
        <div class="list mb-4">
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Quality checked by :site',
                ['site' => @$settings->general->site_name ?? 'Ezymarket'])}}
            </div>
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Future updates') }}
            </div>
            @if ($freePackage)
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate(':support from :seller',
                ['support' => $freePackage->title, 'seller' => $seller->username]) }}
                <a href="{{ route('page', @$settings->product->support_policy_slug ?? 'product-support-policy') }}"
                    class="d-inline-block text-reset ms-1" target="_blank"
                    title="{{ translate('View Support Policy') }}">
                    <i class="bi bi-info-circle text-gray-700"></i>
                </a>
            </div>
            @endif

            @if ($paidPackage)
            <div class="d-flex flex-column gap-2 mt-2">
                @php
                $finalPriceReg = $product->isOnDiscount() ? $product->discount->price->regular :
                $product->price->regular;
                $supportPriceRegValue = $paidPackage->calculatePrice($finalPriceReg);
                $supportPriceReg = getAmount($supportPriceRegValue);
                @endphp
                <div class="form-check modern-support-checkbox-regular">
                    <input class="form-check-input" type="checkbox" value="{{ $paidPackage->id }}"
                        id="mod-supp-reg-0">
                    <label class="form-check-label small cursor-pointer d-flex align-items-center gap-1"
                        for="mod-supp-reg-0">
                        <span>{{ $paidPackage->title }}</span>
                        <span class="ms-1 fw-medium d-flex align-items-center gap-1">
                            (
                            @if ($product->isOnDiscount())
                            <span class="text-decoration-line-through text-muted" style="font-size: 0.9em;">{{
                                getAmount($paidPackage->calculatePrice($product->price->regular)) }}</span>
                            @endif
                            <span class="text-dark">{{ $supportPriceReg }}</span>
                            )
                        </span>
                    </label>
                </div>
            </div>
            @endif

            @if (count($regularExtraFeatures))
            <div class="list-product mt-2">
                <span role="button" class="product-feature-btn cursor-pointer fw-500 text-dark small">
                    <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                    <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                </span>
            </div>
            <div class="card-v product-features-box d-none border rounded-3 bg-light-subtle p-0 mt-2">
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
             <div class="list-product small text-gray-700 mb-0">
                <i class="bi bi-x-circle text-danger me-2"></i>{{ translate('No Support Available') }}
            </div>
        </div>
        @endif

        <div class="d-flex flex-column flex-sm-row gap-3">
            <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST"
                data-default-support="{{ $defaultSupportId ?? '' }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="license_type" value="1" class="license-type">
                <input type="hidden" name="support" id="regularAddCartSupport" value="{{ $defaultSupportId ?? '' }}">
                <button class="btn btn-outline-primary btn-modern rounded-3 px-5 py-2" @disabled(authUser() &&
                    authUser()->id ==
                    $product->seller_id)>
                    <i class="bi bi-cart-plus me-2"></i>{{ translate('Add to Cart') }}
                </button>
            </form>
            @if (@$settings->product->buy_now_button)
            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                class="form-needs-login-modal buy-now-form" method="POST" id="modernBuyNowFormReg"
                data-default-support="{{ $defaultSupportId ?? '' }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="license_type" value="1">
                <input type="hidden" name="support" class="buy-now-support-input" id="modernBuyNowSupportInputReg"
                    value="{{ $defaultSupportId ?? '' }}">
                <button class="btn btn-primary btn-modern rounded-3 px-5 py-2" name="regular_license"
                    @disabled(authUser() && authUser()->id == $product->seller_id)>
                    <i class="bi bi-lightning-charge me-1"></i>{{ translate('Buy Now') }}
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ================= EXTENDED LICENSE PANE ================= --}}
    @if($product->hasExtendedPrice())
    <div id="modern-extended-pane" class="d-none">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <div class="text-gray-700 small mb-1">{{ $product->extended_price_label ?? translate('Price') }}</div>
                <h2 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                    {{ getAmount($product->isOnDiscount() ? $product->discount->price->extended :
                    $product->price->extended, 2, '.', '', true) }}
                    @if ($product->isOnDiscount())
                    <span class="text-decoration-line-through text-muted fs-5 fw-normal">
                        {{ getAmount($product->price->extended, 2, '.', '', true) }}
                    </span>
                    @endif
                </h2>
            </div>
            <div class="text-end">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-light pointer user-select-none"
                    data-license-toggle="regular" style="transition: all 0.2s; cursor: pointer;">
                    <span class="fw-semibold text-dark small">{{ translate('Extended License') }}</span>
                    <i class="bi bi-chevron-expand text-muted small"></i>
                </div>
            </div>
        </div>

        @if (@$settings->product->support_status && $product->isSupported())
        @php
        $freePackage = freeSupportPackage();
        $paidPackage = $product->supportPackage;
        $defaultSupportId = $freePackage?->id ?? '';
        @endphp
        <div class="list mb-4">
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Quality checked by :site',
                ['site' => @$settings->general->site_name ?? 'Ezymarket']) }}
            </div>
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate('Future updates') }}
            </div>
            @if ($freePackage)
            <div class="list-product small text-gray-200 mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>{{ translate(':support from :seller',
                ['support' => $freePackage->title, 'seller' => $seller->username]) }}
                <a href="{{ route('page', @$settings->product->support_policy_slug ?? 'product-support-policy') }}"
                    class="d-inline-block text-reset ms-1" target="_blank"
                    title="{{ translate('View Support Policy') }}">
                    <i class="bi bi-info-circle text-gray-700"></i>
                </a>
            </div>
            @endif

            @if ($paidPackage)
            <div class="d-flex flex-column gap-2 mt-2">
                @php
                $finalPriceExt = ($product->isOnDiscount() && $product->isExtendedOnDiscount()) ?
                $product->discount->price->extended : $product->price->extended;
                $supportPriceExtValue = $paidPackage->calculatePrice($finalPriceExt);
                $supportPriceExt = getAmount($supportPriceExtValue);
                @endphp
                <div class="form-check modern-support-checkbox-extended">
                    <input class="form-check-input" type="checkbox" value="{{ $paidPackage->id }}"
                        id="mod-supp-ext-0">
                    <label class="form-check-label small cursor-pointer d-flex align-items-center gap-1"
                        for="mod-supp-ext-0">
                        <span>{{ $paidPackage->title }}</span>
                        <span class="ms-1 fw-medium d-flex align-items-center gap-1">
                            (
                            @if ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                            <span class="text-decoration-line-through text-muted" style="font-size: 0.9em;">{{
                                getAmount($paidPackage->calculatePrice($product->price->extended)) }}</span>
                            @endif
                            <span class="text-dark">{{ $supportPriceExt }}</span>
                            )
                        </span>
                    </label>
                </div>
            </div>
            @endif

            @if (count($extendedExtraFeatures ?? []))
            <div class="list-product mt-2">
                <span role="button" class="product-feature-btn cursor-pointer fw-500 text-dark small">
                    <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                    <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                </span>
            </div>
            <div class="card-v product-features-box d-none border rounded-3 bg-light-subtle p-0 mt-2">
                <div class="card-body px-3 py-2">
                    <ul class="product-extra-features mb-0 small">
                        @foreach ($extendedExtraFeatures ?? [] as $feature)
                        <li class="extra-features-list">{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
        @elseif(@$settings->product->support_status)
        <div class="list mb-4">
             <div class="list-product small text-gray-700 mb-0">
                <i class="bi bi-x-circle text-danger me-2"></i>{{ translate('No Support Available') }}
            </div>
        </div>
        @endif

        <div class="d-flex flex-column flex-sm-row gap-3">
            <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST"
                data-default-support="{{ $defaultSupportId ?? '' }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="license_type" value="2" class="license-type">
                <input type="hidden" name="support" id="extendedAddCartSupport" value="{{ $defaultSupportId ?? '' }}">
                <button class="btn btn-outline-primary btn-modern rounded-3 px-5 py-2" @disabled(authUser() &&
                    authUser()->id ==
                    $product->seller_id)>
                    <i class="bi bi-cart-plus me-2"></i>{{ translate('Add to Cart') }}
                </button>
            </form>
            @if (@$settings->product->buy_now_button)
            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                class="form-needs-login-modal buy-now-form" method="POST" id="modernBuyNowFormExt"
                data-default-support="{{ $defaultSupportId ?? '' }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="license_type" value="2">
                <input type="hidden" name="support" class="buy-now-support-input" id="modernBuyNowSupportInputExt"
                    value="{{ $defaultSupportId ?? '' }}">
                <button class="btn btn-primary btn-modern rounded-3 px-5 py-2" name="extended_license"
                    @disabled(authUser() && authUser()->id == $product->seller_id)>
                    <i class="bi bi-lightning-charge me-1"></i>{{ translate('Buy Now') }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    @if (@$settings->product->support_status && $product->isSupported())
    <div class="mt-4 pt-3 border-top text-center">
        <div class="d-inline-flex align-items-center gap-3 text-muted small">
            <a href="/{{ @$settings->product->support_policy_slug ?? 'product-support-policy' }}"
                class="text-decoration-none text-muted hover-primary" target="_blank">
                <i class="bi bi-shield-check me-1"></i>{{ translate('Support policy') }}
            </a>
            <span class="vr"></span>
            <a href="/{{ @$settings->product->license_terms_slug ?? 'license-terms' }}"
                class="text-decoration-none text-muted hover-primary" target="_blank">
                <i class="bi bi-file-earmark-text me-1"></i>{{ translate('License terms') }}
            </a>
        </div>
    </div>
    @endif
</div>
