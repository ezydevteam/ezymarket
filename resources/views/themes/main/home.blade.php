@extends('themes.main.layouts.app')
@section('title', @$settings->seo->title ?: translate('Home'))

@section('body_content')
    <div id="ezymarket-home" class="theme-home">
        @if (!empty($homeSections))
        @foreach ($homeSections as $section)
        @php
        $sectionId = $section['sectionId'];
        $containerId = $section['containerId'];
        $containerClass = $section['containerClass'];
        $isFullWidth = $section['isFullWidth'] ?? false;
        @endphp
        <div id="{{ $sectionId }}" class="home-section">
            @if ($isFullWidth)
            @php
            $blocks = $section['columns'][0]['blocks'] ?? [];
            @endphp
            @foreach ($blocks as $block)
            <div class="home-block {{ $block['wrapper_class'] ?? '' }} mb-5">
                @themeInclude($block['view'], ['data' => $block['data'], 'isFullWidth' => $isFullWidth])
            </div>
            @endforeach
            @else
            <div id="{{ $containerId }}" class="{{ $containerClass }}">
                <div class="row g-5">
                    @foreach ($section['columns'] as $col)
                    <div class="col-12 col-lg-{{ $col['width'] }}">
                        @foreach ($col['blocks'] as $block)
                        <div class="home-block {{ $block['wrapper_class'] ?? '' }} mb-5">
                            @themeInclude($block['view'], ['data' => $block['data'], 'isFullWidth' => $isFullWidth])
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
        @elseif (!empty($homeBlocks))
        @foreach ($homeBlocks as $key => $homeBlock)
        @themeInclude($homeBlock['view'])
        @endforeach
        @endif
    </div>
@endsection

@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/aos/aos.min.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/aos/aos.min.js') }}"></script>
@endpush
