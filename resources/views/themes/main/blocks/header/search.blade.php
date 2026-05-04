@php
    $uniqueId = $data['uniqueId'];
    $formId = $data['formId'];
    $style = $data['style'];
    $wrapperClasses = $data['wrapperClasses'];
    $triggerMode = $data['triggerMode'];
    $triggerPos = $data['triggerPos'];
    $triggerClass = $data['triggerClass'];
    $labelClass = $data['labelClass'];
    $triggerText = $data['triggerText'];
    $triggerIconSize = $data['triggerIconSize'];

    // Partial Data
    $partialData = [
        'formId' => $formId,
        'uniqueId' => $uniqueId,
        'btnPosition' => $data['btnPosition'],
        'btnIcon' => $data['btnIcon'],
        'showBtnText' => $data['showBtnText'],
        'placeholder' => $data['placeholder'],
        'liveSearch' => $data['liveSearch'],
        'style' => $style,
    ];
@endphp

<div id="{{ $uniqueId }}" class="{{ $wrapperClasses }}">

    {{-- STANDARD --}}
    @if($style === 'standard')
        <div class="live-search-component">
            @themeInclude('blocks.header.partials.search-form', $partialData)
        </div>

    {{-- MODAL --}}
    @elseif($style === 'modal')
        <a href="javascript:void(0)"
           role="button"
           id="searchTrigger-{{ $uniqueId }}"
           class="{{ $triggerClass }}"
           data-bs-toggle="modal"
           data-bs-target="#searchModal-{{ $uniqueId }}">
            {{-- Trigger Content --}}
            @if(($triggerMode === 'icon' || $triggerMode === 'icon_text') && ($triggerPos === 'right' || $triggerPos === 'bottom')) <i class="bi bi-search {{ $triggerIconSize }}"></i> @endif
            @if($triggerMode === 'text' || $triggerMode === 'icon_text') <span class="{{ $labelClass }}">{{ $triggerText }}</span> @endif
            @if(($triggerMode === 'icon' || $triggerMode === 'icon_text') && $triggerPos === 'left') <i class="bi bi-search {{ $triggerIconSize }}"></i> @endif
        </a>

        @push('footer_content')
        <x-modal id="searchModal-{{ $uniqueId }}"
            title="Search"
            :header="false"
            class="header-search-modal"
            centered="true"
            :data-unique-id="$uniqueId">
            <div class="p-4 live-search-component">
                {{-- Override size for Modal --}}
                @themeInclude('blocks.header.partials.search-form', array_merge($partialData, ['size' => 'input-group-lg']))
            </div>
        </x-modal>
        @endpush

    {{-- EXPANDABLE / FULL WIDTH --}}
    @elseif($style === 'expandable' || $style === 'full_width')
         <a href="javascript:void(0)"
            role="button"
            id="searchTrigger-{{ $uniqueId }}"
            class="{{ $triggerClass }}"
            data-bs-toggle="collapse"
            data-bs-target="#searchCollapse-{{ $uniqueId }}"
            aria-expanded="false">
            @if(($triggerMode === 'icon' || $triggerMode === 'icon_text') && ($triggerPos === 'right' || $triggerPos === 'bottom')) <i class="bi bi-search {{ $triggerIconSize }}"></i> @endif
            @if($triggerMode === 'text' || $triggerMode === 'icon_text') <span class="{{ $labelClass }}">{{ $triggerText }}</span> @endif
            @if(($triggerMode === 'icon' || $triggerMode === 'icon_text') && $triggerPos === 'left') <i class="bi bi-search {{ $triggerIconSize }}"></i> @endif
        </a>

        @if($style === 'full_width')
            @push('footer_content')
                {{-- Backdrop --}}
                <div id="searchBackdrop-{{ $uniqueId }}"
                    class="position-fixed top-0 start-0 w-100 h-100 d-none"
                    style="background: rgba(0,0,0,0.5); z-index: 1040;">
                </div>

                {{-- Full Width Search Container --}}
                <div class="collapse header-search-collapse position-fixed top-0 start-0 w-100"
                     id="searchCollapse-{{ $uniqueId }}"
                     data-unique-id="{{ $uniqueId }}"
                     style="z-index: 1050;">

                    <div class="bg-white shadow-lg p-3 live-search-component">
                         @themeInclude('blocks.header.partials.search-form', array_merge($partialData, ['showClose' => true]))
                    </div>
                </div>
            @endpush
        @else
             {{-- Expandable Search Container (In-place) --}}
             <div class="collapse header-search-collapse collapse-horizontal position-absolute top-100 end-0 mt-2"
                 id="searchCollapse-{{ $uniqueId }}"
                 data-unique-id="{{ $uniqueId }}"
                 style="z-index: 1050;">

                <div class="bg-white shadow-lg p-3 rounded rounded-3 live-search-component">
                     @themeInclude('blocks.header.partials.search-form', array_merge($partialData, ['showClose' => false]))
                </div>
            </div>
        @endif
    @endif
</div>
