@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}" class="home-button {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="d-flex justify-content-{{ $data->blockAlignment ?? 'center' }}">
        @if(!empty($data->buttonData))
        @php
        $btn = $data->buttonData;
        $aosAttr = $btn['aosAnim'] ? 'data-aos="' . $btn['aosAnim'] . '" data-aos-delay="' . $btn['aosDelay'] . '"' : '';
        @endphp
        <a href="{{ $btn['link'] }}" target="{{ $btn['target'] }}"
            class="btn {{ $btn['btnStyle'] }} {{ $btn['btnSize'] }} {{ $btn['btnShape'] }}" {!! $aosAttr !!}>
            @if(!empty($btn['btnIcon']))
            <i class="bi {{ $btn['btnIcon'] }} me-1"></i>
            @endif
            {{ $btn['label'] }}
        </a>
        @endif
    </div>
</div>
