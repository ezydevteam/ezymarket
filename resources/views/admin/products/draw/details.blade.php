<x-product :product="$product" :show-category="false" image-size="md">
    <x-slot:afterName>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
            <span class="status-badge border small text-gray-700 py-0 px-2"
                title="{{ translate('Product ID') }}">
                #{{ $product->id }}
            </span>
            <span class="text-gray-700" title="{{ translate('Seller') }}">
                <i class="bi bi-person me-1"></i>{{ '@' . $product->seller->username }}
            </span>
        </div>
    </x-slot:afterName>
</x-product>
