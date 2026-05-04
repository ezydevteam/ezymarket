@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}"
    class="home-divider d-flex justify-content-{{ $data->blockAlign ?? 'center' }} {{ $isFullWidth ? $data->containerClass : '' }}">
    <div class="divider-line {{ $data->dividerStyle ?? 'horizontal' }}"></div>
</div>
