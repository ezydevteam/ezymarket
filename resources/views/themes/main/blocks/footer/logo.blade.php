@php
    $uniqueId = $data['uniqueId'];
    $logoStyle = $data['logoStyle'];
    $customClass = $data['customClass'];
    $siteName = $data['siteName'];
    $logoUrl = $data['logoUrl'];
@endphp

<div class="footer-logo {{ $customClass }}" id="{{ $uniqueId }}">
    <a href="{{ route('home') }}" class="d-inline-flex align-items-center text-reset">
        @if ($logoStyle === 'site_title')
            <h2 class="site-name fw-bolder text-white mb-0">{{ $siteName }}</h2>
        @else
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="img-fluid">
        @endif
    </a>
</div>
