@if ($product->demo_link || $product->gallery)
<div class="d-flex flex-wrap align-items-center justify-content-center gap-3 {{ $wrapperClass ?? 'mt-3' }}">
    @if ($product->demo_link)
    <a href="{{ $product->view_demo }}" target="_blank"
        class="btn btn-sm btn-primary btn-modern {{ $previewBtnClass ?? '' }}">
        <i class="bi bi-grid-fill me-2"></i>{{ translate('Live Preview') }}
    </a>
    @endif
    @if ($product->gallery)
    <div class="product-gallery d-inline-flex">
        <div class="d-none">
            @foreach ($product->gallery_links as $index => $image)
            <a href="{{ $image }}" data-fancybox="product-gallery"
                data-caption="{{ $product->name }} Gallery {{ $index + 1 }}"></a>
            @endforeach
        </div>
        <button class="btn btn-sm bg-dark-subtle btn-modern view-gallery-btn {{ $galleryBtnClass ?? '' }}">
            <i class="bi bi-images me-2"></i>{{ translate('Gallery') }}
        </button>
    </div>
    @endif
</div>
@endif
