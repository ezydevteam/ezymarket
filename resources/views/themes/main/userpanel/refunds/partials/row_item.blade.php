<div class="d-flex align-items-center gap-3">
    @php $product = $refund->purchase->product; @endphp
    <a href="{{ $product->view_link }}" class="image-fluid flex-shrink-0">
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
    </a>
    <div class="flex-shrink-1">
        <a href="{{ $product->view_link }}" class="text-dark fw-medium d-block mb-1 hover-primary"
            title="{{ $product->name }}">
            {{ truncateText($product->name, 50) }}
        </a>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <code class="small text-gray-700 bg-light px-2 py-0 rounded" title="{{ translate('Purchase ID') }}">
                #{{ $refund->purchase->id }}
            </code>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 fw-normal small"
                title="{{ translate('License type') }}">
                {{ $refund->purchase->isRegularLicense() ? translate('Regular') : translate('Extended') }}
            </span>
        </div>
    </div>
</div>
