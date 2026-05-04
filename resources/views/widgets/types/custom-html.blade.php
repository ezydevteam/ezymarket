{{-- Custom HTML Widget --}}
@php
    $widgetSettings = $instance->settings ?? [];
    if (is_object($widgetSettings)) {
        $widgetSettings = (array) $widgetSettings;
    }

    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp
<div class="widget-custom-html {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $title ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        {!! $widgetSettings['html'] ?? '' !!}
    </div>
</div>
