{{-- Dropdown Item, Divider, or Header --}}
@props([
    'type' => 'item', // item, button, divider, header
    'href' => '#',
    'icon' => null,
    'iconClass' => 'me-2',
    'target' => null,
    'color' => null, // e.g., 'primary', 'danger', 'success'
    'text' => null, // For header type
])

@if($type === 'divider')
    <li><hr class="dropdown-divider"></li>
@elseif($type === 'header')
    <li><h6 class="dropdown-header">{{ $text ?? $slot }}</h6></li>
@elseif($type === 'button')
    <li>
        <button
            type="button"
            class="dropdown-item py-2 @if($color) text-{{ $color }} @endif {{ $attributes->get('class') }}"
            {{ $attributes->except(['class']) }}
        >
            @if($icon)
                <i class="bi {{ $icon }} {{ $iconClass }} me-2"></i>
            @endif
            {{ $slot }}
        </button>
    </li>
@else
    <li>
        <a
            class="dropdown-item py-2 @if($color) text-{{ $color }} @endif {{ $attributes->get('class') }}"
            href="{{ $href }}"
            @if($target) target="{{ $target }}" @endif
            {{ $attributes->except(['class']) }}
        >
            @if($icon)
                <i class="bi {{ $icon }} {{ $iconClass }} me-2"></i>
            @endif
            {{ $slot }}
        </a>
    </li>
@endif
