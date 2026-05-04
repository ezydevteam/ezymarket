@if ($products?->count() > 0)
@php
$slide = $data->container_width === 'boxed' ? 3 : 4;
@endphp
<section class="section section-start">
    <div class="{{ $data->container_class }}">
        <h5 class="custom-section-title fw-medium mb-3">
            {{ $title ?? translate('Products') }}
        </h5>
        <div class="section-body products-swiper" id="SellerproductsSwriper" data-slide="{{ $slide }}">
            <div class="swiper seller-products-swiper">
                <div class="swiper-wrapper">
                    @foreach ($products as $product)
                    <div class="swiper-slide h-100 d-flex">
                        @themeInclude('products.partials.grid-product', [
                        'product' => $product,
                        'custom_class' => 'border flex-fill',
                        'metaStyle' => 'minimal',
                        'actionBtnStyle' => 'style_1',
                        'cartBtnStyle' => 'outline-dark',
                        'previewBtnStyle' => 'dark-subtle',
                        ])
                    </div>
                    @endforeach
                    @if ($products->count() > 5)
                    <div class="swiper-slide h-100 d-flex">
                        <div class="product flex-fill bg-light border w-100">
                            <div class="product-body h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                <i class="bi bi-grid-fill text-muted mb-3 d-block fs-1"></i>
                                <a href="{{ $view_link ?? $seller->portfolio_link }}" class="btn btn-primary btn-modern rounded-pill shadow-sm">
                                    {{ translate('View More') }} <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="swiper-pagination mt-4"></div>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</section>
@endif
