@php
$uniqueId = $data['uniqueId'];
$targetDate = $data['targetDate'];
$label = $data['label'];
$labelIcon = $data['labelIcon'];
$labelTop = $data['labelTop'];
$showDays = $data['showDays'];
$showHours = $data['showHours'];
$showMinutes = $data['showMinutes'];
$showSeconds = $data['showSeconds'];
$style = $data['style'];
$size = $data['size'];
$boxClass = $data['boxClass'];
$labelClass = $data['labelClass'];
@endphp

<div class="footer-countdown d-flex align-items-center gap-2 {{ $labelTop ? 'flex-column' : '' }} {{ $size }}"
    id="{{ $uniqueId }}" data-countdown="{{ $targetDate }}">
    @if($label || $labelIcon)
    <span class="{{ $labelClass }}">
        @if($labelIcon) <i class="bi {{ $labelIcon }} {{ $label ? 'me-1' : '' }}"></i> @endif
        {{ $label }}
    </span>
    @endif
    <div class="countdown-timer d-flex align-items-center gap-1 {{ $style === 'boxed' ? 'countdown-boxed' : '' }}">
        @if($showDays)
        <div class="countdown-item text-center {{ $boxClass }}">
            <span class="countdown-value fw-bold" data-days>00</span>
            <span class="countdown-unit small {{ $style === 'inline' ? 'text-muted' : '' }}">{{ translate('d') }}</span>
        </div>
        <span class="countdown-separator {{ $style === 'boxed' ? 'd-none' : '' }}">:</span>
        @endif
        @if($showHours)
        <div class="countdown-item text-center {{ $boxClass }}">
            <span class="countdown-value fw-bold" data-hours>00</span>
            <span class="countdown-unit small {{ $style === 'inline' ? 'text-muted' : '' }}">{{ translate('h') }}</span>
        </div>
        <span class="countdown-separator {{ $style === 'boxed' ? 'd-none' : '' }}">:</span>
        @endif
        @if($showMinutes)
        <div class="countdown-item text-center {{ $boxClass }}">
            <span class="countdown-value fw-bold" data-minutes>00</span>
            <span class="countdown-unit small {{ $style === 'inline' ? 'text-muted' : '' }}">{{ translate('m') }}</span>
        </div>
        <span class="countdown-separator {{ $style === 'boxed' ? 'd-none' : '' }}">:</span>
        @endif
        @if($showSeconds)
        <div class="countdown-item text-center {{ $boxClass }}">
            <span class="countdown-value fw-bold" data-seconds>00</span>
            <span class="countdown-unit small {{ $style === 'inline' ? 'text-muted' : '' }}">{{ translate('s') }}</span>
        </div>
        @endif
    </div>
</div>
