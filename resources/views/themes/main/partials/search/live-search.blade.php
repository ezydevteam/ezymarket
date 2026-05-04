@if ($products->count() > 0)
    <ul class="list-group list-group-flush">
        @foreach ($products as $product)
            <li class="list-group-item">
                <a href="{{ $product->view_link }}" class="d-flex align-items-center text-decoration-none text-dark">
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="object-fit-cover me-2 rounded" width="50" height="40">
                    <span>
                        {{ $product->name }}
                    </span>
                        <span class="ms-auto badge bg-primary">
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
                        </span>
                </a>
            </li>
        @endforeach
        <li class="list-group-item text-center">
            <a href="{{ route('products.search', ['search' => request('query')]) }}" class="btn btn-sm btn-outline-primary w-100">
                {{ translate('View All') }}
            </a>
        </li>
    </ul>
@else
    <div class="p-3 text-center text-muted">
        {{ translate('No products found.') }}
    </div>
@endif
