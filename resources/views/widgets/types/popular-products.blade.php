@php
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp
<div class="widget-popular-products">
    <div class="popular-products-title {{ $titlePadding }}">
        @include('widgets.partials.widget-title', [
            'title' => $title ?? '',
            'widgetSettings' => $widgetSettings ?? []
        ])
    </div>
    <div class="widget-content {{ $cardStyle === 'none' ? '' : 'px-3' }}">
        @if($products->isNotEmpty())
            <div class="list-group list-group-flush">
                @foreach($products as $index => $product)
                    <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id]) }}"
                        class="list-group-item list-group-item-action d-flex align-items-center px-0">
                        @if($widgetSettings['show_badge'] ?? true)
                            <span class="badge bg-primary rounded-pill me-2">{{ $index + 1 }}</span>
                        @endif
                        @if($widgetSettings['show_image'] ?? true)
                            <img src="{{ $product->thumbnail_url ?? asset('images/placeholder.png') }}"
                                 alt="{{ $product->name }}"
                                 class="image-fluid rounded me-3">
                        @endif
                        <div class="flex-grow-1">
                            <div class="text-truncate fs-15 fw-medium">{{ truncateText($product->name, 40) }}</div>
                            <div class="d-flex align-items-center gap-2">
                                @if($widgetSettings['show_price'] ?? true)
                                    <small class="fw-medium text-gray-200">{{ getAmount($product->regular_price) }}</small>
                                @endif
                                @if(($widgetSettings['show_sales'] ?? true) && $product->total_sales > 0)
                                    <small class="text-gray-200">
                                        <i class="bi bi-bag-check"></i> {{ number_format($product->total_sales) }}
                                    </small>
                                @endif
                                @if($widgetSettings['show_rating'] ?? false)
                                    @themeInclude('partials.rating-stars', [
                                        'rating' => $product->avg_reviews ?? 0,
                                        'ratings_classes' => 'ratings-sm'
                                    ])
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">{{ translate('No products found') }}</p>
        @endif
    </div>
</div>
