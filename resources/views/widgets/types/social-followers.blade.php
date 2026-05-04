{{-- Social Followers Widget --}}
@php
    $style = $widgetSettings['style'] ?? 'list';

    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp
<div class="widget-social-followers {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $widgetTitle ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        @if(count($socials) > 0)
            @if($style === 'grid')
                <div class="row g-2 row-cols-3">
                    @foreach($socials as $key => $social)
                        <div class="col">
                            <a href="{{ formatExternalUrl($social['url']) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="d-flex flex-column align-items-center justify-content-center p-2 rounded-3 text-white h-100 transition-all hover-lift"
                               style="background-color: {{ $social['color'] }}; min-height: 70px;"
                               title="{{ $social['name'] }} {{ $social['count'] ? '(' . $social['count'] . ')' : '' }}">
                                <i class="{{ $social['icon'] }} fs-4 mb-1"></i>
                                @if(!empty($social['count']))
                                    <span class="opacity-75 fw-semibold fs-14">{{ $social['count'] }}</span>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($socials as $key => $social)
                        <a href="{{ formatExternalUrl($social['url']) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-sm d-flex align-items-center justify-content-between rounded-pill px-3 py-2 transition-all hover-lift"
                           style="background-color: {{ $social['color'] }}; color: #fff;">
                            <span class="fw-medium">
                                <i class="{{ $social['icon'] }} me-2"></i>{{ $social['name'] }}
                            </span>
                            @if(!empty($social['count']))
                                <span class="badge bg-white text-dark rounded-pill">{{ $social['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            <p class="text-muted mb-0">
                {{ translate('No social links configured. Please configure social links from settings->general.') }}
            </p>
        @endif
    </div>
</div>
