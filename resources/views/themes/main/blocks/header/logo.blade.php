@php
    $id = $data['uniqueId'];
    $logoStyle = $data['logoStyle'];
    $siteName = $data['siteName'];
    $logoUrl = $data['logoUrl'];
@endphp

<div id="{{ $id }}" class="header-logo-wrapper">
    <a href="{{ route('home') }}" class="d-inline-flex align-items-center">
        @if ($logoStyle === 'site_title')
            <h1 class="site-name fw-bold text-reset mb-0">{{ $siteName }}</h1>
        @else
            <img class="site-logo" src="{{ $logoUrl }}" alt="{{ $siteName }}">
        @endif
    </a>
</div>
