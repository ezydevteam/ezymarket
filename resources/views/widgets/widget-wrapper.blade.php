@php $content = trim($content); @endphp

@if(!empty($content))
    @php
        $cardStyle = $instance->getSetting('widget_card_style', 'card-border');
        $cardClass = match($cardStyle) {
            'card-border' => 'card-border',
            'card-shadow' => 'card-shadow-sm',
            'modern-card' => 'modern-card',
            'modern-card-2' => 'modern-card-2',
            default => '',
        };
    @endphp
    <div class="widget widget-{{ $instance->widget->slug ?? 'unknown' }} {{ $cardClass }} mb-4"
        data-widget-id="{{ $instance->id }}">
        {!! $content !!}
    </div>
@endif
