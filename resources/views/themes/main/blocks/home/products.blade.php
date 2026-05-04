@php
$data = (object)($data ?? []);
$products = $data->products ?? collect();
$categories = $data->categories ?? collect();
@endphp

<div id="{{ $data->uniqueId ?? 'productsBlock' }}"
    class="home-products-block {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])

    <div class="products-wrapper">
        <div class="tab-content" data-aos="fade-up" data-aos-duration="1000">

            <div class="tab-pane {{ $data->activeCategorySlug ? '' : 'show active' }}"
                id="{{ $data->uniqueId }}-pills-all" role="tabpanel"
                aria-labelledby="{{ $data->uniqueId }}-pills-all-tab">

                @if ($products->count() > 0)
                <div class="row {{ $data->gridClass }} g-4">
                    @foreach ($products as $product)
                    <div class="col">
                        @themeInclude('products.partials.grid-product', ['product' => $product, 'config' => $data])
                    </div>
                    @endforeach
                </div>

                @themeInclude('blocks.home.partials.pagination', [
                'products' => $products,
                'data' => $data
                ])
                @else
                @themeInclude('partials.no-products')
                @endif

            </div>

            @foreach ($categories as $category)
            <div class="tab-pane {{ $data->activeCategorySlug === $category->slug ? 'show active' : '' }}"
                id="{{ $data->uniqueId }}-pills-{{ $category->slug }}" role="tabpanel"
                aria-labelledby="{{ $data->uniqueId }}-pills-{{ $category->slug }}-tab">

                @if ($category->products->count() > 0)
                <div class="row {{ $data->gridClass }} g-4">
                    @foreach ($category->products as $product)
                    <div class="col">
                        @themeInclude('products.partials.grid-product', ['product' => $product, 'config' => $data])
                    </div>
                    @endforeach
                </div>

                @themeInclude('blocks.home.partials.pagination', [
                'products' => $category->products,
                'data' => (object) array_merge((array)$data, ['view_more_url' => $category->link])
                ])
                @else
                @themeInclude('partials.no-products')
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
