@php
    $uniqueId = $data['uniqueId'];
    $links = $data['links'] ?? [];
    $linkDisplay = $data['linkDisplay'];
    $linkStyle = $data['linkStyle'];
    $target = $data['target'];
@endphp

<div class="footer-links" id="{{ $uniqueId }}">
    @if(count($links) > 0)
        <ul class="list-unstyled mb-0 {{ $linkDisplay === 'horizontal' ? 'd-flex align-items-center gap-3' : '' }}">
            @foreach($links as $link)
                <li class="mb-1">
                    <a href="{{ $link['url'] ?? '#' }}" target="{{ $target }}" class="d-flex align-items-center text-reset">
                        @if($linkStyle === 'arrow')
                             <i class="bi bi-chevron-right text-xsmall me-2"></i>
                        @elseif($linkStyle === 'bullet')
                             <i class="bi bi-circle-fill me-2 opacity-75" style="font-size: 0.25rem;"></i>
                        @endif
                        {{ $link['label'] ?? '' }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
