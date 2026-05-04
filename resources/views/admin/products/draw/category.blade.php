<div class="text-start">
    <span class="status-badge bg-secondary-subtle text-secondary small py-1 px-2 border"
        title="{{ translate('Category') }}">
        <i class="bi bi-folder2-open me-1"></i>
        {{ $product->category->name }}
        @if($product->subCategory)
            <i class="bi bi-chevron-right small mx-1"></i>{{ $product->subCategory->name }}
        @endif
    </span>
</div>
