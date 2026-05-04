@if ($product->isPurchasingEnabled() && !(isPremiumAvailable() && $product->isPremium() &&
authUser()?->isPremiumMember()))
@php
$showWidgetTitle = $widgetSettings['show_title'] ?? false;
$style = $widgetSettings['style'] ?? 'style-1';
$regularTabLabel = $widgetSettings['regular_tab_label'] ?: translate('Regular');
$extendedTabLabel = $widgetSettings['extended_tab_label'] ?: translate('Extended');
$showExtendedPrice = $widgetSettings['show_extended_price'] ?? true;
$showExtraFeatures = $widgetSettings['show_extra_features'] ?? true;
$showBuyNowButton = $widgetSettings['show_buy_now_button'] ?? true;

$addCartBtnStyle = $widgetSettings['add_to_cart_btn_style'] ?? 'btn-primary';
$addCartBtnIcon = $widgetSettings['add_to_cart_btn_icon'] ?? '';
$buyNowBtnStyle = $widgetSettings['buy_now_btn_style'] ?? 'btn-outline-primary';
$buyNowBtnIcon = $widgetSettings['buy_now_btn_icon'] ?? '';

$productInfoRaw = $widgetSettings['product_info'] ?? '';
$productInfoItems = $productInfoRaw ? array_map('trim', explode(',', $productInfoRaw)) : [];
$supportPolicySlug = $widgetSettings['support_policy_slug'] ?? 'product-support-policy';
$showLicenseTermsLink = $widgetSettings['show_license_terms_link'] ?? true;
$licenseTermsSlug = $widgetSettings['license_terms_slug'] ?? 'license-terms';

$regularPriceLabel = $product->regular_price_label ?? 'For single project';
$extendedPriceLabel = $product->extended_price_label ?? 'For unlimited projects';
$regularExtraFeatures = $product->getRegularExtraFeatures() ?? [];
$extendedExtraFeatures = $product->getExtendedExtraFeatures() ?? [];
$hasExtendedPrice = $product->hasExtendedPrice() && $showExtendedPrice;
$showSupportPolicy = ($widgetSettings['show_support_policy_link'] ?? true)
&& @$settings->product->support_status && $product->isSupported();

$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp

