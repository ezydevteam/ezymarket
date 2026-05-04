@php
    $uniqueId = $data['uniqueId'];
    $type = $data['type'];
    $margin = $data['margin'];
@endphp

<div id="{{ $uniqueId }}" class="footer-divider {{ ($margin && $type === 'vertical') ? 'mx-' . $margin : 'my-' . $margin }}"></div>
