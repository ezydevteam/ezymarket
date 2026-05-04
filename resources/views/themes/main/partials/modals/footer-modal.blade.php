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
@endphp

{{-- Language & Currency Selection Modal --}}
<x-modal id="switchCurrencyLanguage"
         dialogClass="modal-md modal-dialog-centered"
         title="{{ translate('Select your preferences') }}">
   <div class="currency-language-container">
    <ul class="nav currency-language-nav border-bottom" id="settingsTab" role="tablist">
        <li class="nav-product" role="presentation">
            <button class="nav-link active" id="language-tab" data-bs-toggle="tab" data-bs-target="#language" type="button" role="tab" aria-controls="language" aria-selected="true">{{ translate('Language') }}</button>
        </li>
        <li class="nav-product" role="presentation">
            <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab" aria-controls="currency" aria-selected="false">{{ translate('Currency') }}</button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabContent">
        {{-- Language Tab Pane --}}
        <div class="tab-pane show active" id="language" role="tabpanel" aria-labelledby="language-tab">
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

        {{-- Currency Tab Pane --}}
        <div class="tab-pane" id="currency" role="tabpanel" aria-labelledby="currency-tab">
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
    </div>
</div>
</x-modal>

<x-modal id="bugReportModal"
         dialogClass="modal-md modal-dialog-centered"
         title="{{ translate('Report any bugs or problem') }}">
    @include('themes.main.partials.report-form')
</x-modal>
