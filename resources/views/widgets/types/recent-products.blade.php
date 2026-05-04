{{-- Recent Products Widget --}}
@php
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
    $contentPadding = in_array($titleStyle, ['style_1', 'style_2']) ? 'px-3' : 'px-0';
@endphp
<div class="widget-recent-products {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $title ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $cardStyle === 'none' ? 'p-0' : 'pt-1 pb-2' }}">
        @if($products->isNotEmpty())
            <ul class="list-group list-group-flush">
                @foreach($products as $product)
                <li class="list-group-item bg-transparent {{ $contentPadding }}">
                    <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id]) }}"
                        class="text-dark d-flex align-items-center bg-transparent gap-3">
                        @if($widgetSettings['show_image'] ?? true)
                        <div class="flex-shrink-0 image-fluid rounded-2">
                            <img src="{{ $product->thumbnail_url ?? asset('images/placeholder.png') }}"
                                 alt="{{ $product->name }}">
                        </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ truncateText($product->name, 40) }}</div>
                            @if($widgetSettings['show_price'] ?? true)
                                <small class="text-primary fw-medium">{{ getAmount($product->regular_price) }}</small>
                            @endif
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        @else
            <div class="p-3 pt-0">
                <p class="text-muted mb-0">{{ translate('No products found') }}</p>
            </div>
        @endif
    </div>
</div>
