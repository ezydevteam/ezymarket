<div class="store-content">
    <div class="d-flex align-items-center justify-content-between g-3 mb-3 pb-2 border-bottom-dashed">
        <h4 class="fw-bold text-gray-700 mb-0 h5">{{ translate('All Products') }}</h4>
        <div class="profile-search-inline">
            <form action="{{ $user->store_link }}" method="GET" class="position-relative">
                <div class="form-search bg-light border rounded-pill py-1">
                    <input type="text" name="search" placeholder="{{ translate('Search here...') }}"
                        class="form-control border-0 bg-transparent py-1 fs-14" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-link p-0 text-muted">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($products->count() > 0)
        <div class="products-grid">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" id="store-product-list">
                @foreach ($products as $product)
                    <div class="col">
                        @themeInclude('products.partials.grid-product', [
                            'product' => $product,
                            'custom_class' => 'shadow-sm h-100',
                            'metaStyle' => 'minimal',
                            'actionBtnStyle' => 'style_1',
                            'cartBtnStyle' => 'outline-dark',
                            'previewBtnStyle' => 'primary',
                        ])
                    </div>
                @endforeach
            </div>
        </div>

        @themeInclude('partials.load-more', [
            'items' => $products,
            'target' => '#store-product-list',
        ])
    @else
        <div class="text-center py-5 bg-light rounded-4">
            <div class="opacity-25 mb-3">
                <i class="bi bi-box-seam display-4"></i>
            </div>
            <h5 class="fw-bold">{{ translate('No products found') }}</h5>
            <p class="text-muted mb-0">{{ translate(':user hasn\'t published any products yet.', ['user'=> $user->full_name]) }}</p>
        </div>
    @endif
</div>
