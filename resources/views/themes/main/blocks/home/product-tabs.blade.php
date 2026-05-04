@php $data = (object)($data ?? []); @endphp

@if(count($data->productTabsActiveTabs ?? []) > 0)
<div id="{{ $data->uniqueId ?? 'productTabsBlock' }}"
    class="products-block {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])

    <div class="section-body">
        <div class="tab-content" id="prod-tabContent-{{ $data->productTabsId }}" data-aos="fade-up"
            data-aos-duration="1000">
            @foreach($data->productTabsActiveTabs as $key => $label)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                id="prod-{{ $key }}-{{ $data->productTabsId }}" role="tabpanel"
                aria-labelledby="prod-{{ $key }}-{{ $data->productTabsId }}-tab">

                @if(($data->productTabsProducts[$key] ?? collect())->count() > 0)
                <div class="row {{ $data->gridClass }} g-4">
                    @foreach($data->productTabsProducts[$key] as $product)
                    <div class="col">
                        @themeInclude('products.partials.grid-product', ['product' => $product, 'config' => $data])
                    </div>
                    @endforeach
                </div>

                @themeInclude('blocks.home.partials.pagination', [
                'products' => $data->productTabsProducts[$key],
                'data' => $data
                ])
                @else
                @themeInclude('partials.no-products')
                @endif

            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
