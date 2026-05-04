@props([
'style' => null, // spinner, dots, pulse, bars, ring, bounce
'size' => 'md',
'color' => 'primary',
'text' => null,
'spinner_text' => false,
'centered' => false,
])

@php
$sizeClasses = [
'sm' => 'loader-sm',
'md' => 'loader-md',
'lg' => 'loader-lg',
];

$colorClasses = [
'primary' => 'text-primary',
'secondary' => 'text-secondary',
'success' => 'text-success',
'danger' => 'text-danger',
'warning' => 'text-warning',
'info' => 'text-info',
'light' => 'text-light',
'dark' => 'text-dark',
'white' => 'text-white',
];

$loaderStyle = $style ?? @$settings->general->loader_style ?: 'dots';
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
$colorClass = $colorClasses[$color] ?? $colorClasses['primary'];
$containerClass = $centered ? 'd-flex justify-content-center align-items-center' : '';
@endphp

<div {{ $attributes->merge(['class' => trim("codebay-loader {$containerClass}")]) }}>
    @if($loaderStyle === 'spinner')
    {{-- Default Bootstrap Spinner --}}
    <div class="d-flex align-items-center gap-2">
        <span class="spinner-border spinner-border-{{ $size }} {{ $colorClass }}" role="status"></span>
        <span class="{{ $spinner_text ? '' : 'visually-hidden' }}">{{ translate('Loading...') }}</span>
    </div>

    @elseif($loaderStyle === 'dots')
    {{-- Three Dots Loader --}}
    <div class="loader-dots {{ $sizeClass }}">
        <span class="dot {{ $colorClass }}"></span>
        <span class="dot {{ $colorClass }}"></span>
        <span class="dot {{ $colorClass }}"></span>
    </div>

    @elseif($loaderStyle === 'pulse')
    {{-- Pulsing Circle --}}
    <div class="loader-pulse {{ $sizeClass }}">
        <span class="pulse-circle {{ $colorClass }}"></span>
    </div>

    @elseif($loaderStyle === 'bars')
    {{-- Animated Bars --}}
    <div class="loader-bars {{ $sizeClass }}">
        <span class="bar {{ $colorClass }}"></span>
        <span class="bar {{ $colorClass }}"></span>
        <span class="bar {{ $colorClass }}"></span>
        <span class="bar {{ $colorClass }}"></span>
    </div>

    @elseif($loaderStyle === 'ring')
    {{-- Ring Loader --}}
    <div class="loader-ring {{ $sizeClass }}">
        <div class="ring {{ $colorClass }}"></div>
    </div>

    @elseif($loaderStyle === 'bounce')
    {{-- Bouncing Balls --}}
    <div class="loader-bounce {{ $sizeClass }}">
        <span class="bounce-ball {{ $colorClass }}"></span>
        <span class="bounce-ball {{ $colorClass }}"></span>
        <span class="bounce-ball {{ $colorClass }}"></span>
    </div>
    @endif

    @if($text)
    <div class="loader-text mt-2 {{ $colorClass }}">{{ $text }}</div>
    @endif
</div>
