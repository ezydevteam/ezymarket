@php $data = (object)($data ?? []); @endphp

@if(!empty($data->richTextContent))
<div id="{{ $data->uniqueId }}" class="home-rich-text {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="rich-text-content">
        {!! $data->richTextContent !!}
    </div>
</div>
@endif
