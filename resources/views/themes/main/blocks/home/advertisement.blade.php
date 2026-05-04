@php $data = (object)($data ?? []); @endphp

@if(!empty($data->adAlias))
<div id="{{ $data->uniqueId }}" class="home-ads {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <x-advertisement :alias="$data->adAlias" />
</div>
@endif
 
