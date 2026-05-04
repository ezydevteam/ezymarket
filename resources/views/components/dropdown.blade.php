@props([
    'align' => 'end', // start, end, center
    'width' => null, // Custom width (e.g., '200px')
    'maxHeight' => null, // Custom max height (e.g., '300px')
    'trigger' => null, // Slot for custom trigger button
    'icon' => 'bi-three-dots-vertical', // Default icon
    'buttonClass' => 'btn-icon small text-muted', // Button classes
    'menuClass' => '', // Additional menu classes
])

@php
    $styles = [];
    if ($width) $styles[] = "min-width: {$width}";
    if ($maxHeight) $styles[] = "max-height: {$maxHeight}; overflow-y: auto";
    $styleAttr = !empty($styles) ? 'style="' . implode('; ', $styles) . ';"' : '';
@endphp

<div class="dropdown" {{ $attributes }}>
    @if($trigger)
        {{ $trigger }}
    @else
        <button type="button" class="{{ $buttonClass }}" data-bs-toggle="dropdown"
            aria-expanded="false" data-bs-popper-config='{"strategy": "fixed"}'>
            <i class="{{ $icon }}"></i>
        </button>
    @endif

    <ul class="dropdown-menu border shadow-sm dropdown-menu-{{ $align }} {{ $menuClass }}" {!! $styleAttr !!}>
        {{ $slot }}
    </ul>
</div>
