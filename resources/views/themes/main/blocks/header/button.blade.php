@php
    $text = $data['text'];
    $url = $data['url'];
    $style = $data['style'];
    $size = $data['size'];
    $shape = $data['shape'];
    $icon = $data['icon'];
    $position = $data['position'];
    $target = $data['target'];

    $btnClass = 'btn-' . $style;
    $sizeClass = 'btn-' . $size;
@endphp

<a href="{{ $url }}"
   target="{{ $target }}"
   class="btn {{ $btnClass }} {{ $sizeClass }} {{ $shape }} ms-3 d-inline-flex align-items-center {{ $position === 'bottom' ? 'flex-column justify-content-center text-center lh-sm' : 'gap-2' }}"
   @if($position === 'tooltip')
       data-bs-toggle="tooltip"
       data-bs-placement="bottom"
       title="{{ $text }}"
       aria-label="{{ $text }}"
   @elseif($position === 'hidden')
       aria-label="{{ $text }}"
   @endif
>
    @if(!empty($icon))
        <i class="bi {{ $icon }} {{ ($position === 'inline' || $position === 'bottom') ? '' : 'fs-5' }}"></i>
    @endif

    @if($position === 'inline' || $position === 'bottom')
        <span class="{{ $position === 'bottom' ? 'small' : '' }}">{{ $text }}</span>
    @endif
</a>
