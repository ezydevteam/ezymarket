@php
$showMetaBtns = ($data->meta_favorite_btn || $data->meta_share_btn || $data->meta_report_btn);
$showMetaSection = ($data->meta_seller_name || $data->meta_total_sales || $data->meta_avg_reviews ||
$data->meta_free_downloads || $showMetaBtns);
@endphp
<div class="product-page-meta mb-3">
    @if($data->show_breadcrumbs)
    <div class="product-page-breadcrumb mb-2 {{ $data->breadcrumb_color }} {{ $data->breadcrumb_style_class ?? '' }}">
        @yield('breadcrumbs')
    </div>
    @endif
    <h1
        class="product-page-title {{ $data->title_weight }} {{ $data->title_size }} {{ $data->title_color }} {{ $data->title_transform }} mb-2">
        {{ $product->name }}
    </h1>

    <!-- Product Share, Favorite, Review, Sales -->
    @if ($showMetaSection)
    <div class="d-flex flex-wrap align-items-center gap-3">
        @if ($data->meta_seller_name)
        <div class="product-meta-seller">
            <p class="mb-0">
                <span class="text-gray-700 fw-normal small">{{ translate('By ') }}</span>
                <a class="text-reset fw-medium hover-primary"
                    href="{{ $product->seller->profile_link }}" target="_blank">
                    {{ $product->seller->username }}</a>
            </p>
        </div>
        @endif
        @if ($product->isPurchasingEnabled() && $data->meta_total_sales)
        <div class="product-meta-sales">
            <p class="sales-counter fw-medium mb-0" title="Product sales">
                <i class="bi bi-bag-check me-1"></i>{{ numberFormat($product->total_sales ?? 0) }}
                <span class="text-gray-700 fw-normal small text-lowercase">
                    {{ translate($product->total_sales > 1 ? 'sales' : 'sale') }}
                </span>
            </p>
        </div>
        @endif
        @if ($settings->product->reviews_status && $data->meta_avg_reviews)
        <div class="product-meta-reviews">
            <div class="rating-counter text-white" title="Product reviews">
                @themeInclude('partials.rating-stars', ['args' => $product, 'label_only' => 'rating'])
            </div>
        </div>
        @endif
        <!-- Free product Downloads Count -->
        @if (@$productSettings->free_product_total_downloads && $data->meta_free_downloads && $product->isFree())
        <div class="product-meta-free-download">
            <p class="free-download-counter mb-0" title="Total downloads">
                <i class="bi bi-download me-1"></i>
                {{ numberFormat($product->free_downloads) }}
                <span class="text-gray-700 fw-normal small text-lowercase">
                    {{ translate($product->free_downloads > 1 ? 'downloads' : 'download') }}
                </span>
            </p>
        </div>
        @endif
        @if (($data->display_layout ?? null) === 'minimalist' && $showMetaBtns)
        @if($data->meta_favorite_btn)
        <div class="product-meta-favorite">
            <livewire:favorite :product="$product" :key="'favorite-'.$product->id" />
        </div>
        @endif
        @if($data->meta_share_btn)
        <div class="product-meta-share">
            <button id="productMobileShareBtn" class="btn bg-transparent p-0" type="button" data-bs-toggle="modal"
                data-bs-target="#productShareModal" title="{{ translate('Share this product') }}">
                <i class="bi bi-reply me-1"></i>
            </button>
        </div>
        @endif
        @if($data->meta_report_btn)
        <div class="product-meta-report">
            <button class="btn drop-down-btn bg-transparent p-0" type="button" data-bs-toggle="modal"
                data-bs-target="#reportProductModal" data-item-name="{{ $product->name }}"
                data-report-url="{{ route('products.report.store', ['slug' => $product->slug, 'product' => $product->id]) }}"
                title="{{ translate('Report an issue') }}">
                <i class="bi bi-flag"></i>
            </button>
        </div>
        @endif
        @endif
        @if ($data->meta_recent_update && $product->is_recently_updated)
        <p class="mb-0 text-primary fw-medium">
            <i class="bi bi-check-circle-fill me-1"></i>{{ translate('Recently updated') }}
        </p>
        @endif
    </div>
    @endif
    @if ($data->display_layout === 'gallery_focus' && ($data->preview_gallery_display === 'default' ||
    $data->preview_gallery_display === 'both'))
    @themeInclude('products.partials.preview-gallery-buttons')
    @endif
</div>
