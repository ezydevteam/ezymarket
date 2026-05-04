<form action="{{ route('products.search') }}" method="GET" class="w-100 position-relative header-search-form" id="{{ $formId }}">
    <div class="input-group {{ $size ?? '' }}">
        {{-- Left Button --}}
        @if($btnPosition === 'left')
        <button type="submit" class="btn btn-primary search-btn">
            <i class="bi {{ $btnIcon }}"></i>
            @if($showBtnText) <span class="ms-1">{{ translate('Search') }}</span> @endif
        </button>
        @endif

        {{-- Input --}}
        <input type="text"
               name="query"
               class="form-control search-input {{ $liveSearch ? 'live-search-input' : '' }}"
               placeholder="{{ $placeholder }}"
               value="{{ request()->input('query') }}"
               autocomplete="off" required>

        {{-- Clear Button --}}
        @if($liveSearch)
            @php $clearBtnPos = $btnPosition === 'right' ? ($showBtnText ? '136px' : '60px') : '10px'; @endphp
            <button type="button" class="btn position-absolute top-50 translate-middle-y clear-search-button text-muted border-0 bg-transparent p-0 d-none"
                    style="right: {{ $clearBtnPos }}; z-index: 5;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        @endif

        {{-- Right Button --}}
        @if($btnPosition === 'right')
        <button type="submit" class="btn btn-primary search-btn">
            <i class="bi {{ $btnIcon }}"></i>
            @if($showBtnText) <span class="ms-1">{{ translate('Search') }}</span> @endif
        </button>
        @endif
    </div>

    {{-- Live Search Results --}}
    @if($liveSearch)
        <div class="live-search-results dropdown-menu w-100 shadow mt-1 border-0 rounded-bottom d-none"></div>
        <div class="search-backdrop d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 990; background: rgba(0,0,0,0.1);"></div>
    @endif
</form>
