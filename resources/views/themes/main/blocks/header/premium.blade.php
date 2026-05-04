@if (isPremiumAvailable())
    @php
        $url = $data['url'];
        $buttonClasses = $data['buttonClasses'];
        $tooltipAttr = $data['tooltipAttr'];
        $ariaLabel = $data['ariaLabel'];
        $showIcon = $data['showIcon'];
        $iconClass = $data['iconClass'];
        $showLabel = $data['showLabel'];
        $labelClass = $data['labelClass'];
        $text = $data['text'];
    @endphp
    <div class="header-premium">
        <a href="{{ $url }}" class="{{ $buttonClasses }}"
            {!! $tooltipAttr !!}
            {!! $ariaLabel !!}>
            @if($showIcon)
                <i class="{{ $iconClass }} me-1"></i>
            @endif

            @if($showLabel)
                <span class="{{ $labelClass }}">{{ $text }}</span>
            @endif
        </a>
    </div>
@endif
