@php
    $id = $data['uniqueId'];
    $style = $data['style'];
    $label = $data['label'];
    $position = $data['position'];
    $iconSize = $data['iconSize'];
    $wrapperClasses = $data['wrapperClasses'];
    $textClasses = $data['textClasses'];
    $tooltipAttrs = $data['tooltipAttrs'];
@endphp

<div id="{{ $id }}" class="{{ $wrapperClasses }}">
    @if($style === 'icon')
        <div class="custom-theme-switch hover-opacity" {!! $tooltipAttrs !!}>
            <input class="d-none" type="checkbox" id="{{ $id }}-input" data-theme-action="toggle-switch">
            <label class="theme-toggle-label" role="button" for="{{ $id }}-input">
                <i class="bi bi-sun sun-icon theme-toggle-icon {{ $iconSize }}"></i>
                <i class="bi bi-moon-stars moon-icon theme-toggle-icon {{ $iconSize }}"></i>
            </label>
        </div>
        @if($position === 'inline' || $position === 'bottom')
            <label for="{{ $id }}-input" class="cursor-pointer {{ $textClasses }}" role="button">{{ $label }}</label>
        @endif

    @elseif($style === 'dropdown')
        <div class="dropdown">
            <a href="javascript:void(0)" role="button" class="d-inline-flex" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ $label }}" {!! $tooltipAttrs !!}>
                 <i class="bi bi-moon-stars theme-icon-active {{ $iconSize }}"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-theme-value="light">
                        <i class="bi bi-sun opacity-50"></i> {{ translate('Light') }}
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-theme-value="dark">
                        <i class="bi bi-moon-stars opacity-50"></i> {{ translate('Dark') }}
                    </button>
                </li>
                 <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-theme-value="auto">
                        <i class="bi bi-circle-half opacity-50"></i> {{ translate('Auto') }}
                    </button>
                </li>
            </ul>
        </div>
    @endif
</div>
