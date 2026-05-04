@php
$config = (object)($config ?? []);
$showSellerAvatar = property_exists($config, 'seller_avatar') ? !empty($config->seller_avatar) : true;
$showSellerName = property_exists($config, 'seller_name') ? !empty($config->seller_name) : true;
$showCategory = property_exists($config, 'show_category') ? !empty($config->show_category) : true;
$showTotalReviews = property_exists($config, 'total_reviews') ? !empty($config->total_reviews) : true;
$showTotalSales = property_exists($config, 'total_sales') ? !empty($config->total_sales) : true;
$showTotalDownloads = property_exists($config, 'total_downloads') ? !empty($config->total_downloads) : true;
$showCartBtn = property_exists($config, 'cart_btn') ? !empty($config->cart_btn) : true;
$showFavoriteBtn = property_exists($config, 'favorite_btn') ? !empty($config->favorite_btn) : true;
$showDownloadBtn = property_exists($config, 'download_btn') ? !empty($config->download_btn) : true;
$showPostDate = property_exists($config, 'post_date') ? !empty($config->post_date) : true;
$showProductBadge = property_exists($config, 'product_badge') ? !empty($config->product_badge) : true;
$actionButtonStyle = $actionBtnStyle ?? (property_exists($config, 'action_button_style') ? $config->action_button_style : 'default');
$cartButtonStyle = $cartBtnStyle ?? (property_exists($config, 'cart_button_style') ? $config->cart_button_style : 'outline-primary');
$previewButtonStyle = $previewBtnStyle ?? (property_exists($config, 'preview_button_style') ? $config->preview_button_style : 'primary');
$showLivePreviewBtn = property_exists($config, 'live_preview_btn') ? !empty($config->live_preview_btn) : true;
$productMetaStyle = $metaStyle ?? (property_exists($config, 'product_meta_style') ? $config->product_meta_style : 'default');
$hasMetaSection = $productMetaStyle === 'none' ? true : false;
$alignment = property_exists($config, 'blockAlignment') ? $config->blockAlignment : 'start';
$titleLength = property_exists($config, 'products_title_length') ? $config->products_title_length : 45;
@endphp
<div class="product {{ $product->classes ?? '' }} {{ $custom_class ?? '' }}">

    {{-- product Header Section --}}
    <div class="product-header">

        {{-- Preview Media Section --}}
        @if ($product->isImagePreview())
        {{-- Image Preview --}}
        <a class="product-img-holder" href="{{ $product->view_link }}">
            <img class="product-img" src="{{ $product->preview_image_url }}" alt="{{ $product->name }}" />
        </a>

        @elseif($product->isVideoPreview())
        {{-- Video Preview --}}
        <a href="{{ $product->view_link }}" class="opacity-100">
            <div class="product-video">
                <video class="plyr" poster="{{ $product->preview_image_url }}" muted>
                    <source src="{{ $product->preview_video_url }}">
                </video>

                {{-- Play Overlay --}}
                <div class="product-video-play-overlay">
                    <i class="bi bi-play-circle-fill"></i>
                </div>

                {{-- Video Controls --}}
                <div class="product-video-actions d-flex align-items-center justify-content-between gap-1">
                    <div class="product-video-volume product-video-action">
                        <i class="bi bi-volume-up" class="unmuted"></i>
                        <i class="bi bi-volume-mute" class="muted"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <div class="product-video-full product-video-action">
                            <i class="bi bi-arrows-angle-expand"></i>
                        </div>
                    </div>
                </div>

                {{-- Video Progress Bar --}}
                <div class="product-video-progress">
                    <span></span>
                </div>
            </div>
        </a>

        @elseif($product->isAudioPreview())
        {{-- Audio Preview --}}
        <div class="product-audio">
            <a href="{{ $product->view_link }}" class="product-audio-link opacity-100"></a>

            <div class="product-audio-wave">
                {{-- Audio Controls --}}
                <div class="product-audio-actions position-relative">
                    <button class="play-button btn btn-primary rounded-circle px-2">
                        <div class="play-button-icon">
                            <i class="bi bi-play-fill"></i>
                        </div>
                    </button>
                    <button class="pause-button btn btn-primary rounded-circle px-2 d-none">
                        <div class="play-button-icon">
                            <i class="bi bi-pause-fill"></i>
                        </div>
                    </button>
                </div>

                {{-- Waveform Display --}}
                <div class="waveform" data-url="{{ $product->preview_audio_url }}" data-waveheight="50"></div>
            </div>
        </div>
        @endif

        @if ($showProductBadge)
        @if (isPremiumAvailable() && $product->isPremium())
        <div class="product-badge product-badge-premium">
            <i class="bi bi-gem me-1"></i>{{ translate('Premium') }}
        </div>
        @endif
        @if ($product->isFree())
        <div class="product-badge product-badge-free">
            <i class="bi bi-gift me-1"></i>
            {{ translate('Free') }}
        </div>
        @elseif ($product->isOnDiscount())
        <div class="product-badge product-badge-sale text-lowercase">
            <i class="bi bi-tags me-1"></i>
            {{ $product->discount->regular_percentage }}% {{ translate('off') }}
        </div>
        @elseif ($product->isTrending())
        <div class="product-badge product-badge-trending">
            <i class="bi bi-lightning me-1"></i>
            {{ translate('Trending') }}
        </div>
        @endif
        @endif

        {{-- Favorite Button --}}
        @if ($showFavoriteBtn)
        <div class="product-favorite-btn">
            <livewire:favorite :product="$product" />
        </div>
        @endif
    </div>

    {{-- Product Body Section --}}
    <div class="product-body">

        {{-- Seller Information --}}
        @php
        $seller = $product->seller;
        $verifiedBadge = $seller->hasVerifiedBadge();
        @endphp

        <div class="d-flex flex-column align-items-{{ $alignment }} text-{{ $alignment }} mb-2">
            {{-- product Title --}}
            <div class="product-title-section mb-1 flex-grow-1">
                <a class="product-title" href="{{ $product->view_link }}">
                    {{ truncateText($product->name, $titleLength) }}
                </a>
            </div>

            <div
                class="product-meta-section d-flex align-items-center gap-2 mb-1 {{ $hasMetaSection ? 'd-none' : '' }}">
                @if ($productMetaStyle === 'minimal')
                <div class="product-meta flex-grow-1 text-muted text-xsmall text-truncate">
                    @if ($showSellerName)
                    {{ translate('by') }} <a class="text-reset fw-medium hover-primary-underline" href="{{ $seller->profile_link }}">{{
                        $seller->username }}</a>
                    @endif
                    @if ($showCategory)
                    <span class="ms-1">
                        {{ translate('in') }} <a class="text-reset hover-primary-underline" href="{{ $product->category->view_link }}">{{
                            $product->category->name }}</a></span>
                    @endif
                </div>
                @else
                {{-- Default Style --}}
                {{-- SellerAvatar --}}
                @if ($showSellerAvatar)
                <div class="d-flex align-items-center justify-content-center" style="width:30px;height:30px;">
                    <img src="{{ $seller->avatar_url }}" alt="{{ $seller->username }}"
                        class="rounded-circle w-100 h-100 object-fit-cover">
                </div>
                @endif

                <div class="product-meta flex-grow-1">
                    @if ($showSellerName)
                    <a class="text-dark text-small d-block lh-sm text-start hover-primary-underline"
                        href="{{ $seller->profile_link }}">
                        {{ $seller->full_name }}
                        @if (isset($verifiedBadge) && $verifiedBadge)
                        <span class="verified-badge" data-bs-toggle="tooltip" data-bs-title="Verified seller">
                            <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}" width="10"
                                height="10">
                        </span>
                        @endif
                    </a>
                    @endif
                    @if ($showCategory || $showPostDate)
                    <div class="d-flex align-items-center">
                        @if ($showCategory)
                        <a class="text-muted text-xsmall hover-primary-underline"
                            href="{{ $product->category->view_link }}" title="Category">
                            In {{ $product->category->name }}
                        </a>
                        @endif
                        @if ($showCategory && $showPostDate)
                        <span class="dot-seperator"></span>
                        @endif
                        @if ($showPostDate)
                        <span class="text-muted text-xsmall" title="Post Date">
                            {{ $product->created_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Purchase Section --}}
        <div class="product-purchase">
            <div class="row row-cols-auto align-items-center justify-content-between g-2">

                {{-- Price & Stats Column --}}
                <div class="col">
                    {{-- Price Display --}}
                    @if ($product->isFree())
                    <div class="product-price">
                        <span class="product-price-number">{{ translate('Free') }}</span>
                    </div>
                    @else
                    <div class="product-price">
                        @if ($product->isOnDiscount())
                        <span class="product-price-through">
                            {{ getAmount($product->price->regular, 2, '.', '', true) }}
                        </span>
                        <span class="product-price-number">
                            {{ getAmount($product->discount->price->regular, 2, '.', '', true) }}
                        </span>
                        @else
                        <span class="product-price-number">
                            {{ getAmount($product->price->regular, 2, '.', '', true) }}
                        </span>
                        @endif
                    </div>
                    @endif

                    {{-- Product Statistics --}}
                    <div class="product-info d-flex align-items-center justify-content-between">
                        {{-- Reviews Section --}}
                        @if (@$settings->product->reviews_status && $product->hasReviews() && $showTotalReviews)
                        <div class="product-rating d-flex align-items-center me-1">
                            @themeInclude('partials.rating-stars', [
                            'ratings_classes' => 'ratings-sm',
                            'args' => $product
                            ])
                            <div class="text-xsmall text-muted ms-1">
                                ({{ numberFormat($product->total_reviews) }})
                            </div>
                        </div>
                        <span class="dot-seperator"></span>
                        @endif

                        {{-- Sales/Downloads Section --}}
                        @if ($product->isPurchasingEnabled() && $product->hasSales() && $showTotalSales)
                        <div class="product-sales">
                            <i class="bi bi-bag-check text-success"></i>
                            {{ translate($product->total_sales > 1 ? ':count Sales' : ':count Sale', [
                            'count' => numberFormat($product->total_sales)
                            ]) }}
                        </div>
                        @elseif(@$settings->product->free_product_total_downloads && $product->free_downloads > 1)
                        <div class="product-sales">
                            <i class="bi bi-cloud-arrow-down text-success"></i>
                            {{ translate($product->free_downloads > 1 ? ':count Downloads' : ':count Download', [
                            'count' => numberFormat($product->free_downloads)
                            ]) }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons Column --}}
                @if ($showDownloadBtn || $showCartBtn || $showLivePreviewBtn)
                @php
                    $isDefault = $actionButtonStyle === 'default';
                    $isStyle1 = $actionButtonStyle === 'style_1';
                    $isStyle2 = $actionButtonStyle === 'style_2';
                    $actionButtonClass = $isDefault ? 'btn-padding' : 'px-2';
                    $iconClass = $isDefault ? 'fs-4' : '';
                @endphp
                <div class="col">
                    <div class="row row-cols-auto g-2">
                        {{-- Live Preview Button --}}
                        @if ($showLivePreviewBtn && !empty($product->demo_link))
                        <div class="col">
                            <a href="{{ $product->view_demo }}" target="_blank"
                                class="btn btn-sm btn-{{ $previewButtonStyle }} btn-modern {{ $actionButtonClass }}"
                                @if(!$isStyle1) title="{{ translate('Live Preview') }}" @endif>
                                @if($isStyle1)
                                    {{ translate('Live Preview') }}
                                @else
                                    <i class="bi bi-eye-fill {{ $iconClass }}"></i>
                                @endif
                            </a>
                        </div>
                        @endif

                        {{-- Download/Cart Button --}}
                        @if ($product->isFree())
                        @if ($showDownloadBtn)
                        <div class="col">
                            @if ($product->isMainFileExternal())
                            {{-- External Download --}}
                            <a href="{{ route('products.free.download.external', hash_encode($product->id)) }}"
                                target="_blank" class="btn btn-sm  btn-{{ $cartButtonStyle }} btn-modern {{ $actionButtonClass }}"
                                @if(!$isStyle2) title="{{ translate('Download') }}" @endif>
                                @if($isStyle2)
                                    {{ translate('Download') }}
                                @else
                                    <i class="bi bi-cloud-arrow-down-fill {{ $iconClass }}"></i>
                                @endif
                            </a>
                            @else
                            {{-- Internal Download --}}
                            <form action="{{ route('products.free.download', hash_encode($product->id)) }}"
                                method="POST">
                                @csrf
                                <button class="btn btn-sm btn-{{ $cartButtonStyle }} btn-modern {{ $actionButtonClass }}"
                                    @if(!$isStyle2) title="{{ translate('Download') }}" @endif>
                                    @if($isStyle2)
                                        {{ translate('Download') }}
                                    @else
                                        <i class="bi bi-cloud-download-fill {{ $iconClass }}"></i>
                                    @endif
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                        @else
                        {{-- Add to Cart Button --}}
                        @if ($showCartBtn)
                        <div class="col">
                            <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="license_type" value="1">

                                @if (@$settings->product->support_status && $product->isSupported() &&
                                defaultSupportPackage())
                                <input type="hidden" name="support" value="{{ defaultSupportPackage()->id }}">
                                @endif

                                <button class="btn btn-sm btn-{{ $cartButtonStyle }} btn-modern {{ $actionButtonClass }}"
                                    @if(!$isStyle2) title="{{ translate('Add to cart') }}"
                                    @endif @disabled(authUser()?->id == $product->seller_id)>
                                    @if($isStyle2)
                                        {{ translate('Add to cart') }}
                                    @else
                                        <i class="bi bi-cart-plus-fill {{ $iconClass }}"></i>
                                    @endif
                                </button>
                            </form>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
