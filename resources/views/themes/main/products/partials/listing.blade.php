@php
    $pageSettings = themePageSettings();
    $viewStyle = $pageSettings->product_view_style ?? 'grid';
    $paginationType = $pageSettings->pagination_type ?? 'numbered';
    $containerWidth = $pageSettings->container_width ?? 'default';
    $hasSidebar = ($pageSettings->sidebar_layout ?? 'no_sidebar') !== 'no_sidebar';
    $listingClass = ($viewStyle === 'list' || $viewStyle === 'list-compact') ? 'product-list-view' : 'product-grid-view';
    $gridClass = $hasSidebar ? 'row-cols-xxl-3' : 'row-cols-lg-3 row-cols-xxl-4';
    $compactClass= ($viewStyle === 'list-compact' && $containerWidth !== 'boxed') ? 'col-lg-8 mx-auto' : '';
@endphp

<div class="product-listing-wrapper {{ $listingClass }}">
    @if ($products->count() > 0)
        @if ($viewStyle === 'list' || $viewStyle === 'list-compact')
            <div class="{{ $compactClass }}">
                <div id="product-list-container"
                    class="product-list-stack d-flex flex-column gap-3">
                    @foreach ($products as $product)
                        @themeInclude('products.partials.list-product', ['product' => $product])
                    @endforeach
                </div>
            </div>
        @else
            <div id="product-grid-container"
                class="row row-cols-1 row-cols-md-2 justify-content-center g-4 {{ $gridClass }}">
                @foreach ($products as $product)
                    <div class="col">
                        @themeInclude('products.partials.grid-product', [
                            'product' => $product,
                            'custom_class' => 'border flex-fill',
                            'metaStyle' => 'minimal',
                            'actionBtnStyle' => 'style_1',
                            'cartBtnStyle' => 'outline-dark',
                            'previewBtnStyle' => 'outline-primary',
                        ])
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            @if ($paginationType === 'load_more')
                <div class="load-more-wrapper text-center {{ $products->hasMorePages() ? '' : 'd-none' }}">
                    <button class="load-more-btn btn btn-primary px-4 py-2"
                            data-url="{{ $products->nextPageUrl() }}"
                            data-target="#product-grid-container, #product-list-container">
                        <span class="load-more-icon me-1"><i class="bi bi-arrow-repeat"></i></span>
                        <span class="load-more-text">{{ translate('Load More') }}</span>
                        <span class="load-more-loader d-none">
                            <span class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                        </span>
                    </button>
                </div>
            @else
                {{ $products->links() }}
            @endif
        </div>
    @else
        @themeInclude('partials.no-products')
    @endif
</div>
