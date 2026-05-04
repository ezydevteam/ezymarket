{{-- Product Meta Card Widget --}}
@php
    $showReviews = ($widgetSettings['show_reviews'] ?? true)
        && @$settings->product->reviews_status
        && $product->hasReviews();
    $showSales = ($widgetSettings['show_sales'] ?? true)
        && $product->isPurchasingEnabled()
        && $product->hasSales();
    $showDownloads = ($widgetSettings['show_downloads'] ?? true)
        && @$settings->product->free_product_total_downloads
        && $product->isFree() && $product->free_downloads > 0;
    $showFavorite = $widgetSettings['show_favorite'] ?? true;
    $showShare = $widgetSettings['show_share'] ?? true;
    $showReport = $widgetSettings['show_report'] ?? true;
@endphp

@if ($showReviews || $showSales || $showDownloads || $showFavorite || $showShare || $showReport)
<div class="d-flex align-items-center flex-wrap gap-2 justify-content-between px-3 py-2 bg-light rounded-pill">
    {{-- Reviews --}}
    @if ($showReviews)
        <div class="product-widget-reviews">
            <div class="btn btn-soft btn-sm btn-padding product-rating" title="{{ translate('Total reviews') }}">
                @themeInclude('partials.rating-stars', ['args' => $product, 'counter_only' => true])
            </div>
        </div>
    @endif

    {{-- Sales --}}
    @if ($showSales)
        <div class="product-widget-sales">
            <div class="btn btn-soft btn-sm btn-padding product-sale" title="{{ translate('Total sales') }}">
                <i class="bi bi-bag-check me-1"></i>
                <span>
                    {{ translate($product->total_sales > 1 ? ':count Sales' : ':count Sale', [
                        'count' => number_format($product->total_sales)]) }}
                </span>
            </div>
        </div>
    @endif

    {{-- Downloads (Free Products) --}}
    @if ($showDownloads)
        <div class="product-widget-free-download">
            <span class="btn btn-soft btn-sm btn-padding" title="{{ translate('Total downloads') }}">
                <i class="bi bi-download me-1"></i>
                {{ translate($product->free_downloads > 1 ? ':count Downloads' : ':count Download', [
                    'count' => numberFormat($product->free_downloads)]) }}
            </span>
        </div>
    @endif

    {{-- Favorite --}}
    @if ($showFavorite)
        <div class="product-widget-favorite">
            <livewire:favorite
                :product="$product"
                :key="'favorite-widget-'.$product->id"
                btnClass="btn-soft"
            />
        </div>
    @endif

    {{-- Share --}}
    @if ($showShare)
        <div class="product-widget-share">
            <button id="productWidgetShareBtn" class="btn btn-soft btn-sm btn-padding"
                type="button" data-bs-toggle="modal" data-bs-target="#productShareModal" title="{{ translate('Share') }}">
                <i class="bi bi-share me-1"></i>
            </button>
        </div>
    @endif

    {{-- Report --}}
    @if ($showReport)
        <div class="product-widget-more drop-down" data-dropdown data-dropdown-position="top">
            <button class="btn drop-down-btn btn-soft btn-sm btn-padding" type="button" title="{{ translate('More') }}">
                <i class="bi bi-three-dots"></i>
            </button>
            <div class="drop-down-menu drop-down-menu-sm drop-down-menu-start p-2">
                <li class="drop-down-item">
                    <button class="btn btn-sm bg-transparent p-0" type="button" data-bs-toggle="modal"
                        data-bs-target="#reportProductModal" data-item-name="{{ $product->name }}"
                        data-report-url="{{ route('products.report.store', ['slug' => $product->slug, 'product' => $product->id]) }}">
                        <i class="bi bi-flag me-1"></i>
                        {{ translate('Report an issue') }}
                    </button>
                </li>
            </div>
        </div>
    @endif
</div>
@endif
