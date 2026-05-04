@php
    $id = $data['uniqueId'];
    $htmlContent = $data['htmlContent'];
@endphp
@if(!empty($htmlContent))
<div id="{{ $id }}" class="footer-html-block">
    {!! $htmlContent !!}
</div>
@endif
