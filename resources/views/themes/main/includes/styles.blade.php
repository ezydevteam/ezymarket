@bootstrap
<link rel="stylesheet" href="{{ asset('vendor/libs/codebay/toastr/css/toastr.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/plyr/plyr.min.css') }}" />
@stack('styles_libs')
@themeColors
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/app.css') }}" />
@if (getDirection() == 'rtl')
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/app.rtl.css') }}" />
@endif
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/dark.css') }}" />
@themeCustomStyle
@stack('styles')
@php
$fonts = array_filter([
    $headerFontsLink ?? null,
    $footerFontsLink ?? null,
    $homeFontsLink ?? null
], fn($link) => $link && stripos($link, 'poppins') === false);

$fonts = array_unique($fonts);
@endphp
@foreach($fonts as $fontLink)
<link rel="stylesheet" href="{{ $fontLink }}">
@endforeach
@if(!empty($headerSections))
<style>@foreach($headerSections as $section){!! $section['css'] ?? '' !!}@endforeach</style>
@endif
@if(!empty($homeSections))
<style>@foreach($homeSections as $section){!! $section['css'] ?? '' !!}@endforeach</style>
@endif
@if(!empty($footerSections))
<style>@foreach($footerSections as $section){!! $section['css'] ?? '' !!}@endforeach</style>
@endif
@if(!empty($productPageCss))
<style>{!! $productPageCss !!}</style>
@endif
{!! $themeSettings->extra_codes->head_code !!}
<livewire:styles />
