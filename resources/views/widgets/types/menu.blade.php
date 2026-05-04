{{-- Menu Widget --}}
@php
$widgetSettings = $instance->settings ?? [];
if (is_object($widgetSettings)) {
$widgetSettings = (array) $widgetSettings;
}
$style = $widgetSettings['style'] ?? 'style-1';

// Standard positioning and padding logic
$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
$contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp

<div class="widget-menu {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
    'title' => $title ?? '',
    'widgetSettings' => $widgetSettings ?? []
    ])

    <div class="widget-content {{ $contentPadding }}">
        @if($menuItems->isNotEmpty())
        @if($style === 'style-2')
        {{-- Style 2: Modern Minimalist --}}
        <div class="modern-menu {{ $widgetSettings['menu_class'] ?? '' }}">
            @foreach($menuItems as $item)
            <div class="menu-item-group mb-1">
                <a href="{{ $item->slug }}"
                    class="menu-link transition-base d-flex align-items-center justify-content-between p-2 rounded-2 text-dark hover-bg-light">
                    <div class="d-flex align-items-center">
                        @if($item->icon)
                        <i class="{{ $item->icon }} me-2 text-muted menu-icon"></i>
                        @endif
                        <span class="menu-label fs-15">{{ $item->name }}</span>
                    </div>
                    @if($item->children && $item->children->count() > 0)
                    <i class="bi bi-chevron-right fs-12 text-muted opacity-50"></i>
                    @endif
                </a>

                @if($item->children && $item->children->count() > 0)
                <div class="submenu ps-3 mt-1 border-start ms-3">
                    @foreach($item->children as $child)
                    <a href="{{ $child->slug }}"
                        class="menu-link transition-base d-block py-1 px-2 rounded-2 text-gray-700 hover-text-primary fs-14">
                        {{ $child->name }}
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        {{-- Style 1: Classic --}}
        <ul class="nav flex-column {{ $widgetSettings['menu_class'] ?? '' }}">
            @foreach($menuItems as $item)
            <li class="nav-item">
                <a href="{{ $item->slug }}" class="nav-link px-0 text-dark hover-primary">
                    @if($item->icon)
                    <i class="{{ $item->icon }} me-2"></i>
                    @endif
                    {{ $item->name }}
                </a>
                @if($item->children && $item->children->count() > 0)
                <ul class="list-group flex-column ms-3 ps-3">
                    @foreach($item->children as $child)
                    <li class="nav-item list-group-action">
                        <a href="{{ $child->slug }}" class="nav-link px-0 py-1 text-gray-700 hover-primary fs-14">
                            {{ $child->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
        @else
        <p class="text-muted mb-0 small">{{ translate('No menu items') }}</p>
        @endif
    </div>
</div>
