{{-- Newsletter Widget --}}
@php
$showWidgetTitle = $widgetSettings['show_title'] ?? true;
$widgetTitle = $widgetTitle ?? ($widgetSettings['title'] ?? '');
$description = ($widgetSettings['description'] ?? '') ?: translate('Get the latest updates and offers.');
$style = $widgetSettings['style'] ?? 'style-1';
$showIcon = $widgetSettings['show_icon'] ?? true;
$placeholder = ($widgetSettings['placeholder'] ?? '') ?: translate('Enter your email');
$buttonText = ($widgetSettings['button_text'] ?? '') ?: translate('Subscribe');
$buttonType = $widgetSettings['button_type'] ?? 'text_only';

// Standard positioning and padding logic
$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
$contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp

<div class="widget-newsletter {{ $titlePadding }}">
    <div class="widget-content {{ $contentPadding }}">
        @if ($style === 'style-1')
        {{-- Style 1: Classic Pill --}}
        <div class="text-center">
            @if ($showIcon)
            <div class="mb-2 text-primary">
                <i class="bi bi-envelope-paper fs-1"></i>
            </div>
            @endif
            @include('widgets.partials.widget-title', [
            'title' => $widgetTitle,
            'widgetSettings' => $widgetSettings,
            'titleClass' => 'mb-2'
            ])
            @if ($description)
            <p class="small text-gray-700 mb-4">{{ $description }}</p>
            @endif
            <livewire:newsletter-form :placeholder="$placeholder" :buttonText="$buttonText" :buttonDisplay="$buttonType"
                :style="'pill'" />
        </div>

        @elseif ($style === 'style-2')
        {{-- Style 2: Inline Dash Card --}}
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3">
                @if ($showIcon)
                <div class="flex-shrink-0">
                    <div class="bg-primary-subtle rounded-3 p-3">
                        <i class="bi bi-envelope text-primary fs-3"></i>
                    </div>
                </div>
                @endif
                <div class="flex-grow-1">
                    @include('widgets.partials.widget-title', [
                    'title' => $widgetTitle,
                    'widgetSettings' => $widgetSettings,
                    'titleClass' => 'mb-2'
                    ])
                    @if ($description)
                    <p class="small text-muted mb-3">{{ $description }}</p>
                    @endif
                    <livewire:newsletter-form :placeholder="$placeholder" :buttonText="$buttonText"
                        :buttonDisplay="$buttonType" :style="'boxed'" />
                </div>
            </div>
        </div>

        @elseif ($style === 'style-3')
        {{-- Style 3: Modern --}}
        <div class="newsletter-minimal text-center">
            @if ($showIcon)
            <div class="mb-2 text-primary">
                <i class="bi bi-envelope-paper fs-1"></i>
            </div>
            @endif
            @include('widgets.partials.widget-title', [
            'title' => $widgetTitle,
            'widgetSettings' => $widgetSettings,
            'titleClass' => 'mb-2'
            ])
            @if ($description)
            <p class="text-gray-700 mb-3 fs-14">{{ $description }}</p>
            @endif
            <livewire:newsletter-form :placeholder="$placeholder" :buttonText="$buttonText" :buttonDisplay="$buttonType"
                :style="'modern'" />
        </div>
        @endif
    </div>
</div>