<div class="widget-product-price-card {{ $style }}">
    @if ($activePurchase)
    {{-- Purchased Product Tabs --}}
    <div class="purchased-tabs mb-0 border-bottom">
        <ul class="nav nav-tabs nav-fill border-0" id="purchasedTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link bg-transparent position-relative active py-3 fw-semibold text-gray-700 border-0 fs-14 transition-all"
                    id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab"
                    aria-controls="overview-pane" aria-selected="true">
                    {{ translate('Overview') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link bg-transparent position-relative py-3 fw-semibold text-gray-700 border-0 fs-14 transition-all"
                    id="buyLicenseTabTrigger" data-bs-toggle="tab" data-bs-target="#buy-license-pane" type="button"
                    role="tab" aria-controls="buy-license-pane" aria-selected="false">
                    {{ translate('Buy license') }}
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="purchasedTabContent">
        <div class="tab-pane fade show active p-4" id="overview-pane" role="tabpanel" aria-labelledby="overview-tab">
            @themeInclude('products.partials.price-card-purchased')
        </div>
        <div class="tab-pane fade" id="buy-license-pane" role="tabpanel" aria-labelledby="buyLicenseTabTrigger">
            {{-- Standard Price Card Content wrapped below --}}
            @elseif ($showWidgetTitle)
            <div class="{{ $titlePadding }}">
                @include('widgets.partials.widget-title', [
                'title' => $widgetTitle ?? '',
                'widgetSettings' => $widgetSettings ?? []
                ])
            </div>
            @endif

            @if ($style === 'style-1')
            {{-- Style 1: Classic Tabs --}}
            @if ($hasExtendedPrice)
            <div class="product-price-section d-flex align-items-center gap-3 border-bottom">
                <ul class="nav product-price-tab" id="productPriceTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-capitalize fw-500" id="productRegularPrice"
                            data-bs-toggle="tab" data-bs-target="#product-regular-price" type="button" role="tab"
                            aria-controls="product-regular-price" aria-selected="true">
                            {{ $regularTabLabel }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-capitalize fw-500" id="productExtendedPrice" data-bs-toggle="tab"
                            data-bs-target="#product-extended-price" type="button" role="tab"
                            aria-controls="product-extended-price" aria-selected="false">
                            {{ $extendedTabLabel }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="productPriceTabContent">
                <div class="tab-pane show active p-3" id="product-regular-price" role="tabpanel"
                    aria-labelledby="productRegularPrice" tabindex="0">
                    @themeInclude('products.partials.price-card-regular')
                </div>
                <div class="tab-pane p-3" id="product-extended-price" role="tabpanel"
                    aria-labelledby="productExtendedPrice" tabindex="0">
                    @themeInclude('products.partials.price-card-extended')
                </div>
            </div>
            @else
            <div class="regular-price-card p-3" id="onlyRegularPrice">
                @themeInclude('products.partials.price-card-regular')
            </div>
            @endif

            @elseif ($style === 'style-2')
            {{-- Style 2: Dropdown Selector --}}
            <div class="style-2-price-card">
                @if ($hasExtendedPrice)
                <div class="product-price-dropdown position-relative" id="style2LicenseDropdownWrap">
                    {{-- Dropdown Toggle --}}
                    <div role="button" class="dropdown px-3 py-2 border-bottom cursor-pointer user-select-none"
                        data-bs-toggle="dropdown" aria-expanded="false" id="style2LicenseDropdownBtn">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold d-flex align-items-center fs-16 mb-0">
                                <span id="s2ActiveLicenseName">{{ $regularTabLabel ?: translate('Regular License')
                                    }}</span>
                                <i class="bi bi-caret-down-fill ms-2 text-muted fs-10"></i>
                            </h6>
                            <div class="price-display d-flex align-items-center gap-2">
                                <span class="text-decoration-line-through text-gray-700 fs-5" id="s2ActiveOldPrice">
                                    @if ($product->isOnDiscount())
                                    {{ getAmount($product->price->regular, 2, '.', '', true) }}
                                    @endif
                                </span>
                                <span class="fw-bold fs-3 text-{{ $product->isOnDiscount() ? 'primary' : 'dark' }}"
                                    id="s2ActivePrice">
                                    {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular :
                                    $product->price->regular, 2, '.', '', true) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown Menu content --}}
                    <div class="dropdown-menu w-100 p-0 shadow border-0 mt-0 rounded-bottom style-2-dropdown-menu"
                        aria-labelledby="style2LicenseDropdownBtn">
                        <div class="dropdown-menu-inner position-relative bg-white" style="top: -2px;">
                            {{-- Regular Option --}}
                            <div role="button" class="p-3 cursor-pointer s2-license-option active"
                                data-target="#s2-regular-pane"
                                data-name="{{ $regularTabLabel ?: translate('Regular License') }}"
                                data-price="{{ getAmount($product->isOnDiscount() ? $product->discount->price->regular : $product->price->regular, 2, '.', '', true) }}"
                                data-old-price="@if($product->isOnDiscount()){{ getAmount($product->price->regular, 2, '.', '', true) }}@endif">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fs-14">
                                        {{ $regularTabLabel ?: translate('Regular License') }}
                                        <span class="badge bg-primary fs-10 ms-2 s2-selected-badge">{{
                                            translate('Selected') }}</span>
                                    </h6>
                                    <span class="fs-5 fw-bold text-dark">
                                        {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular :
                                        $product->price->regular, 2, '.', '', true) }}
                                    </span>
                                </div>
                                <p class="text-gray-700 mb-0 fs-14 lh-base">{{ translate($regularPriceLabel) }}</p>
                            </div>

                            <div class="border-bottom"></div>

                            {{-- Extended Option --}}
                            <div role="button" class="p-3 cursor-pointer s2-license-option"
                                data-target="#s2-extended-pane"
                                data-name="{{ $extendedTabLabel ?: translate('Extended License') }}"
                                data-price="{{ getAmount($product->isOnDiscount() ? $product->discount->price->extended : $product->price->extended, 2, '.', '', true) }}"
                                data-old-price="@if($product->isOnDiscount()){{ getAmount($product->price->extended, 2, '.', '', true) }}@endif">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fs-14">
                                        {{ $extendedTabLabel ?: translate('Extended License') }}
                                        <span class="badge bg-primary fs-10 ms-2 s2-selected-badge d-none">{{
                                            translate('Selected') }}</span>
                                    </h6>
                                    <span class="fs-5 fw-bold text-dark">
                                        {{ getAmount($product->isOnDiscount() ? $product->discount->price->extended :
                                        $product->price->extended, 2, '.', '', true) }}
                                    </span>
                                </div>
                                <p class="text-gray-700 mb-0 fs-14 lh-base">{{ translate($extendedPriceLabel) }}</p>
                            </div>
                        </div>
                        @if ($showLicenseTermsLink)
                        <div class="border-top text-center py-2 mt-2">
                            <a href="/{{ $licenseTermsSlug }}" class="text-primary hover-underline fs-14"
                                target="_blank">
                                {{ translate('Read license terms') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                {{-- Minimal fallback if no extended price --}}
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark fs-16">{{ $regularTabLabel ?: translate('Regular License') }}</h6>
                    <div class="price-display d-flex align-items-start gap-2">
                        <span class="text-decoration-line-through fw-bold mt-1 text-muted fs-16">
                            @if ($product->isOnDiscount())
                            {{ getAmount($product->price->regular, 2, '.', '', true) }}
                            @endif
                        </span>
                        <span class="fw-bold fs-3 text-{{ $product->isOnDiscount() ? 'primary' : 'dark' }}">
                            {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular :
                            $product->price->regular, 2, '.', '', true) }}
                        </span>
                    </div>
                </div>
                @endif

                {{-- Content Panes --}}
                <div class="p-3 s2-panes-container">
                    {{-- REGULAR PANE --}}
                    <div id="s2-regular-pane" class="s2-pane">
                        <ul class="list-unstyled mb-3 small">
                            @if (count($productInfoItems) > 0)
                            @foreach ($productInfoItems as $info)
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>{{ $info }}
                            </li>
                            @endforeach
                            @else
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>{{ translate('Quality checked by
                                :site', ['site' => getSiteName()])}}
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>{{ translate('Future updates') }}
                            </li>
                            @endif

                            @if (@$settings->product->support_status && $product->isSupported())
                            @php
                            $freePackage = freeSupportPackage();
                            $paidPackage = $product->supportPackage;
                            $defaultSupportId = $freePackage?->id ?? '';
                            @endphp
                            @if ($freePackage)
                            <li class="mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-check2 text-muted fs-6"></i>
                                <span>
                                    {{ translate(':support from :seller', [
                                    'support' => $freePackage->title,
                                    'seller' => $product->seller->username
                                    ]) }}
                                    <a href="{{ route('page', @$settings->product->support_policy_slug ?? 'product-support-policy') }}"
                                        class="text-gray-700 fs-12 ms-1" target="_blank"
                                        title="{{ translate('View support policy') }}">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </a>
                                </span>
                            </li>
                            @endif
                            @endif
                        </ul>

                        @if (@$settings->product->support_status && $product->isSupported() && $paidPackage)
                        <div class="mb-3 d-flex flex-column gap-2">
                            @php
                            $finalPriceReg = $product->isOnDiscount() ? $product->discount->price->regular :
                            $product->price->regular;
                            $supportPriceRegValue = $paidPackage->calculatePrice($finalPriceReg);
                            $supportPriceReg = getAmount($supportPriceRegValue);
                            @endphp
                            <div class="form-check d-flex align-items-center gap-2 style2-support-checkbox-regular">
                                <input class="form-check-input border-secondary mt-0" type="checkbox"
                                    value="{{ $paidPackage->id }}" id="s2-reg-supp-paid">
                                <label class="form-check-label d-flex justify-content-between align-items-center w-100"
                                    for="s2-reg-supp-paid">
                                    <div class="text-secondary fs-14">{{ $paidPackage->title }}</div>
                                    <div class="d-flex align-items-center gap-1">
                                        @if ($product->isOnDiscount())
                                        <span class="text-decoration-line-through text-gray-700 fs-14 me-1">
                                            {{ getAmount($paidPackage->calculatePrice($product->price->regular)) }}
                                        </span>
                                        @endif
                                        <span class="text-dark fw-bold">{{ $supportPriceReg }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif

                        @if ($showExtraFeatures && count($regularExtraFeatures))
                        <div class="list-product">
                            <button class="btn btn-sm p-0 product-feature-btn fw-500">
                                <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                                <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                            </button>
                        </div>
                        <div
                            class="card-v product-features-box border rounded-3 bg-light-subtle p-0 mt-2 d-none mx-1 mb-3">
                            <div class="card-body px-3 py-2">
                                <ul class="product-extra-features mb-0 small">
                                    @foreach ($regularExtraFeatures as $feature)
                                    <li class="extra-features-list">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex flex-column gap-2 px-1 mt-4">
                            <form class="add-to-cart-form" method="POST" data-action="{{ route('cart.add-product') }}"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="1" class="license-type">
                                <input type="hidden" name="support" id="s2RegularAddCartSupport"
                                    value="{{ $defaultSupportId ?? '' }}">
                                <button
                                    class="btn {{ $addCartBtnStyle }} btn-modern w-100 py-2 fw-semibold shadow-sm rounded-1"
                                    @disabled(authUser()?->id == $product->seller_id)>
                                    @if($addCartBtnIcon)<i class="bi {{ $addCartBtnIcon }} fs-6 me-2"></i>@endif{{
                                    translate('Add to Cart') }}
                                </button>
                            </form>
                            @if ($showBuyNowButton)
                            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                                class="form-needs-login-modal buy-now-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="1">
                                <input type="hidden" name="support" class="buy-now-support-input"
                                    id="s2RegularBuyNowSupport" value="{{ $defaultSupportId ?? '' }}">
                                <button
                                    class="btn {{ $buyNowBtnStyle }} btn-modern w-100 py-2 fw-semibold shadow-sm rounded-1"
                                    name="regular_license" @disabled(authUser()?->id == $product->seller_id)>
                                    @if($buyNowBtnIcon)<i class="bi {{ $buyNowBtnIcon }} fs-6 me-2"></i>@endif{{
                                    translate('Buy Now') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- EXTENDED PANE --}}
                    @if ($hasExtendedPrice)
                    <div id="s2-extended-pane" class="s2-pane d-none">
                        <ul class="list-unstyled mb-3 small">
                            @if (count($productInfoItems) > 0)
                            @foreach ($productInfoItems as $info)
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>{{ $info }}
                            </li>
                            @endforeach
                            @else
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>
                                {{ translate('Quality checked by :site', ['site' => @$settings->general->site_name ??
                                'Ezymarket'])}}
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check2 text-muted fs-6 me-2"></i>{{ translate('Future updates') }}
                            </li>
                            @endif

                            @if (@$settings->product->support_status && $product->isSupported())
                            @if ($freePackage)
                            <li class="mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-check2 text-muted fs-6"></i>
                                <span>
                                    {{ translate(':support from :seller', ['support' => $freePackage->title, 'seller'
                                    => $product->seller->username]) }}
                                    <a href="{{ route('page', @$settings->product->support_policy_slug ?? 'product-support-policy') }}"
                                        class="text-gray-700 ms-1 fs-12" target="_blank"
                                        title="{{ translate('View support Policy') }}">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </a>
                                </span>
                            </li>
                            @endif
                            @endif
                        </ul>

                        @if (@$settings->product->support_status && $product->isSupported() && $paidPackage)
                        <div class="mb-3 d-flex flex-column gap-2">
                            @php
                            $finalPriceExt = ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                            ? $product->discount->price->extended
                            : $product->price->extended;
                            $supportPriceExtValue = $paidPackage->calculatePrice($finalPriceExt);
                            $supportPriceExt = getAmount($supportPriceExtValue);
                            @endphp
                            <div class="form-check d-flex align-items-center gap-2 style2-support-checkbox-extended">
                                <input class="form-check-input mt-0 border-secondary" type="checkbox"
                                    name="support_package" value="{{ $paidPackage->id }}"
                                    id="s2-ext-supp-paid">
                                <label class="form-check-label d-flex justify-content-between align-items-center w-100"
                                    for="s2-ext-supp-paid">
                                    <div class="fs-14">{{ $paidPackage->title }}</div>
                                    <div class="d-flex align-items-center gap-1">
                                        @if ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                                        <span class="text-decoration-line-through text-gray-700 fs-12 me-1">
                                            {{ getAmount($paidPackage->calculatePrice($product->price->extended)) }}
                                        </span>
                                        @endif
                                        <span class="text-dark fw-bold">{{ $supportPriceExt }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif

                        @if ($showExtraFeatures && count($extendedExtraFeatures))
                        <div class="list-product">
                            <button class="btn btn-sm p-0 product-feature-btn fw-500">
                                <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                                <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                            </button>
                        </div>
                        <div
                            class="card-v product-features-box border rounded-3 bg-light-subtle p-0 mt-2 d-none mx-1 mb-3">
                            <div class="card-body px-3 py-2">
                                <ul class="product-extra-features mb-0 small">
                                    @foreach ($extendedExtraFeatures as $feature)
                                    <li class="extra-features-list">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex flex-column gap-2 px-1 mt-4">
                            <form class="add-to-cart-form" method="POST" data-action="{{ route('cart.add-product') }}"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="2" class="license-type">
                                <input type="hidden" name="support" id="s2ExtendedAddCartSupport"
                                    value="{{ $defaultSupportId ?? '' }}">
                                <button
                                    class="btn {{ $addCartBtnStyle }} btn-modern w-100 py-2 fw-semibold shadow-sm rounded-1"
                                    @disabled(authUser()?->id == $product->seller_id)>
                                    @if($addCartBtnIcon)<i class="bi {{ $addCartBtnIcon }} fs-6 me-2"></i>@endif{{
                                    translate('Add to Cart') }}
                                </button>
                            </form>
                            @if ($showBuyNowButton)
                            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                                class="form-needs-login-modal buy-now-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="2">
                                <input type="hidden" name="support" class="buy-now-support-input"
                                    id="s2ExtendedBuyNowSupport" value="{{ $defaultSupportId ?? '' }}">
                                <button
                                    class="btn {{ $buyNowBtnStyle }} btn-modern w-100 py-2 fw-semibold shadow-sm rounded-1"
                                    name="extended_license" @disabled(authUser()?->id == $product->seller_id)>
                                    @if($buyNowBtnIcon)<i class="bi {{ $buyNowBtnIcon }} fs-6 me-2"></i>@endif{{
                                    translate('Buy Now') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @elseif ($style === 'style-3')
            {{-- Style 3: Modern Tabs --}}
            <div class="style-3-price-card p-3">

                {{-- License Tabs (Toggle) --}}
                @if ($hasExtendedPrice)
                <div class="d-flex p-1 bg-light border rounded-pill mb-4">
                    <button
                        class="btn btn-sm w-50 rounded-pill py-2 fw-semibold bg-white shadow-sm border transition-all s3-tab-btn active"
                        data-target="#s3-regular-pane">
                        {{ $regularTabLabel ?: translate('Regular License') }}
                    </button>
                    <button class="btn btn-sm w-50 rounded-pill py-2 transition-all s3-tab-btn"
                        data-target="#s3-extended-pane">
                        {{ $extendedTabLabel ?: translate('Extended License') }}
                    </button>
                </div>
                @else
                <div class="text-center mb-4">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm">
                        {{ $regularTabLabel ?: translate('Regular License') }}
                    </span>
                </div>
                @endif

                <div class="s3-panes-container">
                    {{-- REGULAR PANE --}}
                    <div id="s3-regular-pane" class="s3-pane text-center">
                        {{-- Price Block --}}
                        <div class="mb-3">
                            @if ($product->isOnDiscount())
                            <p class="text-decoration-line-through text-gray-700 mb-1 fs-5">
                                {{ getAmount($product->price->regular, 2, '.', '', true) }}
                            </p>
                            @endif
                            <h2
                                class="display-6 fw-bold text-{{ $product->isOnDiscount() ? 'primary' : 'dark' }} mb-0 lh-1">
                                {{ getAmount($product->isOnDiscount() ? $product->discount->price->regular :
                                $product->price->regular, 2, '.', '', true) }}
                            </h2>
                            <p class="text-muted small mt-2 mb-0">{{ translate($regularPriceLabel) }}</p>
                        </div>

                        {{-- Extra Features / Info --}}
                        <div class="text-start mb-4 px-2">
                            <ul class="list-unstyled mb-0 small text-gray-200">
                                @if (count($productInfoItems) > 0)
                                @foreach ($productInfoItems as $info)
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ $info }}
                                </li>
                                @endforeach
                                @else
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate('Quality checked by
                                    :site', [
                                    'site' => @$settings->general->site_name ?? 'Ezymarket'
                                    ])}}
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate('Future updates') }}
                                </li>
                                @endif

                                @if (@$settings->product->support_status && $product->isSupported())
                                @php
                                $freePackage = freeSupportPackage();
                                $paidPackage = $product->supportPackage;
                                $defaultSupportId = $freePackage?->id ?? '';
                                @endphp
                                @if ($freePackage)
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate(':support from :seller',
                                    ['support' => $freePackage->title, 'seller' => $product->seller->username]) }}
                                </li>
                                @endif
                                @endif
                            </ul>

                            {{-- Paid Support --}}
                            @if (@$settings->product->support_status && $product->isSupported() && $paidPackage)
                            <div class="mt-3 d-flex flex-column gap-2 bg-light p-3 rounded-3 border">
                                @php
                                $finalPriceReg = $product->isOnDiscount()
                                ? $product->discount->price->regular
                                : $product->price->regular;
                                $supportPriceRegValue = $paidPackage->calculatePrice($finalPriceReg);
                                $supportPriceReg = getAmount($supportPriceRegValue);
                                @endphp
                                <div
                                    class="form-check d-flex align-items-start gap-2 m-0 style3-support-checkbox-regular">
                                    <input class="form-check-input border-secondary" type="checkbox"
                                        value="{{ $paidPackage->id }}" id="s3-reg-supp-paid">
                                    <label class="form-check-label w-100" for="s3-reg-supp-paid">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="text-dark fw-medium small">{{ $paidPackage->title }}</span>
                                            <span class="text-small d-flex align-items-center gap-1">
                                                @if ($product->isOnDiscount())
                                                <span class="text-decoration-line-through text-gray-700 text-small">
                                                    {{
                                                    getAmount($paidPackage->calculatePrice($product->price->regular))
                                                    }}
                                                </span>
                                                @endif
                                                <span class="fw-bold text-dark">{{ $supportPriceReg }}</span>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if ($showExtraFeatures && count($regularExtraFeatures))
                            <div class="list-product mt-3 border-top pt-3">
                                <span role="button"
                                    class="product-feature-btn cursor-pointer fw-medium text-dark small d-inline-flex align-items-center">
                                    <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                                    <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                                </span>
                            </div>
                            <div class="card-v product-features-box d-none border rounded-3 bg-light-subtle p-0 mt-2">
                                <div class="card-body p-2">
                                    <ul class="product-extra-features mb-0 small ps-3">
                                        @foreach ($regularExtraFeatures as $feature)
                                        <li class="extra-features-list mb-1 text-gray-700">{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column gap-2">
                            <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="1" class="license-type">
                                <input type="hidden" name="support" id="s3RegularAddCartSupport"
                                    value="{{ $defaultSupportId ?? '' }}">
                                <button class="btn {{ $addCartBtnStyle }} w-100 py-2 fw-semibold rounded-pill shadow-sm"
                                    @disabled(authUser() && authUser()->id == $product->seller_id)>
                                    @if($addCartBtnIcon)<i class="bi {{ $addCartBtnIcon }} me-1"></i>@endif {{
                                    translate('Add to Cart') }}
                                </button>
                            </form>
                            @if ($showBuyNowButton)
                            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                                class="form-needs-login-modal buy-now-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="1">
                                <input type="hidden" name="support" class="buy-now-support-input"
                                    id="s3RegularBuyNowSupport" value="{{ $defaultSupportId ?? '' }}">
                                <button class="btn {{ $buyNowBtnStyle }} w-100 py-2 fw-semibold rounded-pill"
                                    name="regular_license" @disabled(authUser() && authUser()->id ==
                                    $product->seller_id)>
                                    @if($buyNowBtnIcon)<i class="bi {{ $buyNowBtnIcon }} me-1"></i>@endif {{
                                    translate('Buy Now') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- EXTENDED PANE --}}
                    @if ($hasExtendedPrice)
                    <div id="s3-extended-pane" class="s3-pane text-center d-none">
                        {{-- Price Block --}}
                        <div class="mb-3">
                            @if ($product->isOnDiscount())
                            <div class="text-decoration-line-through text-gray-700 mb-1 fs-5">
                                {{ getAmount($product->price->extended, 2, '.', '', true) }}
                            </div>
                            @endif
                            <h2
                                class="display-6 fw-bold text-{{ $product->isOnDiscount() ? 'primary' : 'dark' }} mb-0 lh-1">
                                {{ getAmount($product->isOnDiscount() ? $product->discount->price->extended :
                                $product->price->extended, 2, '.', '', true) }}
                            </h2>
                            <p class="text-muted small mt-2 mb-0">{{ translate($extendedPriceLabel) }}</p>
                        </div>

                        {{-- Extra Features / Info --}}
                        <div class="text-start mb-4 px-2">
                            <ul class="list-unstyled mb-0 small text-gray-200">
                                @if (count($productInfoItems) > 0)
                                @foreach ($productInfoItems as $info)
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ $info }}
                                </li>
                                @endforeach
                                @else
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate('Quality checked by
                                    :site',
                                    ['site' => @$settings->general->site_name ?? 'Ezymarket'])}}
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate('Future updates') }}
                                </li>
                                @endif

                                @if (@$settings->product->support_status && $product->isSupported())
                                @if ($freePackage)
                                <li class="mb-1">
                                    <i class="bi bi-check2 text-primary me-1"></i>{{ translate(':support from :seller',
                                    ['support' => $freePackage->title, 'seller' => $product->seller->username]) }}
                                </li>
                                @endif
                                @endif
                            </ul>

                            {{-- Paid Support --}}
                            @if (@$settings->product->support_status && $product->isSupported() && $paidPackage)
                            <div class="mt-3 d-flex flex-column gap-2 bg-light p-3 rounded-3 border">
                                @php
                                $finalPriceExt = ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                                ? $product->discount->price->extended
                                : $product->price->extended;
                                $supportPriceExtValue = $paidPackage->calculatePrice($finalPriceExt);
                                $supportPriceExt = getAmount($supportPriceExtValue);
                                @endphp
                                <div
                                    class="form-check d-flex align-items-start gap-2 m-0 style3-support-checkbox-extended">
                                    <input class="form-check-input border-secondary" type="checkbox"
                                        value="{{ $paidPackage->id }}" id="s3-ext-supp-paid">
                                    <label class="form-check-label w-100" for="s3-ext-supp-paid">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="text-dark fw-medium small">{{ $paidPackage->title }}</span>
                                            <span class="text-small d-flex align-items-center gap-1">
                                                @if ($product->isOnDiscount() && $product->isExtendedOnDiscount())
                                                <span class="text-decoration-line-through text-gray-700 text-small">
                                                    {{
                                                    getAmount($paidPackage->calculatePrice($product->price->extended))
                                                    }}
                                                </span>
                                                @endif
                                                <span class="fw-bold text-dark">{{ $supportPriceExt }}</span>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if ($showExtraFeatures && count($extendedExtraFeatures))
                            <div class="list-product mt-3 border-top pt-3">
                                <span role="button"
                                    class="product-feature-btn cursor-pointer fw-medium text-dark small d-inline-flex align-items-center">
                                    <i class="bi bi-database-add me-1"></i>{{ translate("What's more included") }}
                                    <i class="bi bi-chevron-down feature-chevron ms-1 small"></i>
                                </span>
                            </div>
                            <div class="card-v product-features-box d-none border rounded-3 bg-light-subtle p-0 mt-2">
                                <div class="card-body p-2">
                                    <ul class="product-extra-features mb-0 small ps-3">
                                        @foreach ($extendedExtraFeatures as $feature)
                                        <li class="extra-features-list mb-1 text-gray-700">{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column gap-2">
                            <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="2" class="license-type">
                                <input type="hidden" name="support" id="s3ExtendedAddCartSupport"
                                    value="{{ $defaultSupportId ?? '' }}">
                                <button class="btn {{ $addCartBtnStyle }} w-100 py-2 fw-semibold rounded-pill shadow-sm"
                                    @disabled(authUser()?->id == $product->seller_id)>
                                    @if($addCartBtnIcon)<i class="bi {{ $addCartBtnIcon }} me-1"></i>@endif {{
                                    translate('Add to Cart') }}
                                </button>
                            </form>
                            @if ($showBuyNowButton)
                            <form action="{{ route('products.buy-now', [$product->slug, $product->id]) }}"
                                class="form-needs-login-modal buy-now-form" method="POST"
                                data-default-support="{{ $defaultSupportId ?? '' }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="2">
                                <input type="hidden" name="support" class="buy-now-support-input"
                                    id="s3ExtendedBuyNowSupport" value="{{ $defaultSupportId ?? '' }}">
                                <button class="btn {{ $buyNowBtnStyle }} w-100 py-2 fw-semibold rounded-pill"
                                    name="extended_license" @disabled(authUser()?->id == $product->seller_id)>
                                    @if($buyNowBtnIcon)<i class="bi {{ $buyNowBtnIcon }} me-1"></i>@endif {{
                                    translate('Buy Now') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if ($activePurchase)
        </div> {{-- End of buy-license-pane --}}
    </div> {{-- End of purchasedTabContent --}}
    @endif

    @if ($style !== 'style-2' && ($showSupportPolicy || $showLicenseTermsLink))
    <div class="mb-2 text-center">
        @if ($showSupportPolicy)
        <a href="/{{ $supportPolicySlug }}" class="text-muted text-xsmall hover-primary-underline" target="_blank">
            {{ translate('Support policy') }}
        </a>
        @endif
        @if ($showLicenseTermsLink)
        <span class="text-muted text-xsmall mx-1">|</span>
        <a href="/{{ $licenseTermsSlug }}" class="text-muted text-xsmall hover-primary-underline" target="_blank">
            {{ translate('License terms') }}
        </a>
        @endif
    </div>
    @endif
</div>
@endif
