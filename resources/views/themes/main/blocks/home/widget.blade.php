@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}"
    class="home-widget {{ $isFullWidth ? $data->containerClass ?? 'container container-default' : '' }}">
    <x-widget name="home-sidebar" />
</div>
