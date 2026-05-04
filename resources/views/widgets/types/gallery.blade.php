{{-- Gallery Widget --}}
@php
    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp

<div class="widget-gallery {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $widgetTitle ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        @if(count($images) > 0)
            <div class="row g-2">
                @foreach($images as $index => $image)
                    @php $caption = $captions[$index] ?? ''; @endphp
                    <div class="col-{{ 12 / $columns }}">
                        <a href="{{ $image }}"
                           data-fancybox="widget-gallery-{{ $instance->id }}"
                           data-caption="{{ $caption }}"
                           class="d-block shadow-sm hover-zoom rounded overflow-hidden"
                           title="{{ $caption }}">
                            <img src="{{ $image }}"
                                 alt="{{ $caption ?: 'product-gallery-'.$index }}"
                                 class="img-fluid object-fit-cover w-100"
                                 style="height: 100px;">
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">{{ translate('No images added') }}</p>
        @endif
    </div>
</div>
