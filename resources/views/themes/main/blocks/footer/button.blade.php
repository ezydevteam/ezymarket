@php
    $id = $data['uniqueId'];
    $text = $data['text'];
    $url = $data['url'];
    $style = $data['style'];
    $size = $data['size'];
    $icon = $data['icon'];
    $iconPosition = $data['iconPosition'];
    $target = $data['target'];
    $fullWidth = $data['fullWidth'];
@endphp
<div id="{{ $id }}" class="footer-button">
    <a href="{{ $url }}" target="{{ $target }}" class="btn btn-{{ $style }} btn-{{ $size }} {{ $fullWidth ? 'w-100' : '' }}">
        @if($icon && $iconPosition === 'left')
            <i class="bi {{ $icon }} me-1"></i>
        @endif
        {{ $text }}
        @if($icon && $iconPosition === 'right')
            <i class="bi {{ $icon }} ms-1"></i>
        @endif
    </a>
</div>
