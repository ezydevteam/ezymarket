<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Define all locales your EasyMarket marketplace supports.
    | Format: 'locale_code' => ['name' => 'Display Name', 'script' => 'Script', 'native' => 'Native Name']
    |
    | Common marketplace locales:
    | 'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_US']
    | 'es' => ['name' => 'Spanish', 'script' => 'Latn', 'native' => 'Español', 'regional' => 'es_ES']
    | 'fr' => ['name' => 'French', 'script' => 'Latn', 'native' => 'Français', 'regional' => 'fr_FR']
    | 'de' => ['name' => 'German', 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE']
    | 'ar' => ['name' => 'Arabic', 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_SA', 'dir' => 'rtl']
    |
    | Add more locales as your marketplace expands to new regions.
    | Each locale should have corresponding translation files in lang/{locale}/.
    |
    */
    'supportedLocales' => [
        'en' => ['name' => 'en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Use Accept-Language Header
    |--------------------------------------------------------------------------
    |
    | Automatically detect user's preferred language from browser Accept-Language header.
    |
    | TRUE: Good for international marketplaces - auto-detects visitor language
    | FALSE: Users must explicitly choose language (more predictable, recommended for production)
    |
    | ⚠️ For EasyMarket: Keep FALSE to avoid confusion with product listings
    | If buyer changes locale mid-purchase, ensure cart/checkout language consistency.
    |
    */
    'useAcceptLanguageHeader' => env('LOCALIZATION_USE_ACCEPT_HEADER', false),

    /*
    |--------------------------------------------------------------------------
    | Hide Default Locale in URL
    |--------------------------------------------------------------------------
    |
    | Remove default locale prefix from URLs for cleaner appearance.
    |
    | TRUE: example.com/products (default locale) vs example.com/es/products
    | FALSE: example.com/en/products (explicit locale in all URLs)
    |
    | SEO Consideration: TRUE is better for default market (cleaner URLs)
    | Consistency: FALSE makes all URLs predictable (good for API/mobile apps)
    |
    | Recommended for EasyMarket: TRUE (cleaner URLs for primary language)
    |
    */
    'hideDefaultLocaleInURL' => env('LOCALIZATION_HIDE_DEFAULT', false),

    /*
    |--------------------------------------------------------------------------
    | Locales Order
    |--------------------------------------------------------------------------
    |
    | Define priority order for locale detection (if useAcceptLanguageHeader = true).
    | Empty array = use order from supportedLocales.
    |
    | Example: ['en', 'es', 'fr'] - English has highest priority
    |
    | Not needed if useAcceptLanguageHeader = false (current setup).
    |
    */
    'localesOrder' => [],

    /*
    |--------------------------------------------------------------------------
    | Locales Mapping
    |--------------------------------------------------------------------------
    |
    | Map browser locale codes to your supported locales.
    | Useful when browser sends regional variants not in your supportedLocales.
    |
    | Example:
    | 'en-US' => 'en',  // Map American English to generic English
    | 'en-GB' => 'en',  // Map British English to generic English
    | 'es-MX' => 'es',  // Map Mexican Spanish to generic Spanish
    |
    | Leave empty if you want exact locale matching only.
    | Useful for marketplaces with regional products (e.g., US vs UK digital goods).
    |
    */
    'localesMapping' => [],

    /*
    |--------------------------------------------------------------------------
    | UTF-8 Suffix
    |--------------------------------------------------------------------------
    |
    | Suffix appended to locale when setting PHP's setlocale().
    | Ensures proper character encoding for currency, dates, numbers.
    |
    | Default: '.UTF-8' (standard for most Linux systems)
    | Windows: May need '' or '.utf8' depending on system
    |
    | Important for EasyMarket:
    | - Currency formatting (e.g., $1,234.56 vs 1.234,56€)
    | - Date formatting in invoices
    | - Product price displays
    |
    | Test with: setlocale(LC_ALL, 'es_ES.UTF-8')
    |
    */
    'utf8suffix' => env('LOCALIZATION_UTF8_SUFFIX', '.UTF-8'),

    /*
    |--------------------------------------------------------------------------
    | URLs Ignored
    |--------------------------------------------------------------------------
    |
    | URL patterns that should NOT have locale prefix.
    | Useful for admin routes, APIs, webhooks that shouldn't be localized.
    |
    | Example for EasyMarket:
    | [
    |     '/admin/*',           // Admin panel always in default language
    |     '/api/*',             // API endpoints (use Accept-Language header instead)
    |     '/webhooks/*',        // Payment gateway callbacks
    |     '/editor/*',          // Editor routes
    |     '/storage/*',         // File downloads
    | ]
    |
    | Empty array = all URLs get locale prefix (except hideDefaultLocaleInURL = true).
    |
    */
    'urlsIgnored' => [],

    /*
    |--------------------------------------------------------------------------
    | HTTP Methods Ignored
    |--------------------------------------------------------------------------
    |
    | HTTP methods that should NOT trigger locale detection/switching.
    |
    | Default: ['POST', 'PUT', 'PATCH', 'DELETE']
    | Reason: These methods modify data and shouldn't change language mid-request.
    |
    | GET: Always processes locale (safe, idempotent)
    | POST/PUT/PATCH/DELETE: Ignore locale switching (prevent CSRF issues)
    |
    | For EasyMarket: Keep defaults to ensure:
    | - Product creation doesn't switch language
    | - Order placement remains in chosen language
    | - Payment processing stays consistent
    |
    */
    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];



















