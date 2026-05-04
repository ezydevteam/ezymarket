@php
    $id = $data['uniqueId'];
    $socials = $data['socials'];
    $socialIcons = $data['socialIcons'];
    $brandColors = $data['brandColors'];

    $iconSize = $data['iconSize'];
    $iconStyle = $data['iconStyle'];
    $viewStyle = $data['viewStyle'];
    $displayStyle = $data['displayStyle'];
    $colorStyle = $data['colorStyle'];
    $activeHoverEffects = $data['activeHoverEffects'];

    $triggerText = $data['triggerText'];
    $triggerIcon = $data['triggerIcon'];
    $triggerPos = $data['triggerPos'];
    $triggerBtnStyle = $data['triggerBtnClass'];
    $triggerSizeClass = $data['triggerSizeClass'];
    $triggerShape = $data['triggerShape'];
    $hideDropdownIcon = $data['hideDropdownIcon'];
@endphp

@if($viewStyle == 'dropdown')
<div id="{{ $id }}" class="dropdown">
    @if($triggerPos === 'tooltip')
    <span class="d-inline-block" data-bs-toggle="tooltip" title="{{ $triggerText }}">
    @endif
    <button class="btn {{ $triggerBtnStyle }} {{ $triggerSizeClass }} {{ $triggerShape }} {{ $hideDropdownIcon ? '' : 'dropdown-toggle' }} d-inline-flex align-items-center {{ $triggerPos === 'bottom' ? 'flex-column justify-content-center text-center lh-sm gap-1' : 'gap-2' }}"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        @if($triggerPos === 'hidden' || $triggerPos === 'tooltip')
            aria-label="{{ $triggerText }}"
        @endif
    >
        {{-- Icon First --}}
        @if(!empty($triggerIcon))
            <i class="bi {{ $triggerIcon }} {{ ($triggerPos === 'inline' || $triggerPos === 'bottom') ? '' : 'fs-5' }}"></i>
        @endif

        {{-- Text --}}
        @if(in_array($triggerPos, ['inline', 'bottom']))
            <span class="{{ $triggerPos === 'bottom' ? 'small' : '' }}">{{ $triggerText }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0">
        <div class="social-grid-layout gap-{{ $data['gap'] ?? '1' }}">
         @foreach($socials as $key => $link)
            @if($link)
                @php
                    $brandClass = ($colorStyle == 'multicolor' && isset($brandColors[$key])) ? 'text-' . $key : '';
                    $borderClass = ($colorStyle == 'multicolor' && isset($brandColors[$key])) ? 'border-' . $key : '';
                    $iconClass = $socialIcons[$key] ?? 'bi-link-45deg';
                    $tooltipAttr = ($displayStyle == 'tooltip') ? 'data-bs-toggle="tooltip"; title="{{ ucfirst($key) }}"' : '';
                @endphp
                @if($iconStyle === 'circle')
                    <a class="dropdown-item d-flex align-items-center gap-1 rounded border border-light justify-content-center {{ $displayStyle == 'icon_name' ? 'flex-column text-center p-1 h-100' : 'p-1' }} {{ $brandClass }}"
                       href="{{ $link }}"
                       target="_blank"
                       rel="noopener"
                       {!! $tooltipAttr !!}>
                        <span class="d-flex align-items-center justify-content-center border rounded-circle icon-circle-box {{ $brandClass }} {{ $borderClass }}">
                             <i class="bi {{ $iconClass }} {{ $iconSize }}"></i>
                        </span>
                        @if($displayStyle == 'icon_name')
                            <span class="small w-100 text-truncate">{{ ucfirst($key) }}</span>
                        @endif
                    </a>
                @elseif($iconStyle === 'square')
                     <a class="dropdown-item d-flex align-items-center gap-1 rounded-3 border justify-content-center {{ $displayStyle == 'icon_name' ? 'flex-column text-center p-1 h-100' : 'p-1' }} {{ $brandClass }} {{ $borderClass }}"
                       href="{{ $link }}"
                       target="_blank"
                       rel="noopener"
                       {!! $tooltipAttr !!}>
                        <i class="bi {{ $iconClass }} {{ $iconSize }}"></i>
                        @if($displayStyle == 'icon_name')
                            <span class="small w-100 text-truncate">{{ ucfirst($key) }}</span>
                        @endif
                    </a>
                @else
                    <a class="dropdown-item d-flex align-items-center gap-1 rounded border border-light justify-content-center {{ $displayStyle == 'icon_name' ? 'flex-column text-center p-1 h-100' : 'p-1' }} {{ $brandClass }}"
                       href="{{ $link }}"
                       target="_blank"
                       rel="noopener"
                       {!! $tooltipAttr !!}>
                        <i class="bi {{ $iconClass }} {{ $iconSize }}"></i>
                        @if($displayStyle == 'icon_name')
                            <span class="small w-100 text-truncate">{{ ucfirst($key) }}</span>
                        @endif
                    </a>
                @endif
            @endif
        @endforeach
        </div>
    </div>
    @if($triggerPos === 'tooltip')
    </span>
    @endif
</div>
@else
{{-- Regular View --}}
<div id="{{ $uniqueId }}" class="header-social d-flex align-items-center gap-{{ $data['gap'] ?? '2' }}">
    @foreach($socials as $key => $link)
        @if($link)
            @php
                 $brandClass = ($colorStyle == 'multicolor' && isset($brandColors[$key])) ? 'text-' . $key : '';
                 $borderClass = ($colorStyle == 'multicolor' && isset($brandColors[$key])) ? 'border-' . $key : '';
                 $tooltipAttr = ($displayStyle == 'tooltip') ? 'data-bs-toggle="tooltip"; data-bs-title="{{ ucfirst($key) }}"' : '';
                 $iconClass = $socialIcons[$key] ?? 'bi-link-45deg';
            @endphp

            @if($iconStyle === 'circle')
                <a href="{{ $link }}" {!! $tooltipAttr !!} target="_blank" rel="noopener" class="d-flex flex-column align-items-center gap-1 {{ $iconSize }} {{ $brandClass }}">
                    <span class="d-flex align-items-center justify-content-center border rounded-circle icon-circle-box {{ $brandClass }} {{ $borderClass }}"><i class="bi {{ $iconClass }}"></i></span>
                    @if($displayStyle == 'icon_name')
                        <span class="text-xsmall">{{ ucfirst($key) }}</span>
                    @endif
                </a>
            @elseif($iconStyle === 'square')
                <a href="{{ $link }}" {!! $tooltipAttr !!} target="_blank" rel="noopener" class="d-flex flex-column align-items-center px-2 py-1 rounded-3 border {{ $iconSize }} {{ $brandClass }} {{ $borderClass }}">
                    <i class="bi {{ $iconClass }}"></i>
                    @if($displayStyle == 'icon_name')
                        <span class="text-xsmall">{{ ucfirst($key) }}</span>
                    @endif
                </a>
            @else
                <a href="{{ $link }}" {!! $tooltipAttr !!} target="_blank" rel="noopener" class="text-muted hover-primary d-flex flex-column align-items-center {{ $iconSize }} {{ $brandClass }}">
                    <i class="bi {{ $iconClass }}"></i>
                    @if($displayStyle == 'icon_name')
                        <span class="text-xsmall">{{ ucfirst($key) }}</span>
                    @endif
                </a>
            @endif
        @endif
    @endforeach
</div>
@endif
