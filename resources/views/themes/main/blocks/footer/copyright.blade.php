@php
    $id = $data['uniqueId'];
    $copyrightText = $data['copyrightText'];
@endphp

<div id="{{ $id }}" class="footer-copyright">
    <p class="mb-0 opacity-75">
        {!! $copyrightText !!}
    </p>
</div>
