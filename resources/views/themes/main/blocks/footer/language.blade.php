@php
    $uniqueId = $data['uniqueId'];
    $style = $data['style'];
    $customIcon = $data['customIcon'];
    $triggers = $data['triggers'];
    $oneMoreTrigger = count($triggers) > 1;

    $languages = $data['languages'];
    $currencies = $data['currencies'];
    $currentLangCode = $data['currentLangCode'];
    $currentCurrCode = $data['currentCurrCode'];
@endphp

<div class="footer-language {{ $oneMoreTrigger ? 'd-flex align-items-center ' . ($data['blockGap'] ?? 'gap-2') : '' }}">
@foreach($triggers as $trigger)
    <div class="{{ $trigger['wrapperClass'] }}" {!! $trigger['wrapperAttrs'] !!}>
        <button class="{{ $trigger['btnClass'] }} {{ $trigger['contentLayout'] }}"
                type="button"
                {!! $trigger['btnAttrs'] !!}>

            @if($customIcon)
                <i class="{{ $customIcon }}"></i>
            @endif

            @if($trigger['showLabel'])
                <span class="{{ $trigger['labelPosition'] === 'bottom' ? 'small lh-1' : 'ms-1' }}">
                    {{ $trigger['label'] }}
                </span>
            @endif
        </button>

        {{-- Dropdown Content --}}
        @if($style === 'dropdown')
            <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0 overflow-y-auto" style="min-width: 240px; max-height: 400px;">

                {{-- Language Section --}}
                @if($trigger['content'] === 'both' || $trigger['content'] === 'language')
                    <h6 class="dropdown-header px-0 text-uppercase small text-muted fw-bold mb-2">{{ translate('Language') }}</h6>
                    <div class="d-grid gap-2 mb-3">
                        @foreach($languages as $code => $name)
                            <a href="{{ route('language.switch', $code) }}"
                               class="btn btn-sm d-flex justify-content-between align-items-center {{ $currentLangCode === $code ? 'btn-primary' : 'btn-light' }}">
                                <span>{{ $name }}</span>
                                @if($currentLangCode === $code)
                                    <i class="bi bi-check-lg"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($trigger['content'] === 'both')
                    <hr class="dropdown-divider my-3">
                @endif

                {{-- Currency Section --}}
                @if($trigger['content'] === 'both' || $trigger['content'] === 'currency')
                    <h6 class="dropdown-header px-0 text-uppercase small text-muted fw-bold mb-2">{{ translate('Currency') }}</h6>
                    <div class="d-grid gap-2">
                        @foreach($currencies as $currency)
                            <a href="{{ route('currency', $currency->code) }}"
                               class="btn btn-sm d-flex justify-content-between align-items-center {{ $currentCurrCode === $currency->code ? 'btn-primary' : 'btn-light' }}">
                                <span>{{ $currency->symbol }} {{ $currency->code }}</span>
                                @if($currentCurrCode === $currency->code)
                                    <i class="bi bi-check-lg"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endforeach

</div>

@if($style === 'modal')
    @once
        @push('footer_content')
            @themeInclude('blocks.header.partials.language-currency')
        @endpush
    @endonce
@endif
