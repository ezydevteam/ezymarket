@php
    $uniqueId = $data['uniqueId'];
    $socials = $data['socials'] ?? [];
    $showTooltip = $data['showTooltip'] ?? false;
    $linkClass = $data['linkClass'] ?? '';
    $gapClass = $data['gapClass'] ?? 'gap-2';
@endphp

<div class="footer-social" id="{{ $uniqueId }}">
    @if(!empty($socials))
        <div class="d-flex flex-wrap {{ $gapClass }}">
            @foreach($socials as $social)
                <a href="{{ formatExternalUrl($social['url']) }}"
                   target="_blank"
                   class="{{ $linkClass }} social-link-{{ $social['platform'] }}"
                   aria-label="{{ $social['label'] }}"
                   @if($showTooltip) data-bs-toggle="tooltip" title="{{ $social['label'] }}" @endif>
                    <i class="bi {{ $social['icon'] }}"></i>
                </a>
            @endforeach
        </div>
    @endif
</div>
