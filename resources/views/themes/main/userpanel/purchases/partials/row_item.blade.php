<div class="d-flex align-items-center gap-3">
    @php $product = $purchase->product; @endphp
    <a href="{{ $product->view_link }}" class="image-fluid flex-shrink-0">
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
    </a>
    <div class="flex-shrink-1">
        <div class="mb-0">
            <a href="{{ $product->view_link }}"
                class="text-dark fw-medium hover-primary"
                title="{{ $product->name }}">
                {{ truncateText($product->name, 50) }}
            </a>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <code class="small text-gray-700 bg-light px-2 py-0 rounded-2"
                title="{{ translate('Purchase ID') }}">
                #{{ $purchase->id }}
            </code>
            @if (@settings('product')->reviews_status && !$product->isDeleted())
            <div class="d-flex align-items-center gap-2">
                @themeInclude('partials.rating-stars', [
                    'args' => $purchase->review,
                    'ratings_classes' => 'sm',
                    'type' => 'full',
                    'rating_number' => false,
                    'star_size' => 'fs-10',
                    'star_gap' => 'gap-1'
                ])
                @if (!authUser()->hasReviewedProduct($product->id))
                    <a href="{{ $product->getReviewsLink() }}"
                        class="small text-primary hover-underline export-ignore">
                        {{ translate('Rate') }}
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
