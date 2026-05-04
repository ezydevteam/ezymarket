@php
    $htmlContent = $data['htmlContent'];
@endphp
@if($htmlContent)
<div class="header-html-block">
    {!! $htmlContent !!}
</div>
@endif
