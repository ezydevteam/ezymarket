@php
    $id = $data['uniqueId'];
    $wrapperTag = $data['wrapperTag'];
    $wrapperAttrs = $data['wrapperAttrs'];
    $iconClass = $data['iconClass'];
    $iconSize = $data['iconSize'];
    $showLabel = $data['showLabel'];
    $labelText = $data['labelText'];
    $labelPosition = $data['labelPosition'];
@endphp

<div id="{{ $id }}" class="header-icon-wrapper">
    <{{ $wrapperTag }} {!! $wrapperAttrs !!}>
        @if($showLabel && $labelText && $labelPosition === 'left')
            <span class="header-icon-label">{{ $labelText }}</span>
        @endif

        <i class="bi {{ $iconClass }} {{ $iconSize }}"></i>

        @if($showLabel && $labelText && $labelPosition === 'right')
            <span class="header-icon-label">{{ $labelText }}</span>
        @endif

        @if($showLabel && $labelText && $labelPosition === 'bottom')
            <span class="header-icon-label small lh-1">{{ $labelText }}</span>
        @endif
    </{{ $wrapperTag }}>
</div>
