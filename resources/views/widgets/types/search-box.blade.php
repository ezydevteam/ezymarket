{{-- Search Box Widget --}}
@php
    $showWidgetTitle = $widgetSettings['show_title'] ?? false;
    $widgetTitle = $widgetTitle ?? ($widgetSettings['title'] ?? '');
    $style = $widgetSettings['style'] ?? 'style-1';
    $searchType = $widgetSettings['search_type'] ?? 'products';
    $placeholder = ($widgetSettings['placeholder'] ?? '') ?: translate('Search...');
    $showButton = $widgetSettings['show_button'] ?? true;
    $buttonText = $widgetSettings['button_text'] ?? '';

    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');

    // Determine search route based on type
    $searchParam = $searchType === 'blog' ? 'search' : 'keyword';
    $searchRoute = match($searchType) {
        'blog' => route('blog.index'),
        'all' => route('search'),
        default => route('products.index'),
    };
@endphp

<div class="widget-search-box {{ $style }} {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $widgetTitle,
        'widgetSettings' => $widgetSettings
    ])

    <div class="search-box-content {{ $contentPadding }}">
        <form action="{{ $searchRoute }}" method="GET">
            @if ($style === 'style-1')
                {{-- Style 1: Classic --}}
                <div class="input-group search-group-modern">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                            name="{{ $searchParam }}"
                            class="form-control border-start-0 ps-0"
                            placeholder="{{ $placeholder }}"
                            value="{{ request($searchParam) }}">
                    @if ($showButton)
                        <button class="btn btn-primary px-3" type="submit">
                            @if ($buttonText)
                                <span>{{ $buttonText }}</span>
                            @else
                                <i class="bi bi-arrow-right"></i>
                            @endif
                        </button>
                    @endif
                </div>

            @elseif ($style === 'style-2')
                {{-- Style 2: Rounded Pill --}}
                <div class="position-relative search-rounded-modern">
                    <input type="text"
                            name="{{ $searchParam }}"
                            class="form-control rounded-pill ps-4 pe-5 bg-light border-0"
                            placeholder="{{ $placeholder }}"
                            value="{{ request($searchParam) }}">
                    @if ($showButton)
                    <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-primary pe-3 text-decoration-none"
                        type="submit">
                        <i class="bi bi-search"></i> @if ($buttonText) <span class="ms-1">{{ $buttonText }}</span> @endif
                    </button>
                    @endif
                </div>

            @elseif ($style === 'style-3')
                {{-- Style 3: Minimal Underline --}}
                <div class="d-flex align-items-center gap-2 border-bottom pb-1 search-minimal-modern">
                    <input type="text"
                            name="{{ $searchParam }}"
                            class="form-control form-control-sm border-0 bg-transparent p-0 shadow-none"
                            placeholder="{{ $placeholder }}"
                            value="{{ request($searchParam) }}">
                    @if ($showButton)
                    <button class="btn btn-sm p-0 d-flex align-items-center hover-primary transition-base" type="submit">
                        <i class="bi bi-search"></i> @if ($buttonText) <span class="ms-1">{{ $buttonText }}</span> @endif
                    </button>
                    @endif
                </div>
            @endif
        </form>
    </div>
</div>
