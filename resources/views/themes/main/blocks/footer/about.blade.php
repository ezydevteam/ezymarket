@php
    $id = $data['uniqueId'];
    $aboutText = $data['aboutText'];
@endphp

@if (!empty($aboutText))
    <div id="{{ $id }}" class="footer-about">
        <p class="mb-0">{{ $aboutText }}</p>
    </div>
@endif
