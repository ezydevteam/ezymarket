@php $data = (object)($data ?? []); @endphp

@if(!empty($data->htmlContent))
<div id="{{ $data->uniqueId }}" class="home-html {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="custom-html-section">
        {!! $data->htmlContent !!}
    </div>
</div>
@endif
