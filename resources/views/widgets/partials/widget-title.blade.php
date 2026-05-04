@php
    // Determine title visibility, default to true if not explicitly set
    $showTitle = $widgetSettings['show_title'] ?? true;
    // For string '0' or '1' from database settings json, cast to bool
    if (is_string($showTitle)) {
        $showTitle = $showTitle === '1' || $showTitle === 'true';
    }

    if ($showTitle) {
        $titleStyle = $widgetSettings['title_style'] ?? 'default';
        $titleSize = $widgetSettings['title_size'] ?? 'fs-5';
        $titleTransform = $widgetSettings['title_transform'] ?? 'text-capitalize';
        $titleWeight = $widgetSettings['title_weight'] ?? 'fw-medium';
        $titleColor = $widgetSettings['title_color'] ?? '';
        $titleIcon = $widgetSettings['title_icon'] ?? '';

        $classes = "widget-title {$titleSize} {$titleTransform} {$titleWeight}";
        $marginBottom = in_array($titleStyle, ['style_1', 'style_2']) ? 'mb-0' : 'mb-3' ;
    }
@endphp

@if($showTitle && !empty($title))
    <div class="widget-title-wrapper title-{{ $titleStyle }} {{ $titleClass ?? $marginBottom }}"
         @if($titleColor) style="--widget-title-color: {{ $titleColor }};" @endif>

        <h5 class="{{ $classes }} mb-0"
            @if($titleColor && $titleStyle === 'default') style="color: var(--widget-title-color)" @endif>

            @if($titleIcon && $titleIcon !== 'none')
                <i class="bi {{ $titleIcon }} me-1"></i>
            @endif
            <span>{{ $title }}</span>

        </h5>
    </div>
@endif
