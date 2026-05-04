@php
    // Multi-Language Configuration
    $Languages = getLanguageSwiter();
    $languageCountry = [
        'bn' => 'Bangladesh',
        'en' => 'United States',
        'hi' => 'India',
        'es' => 'Spain',
        'de' => 'Dutch',
        'ar' => 'Middle East',
        'vi' => 'Vietnam',
        'fr' => 'France',
    ];

    // Multi-Currency Configuration
    $currencies = currencies();
    $currencyNames = [
        'BDT' => 'Bangladeshi Taka',
        'USD' => 'United States Dollar',
        'EUR' => 'Euro',
        'INR' => 'Indian Rupee',
        'PKR' => 'Pakistani Rupee',
        'MYR' => 'Malaysian Ringgit',
        'AED' => 'United Arab Emirates Dirham',
        'SAR' => 'Saudi Arabian Riyal',
    ];

    // Display Logic
    $triggerType = $data['trigger_type'] ?? 'both';
    $dropdownContent = $data['dropdown_content'] ?? 'respective';

    $modalsToRender = [];

    // If "both" triggers are active, we render two separate modals as per separate buttons
    if ($triggerType === 'both') {
         $modalsToRender[] = [
             'id' => 'switchLanguage',
             'show_language' => true,
             'show_currency' => false
         ];
         $modalsToRender[] = [
             'id' => 'switchCurrency',
             'show_language' => false,
             'show_currency' => true
         ];
    } else {
         // Single modal logic
         $showLang = ($dropdownContent === 'both' || $triggerType === 'language');
         $showCurr = ($dropdownContent === 'both' || $triggerType === 'currency');

         $modalsToRender[] = [
             'id' => 'switchCurrencyLanguage',
             'show_language' => $showLang,
             'show_currency' => $showCurr
         ];
    }
@endphp

@foreach($modalsToRender as $modal)
@php
    $showLanguage = $modal['show_language'];
    $showCurrency = $modal['show_currency'];
    $activeTab = ($showLanguage) ? 'language' : 'currency';
    $modalTitle = translate('Select your preferences');

    if($showLanguage && !$showCurrency) $modalTitle = translate('Select Language');
    if(!$showLanguage && $showCurrency) $modalTitle = translate('Select Currency');
@endphp

{{-- Language & Currency Selection Modal --}}
<x-modal id="{{ $modal['id'] }}"
         dialogClass="modal-md modal-dialog-centered"
         title="{{ $modalTitle }}">
   <div class="currency-language-container">
    @if($showLanguage && $showCurrency)
        <ul class="nav currency-language-nav border-bottom" id="settingsTab_{{ $modal['id'] }}" role="tablist">
            <li class="nav-product" role="presentation">
                <button class="nav-link active" id="language-tab-{{ $modal['id'] }}" data-bs-toggle="tab" data-bs-target="#language_{{ $modal['id'] }}" type="button" role="tab" aria-controls="language" aria-selected="true">{{ translate('Language') }}</button>
            </li>
            <li class="nav-product" role="presentation">
                <button class="nav-link" id="currency-tab-{{ $modal['id'] }}" data-bs-toggle="tab" data-bs-target="#currency_{{ $modal['id'] }}" type="button" role="tab" aria-controls="currency" aria-selected="false">{{ translate('Currency') }}</button>
            </li>
        </ul>
    @endif

    <div class="tab-content" id="settingsTabContent_{{ $modal['id'] }}">
        {{-- Language Tab Pane --}}
        @if($showLanguage)
        <div class="tab-pane show {{ $activeTab == 'language' ? 'active' : '' }}" id="language_{{ $modal['id'] }}" role="tabpanel" aria-labelledby="language-tab-{{ $modal['id'] }}">
            <div class="language-switcher-container mt-3">
                @foreach($Languages as $language => $name)
                    @php
                        $country = $languageCountry[strtolower($language)] ?? '';
                    @endphp
                    <a class="multi-currency-title {{ app()->getLocale() == $language ? 'active' : '' }}"
                       href="{{ route('language.switch', $language) }}">
                        {{ $name }}
                        <p class="text-muted fw-light small mb-0">
                            {{ $country }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Currency Tab Pane --}}
        @if($showCurrency)
        <div class="tab-pane {{ $activeTab == 'currency' ? 'show active' : '' }}" id="currency_{{ $modal['id'] }}" role="tabpanel" aria-labelledby="currency-tab-{{ $modal['id'] }}">
            <div class="multi-currency-container mt-3">
                @foreach ($currencies as $currency)
                    @if (array_key_exists($currency->code, $currencyNames))
                        <a href="{{ route('currency', $currency->code) }}"
                           class="multi-currency-title {{ currentCurrency()->id == $currency->id ? 'active' : '' }}">
                            {{ translate($currencyNames[$currency->code]) }}
                            <p class="text-muted fw-light small mb-0">
                                {{ $currency->code }} - {{ $currency->symbol }}
                            </p>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
</x-modal>
@endforeach
