{{-- Image Widget --}}
@php
    $widgetSettings = $instance->settings ?? [];
    if (is_object($widgetSettings)) {
        $widgetSettings = (array) $widgetSettings;
    }
    $imageRaw = $widgetSettings['image_url'] ?? null;
    $imageUrl = $imageRaw ? storageUrl($imageRaw) : null;

    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp
<div class="widget-image {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $title ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        @if($imageUrl)
            @if(!empty($widgetSettings['link_url']))
                <a href="{{ formatExternalUrl($widgetSettings['link_url']) }}"
                   @if($widgetSettings['open_new_tab'] ?? false) target="_blank" rel="noopener" @endif
                   @if(!empty($widgetSettings['alt_text'])) title="{{ $widgetSettings['alt_text'] }}" @endif>
                    <img src="{{ $imageUrl }}"
                         alt="{{ $widgetSettings['alt_text'] ?? 'image' }}"
                         class="img-fluid rounded-3">
                </a>
            @else
                <img src="{{ $imageUrl }}"
                     alt="{{ $widgetSettings['alt_text'] ?? 'image' }}"
                     @if(!empty($widgetSettings['alt_text'])) title="{{ $widgetSettings['alt_text'] }}" @endif
                     class="img-fluid rounded-3">
            @endif
        @endif
    </div>
</div>
