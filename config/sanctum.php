<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Domains/hosts that will receive stateful API authentication cookies.
    | For EasyMarket, this enables session-based authentication for:
    | - Frontend SPA (if using Vue/React)
    | - Mobile app WebViews
    | - Trusted third-party integrations
    |
    | Default includes:
    | - localhost, localhost:3000 (local development)
    | - 127.0.0.1, 127.0.0.1:8000 (local testing)
    | - ::1 (IPv6 localhost)
    | - Your APP_URL domain (automatically added)
    |
    | For production, add:
    | SANCTUM_STATEFUL_DOMAINS="easymarket.com,www.easymarket.com,admin.easymarket.com"
    |
    | ⚠️ Security: Only add domains you control and trust completely.
    | These domains can make authenticated API requests on behalf of users.
    |
    | 💡 For mobile apps: Don't add app:// schemes here (use token auth instead)
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | Authentication guards that Sanctum will authenticate through when
    | checking for API token authentication.
    |
    | Default: ['web'] - Uses session-based authentication
    |
    | For EasyMarket multi-auth setup, you might use:
    | - 'web' for buyers/sellers
    | - 'admin' for admin panel API
    | - 'editor' for editor dashboard API
    |
    | Example: ['web', 'admin', 'editor']
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Minutes until an issued API token expires (personal access tokens).
    | NULL = tokens never expire (not recommended for production)
    |
    | Recommended values for EasyMarket:
    | - Buyers/Sellers: 60-120 minutes (1-2 hours for shopping sessions)
    | - Mobile apps: 10080 minutes (7 days for persistent login)
    | - Admin API: 30 minutes (shorter for security)
    | - Webhooks: NULL or very long (if using token-based webhooks)
    |
    | ⚠️ Production Recommendation: Set to 120 (2 hours)
    | This protects against token theft while maintaining good UX.
    |
    | 💡 Note: This does NOT affect first-party session cookies (web login)
    | Session expiration is controlled by config/session.php
    |
    | To enable: SANCTUM_EXPIRATION=120
    |
    */

    'expiration' => env('SANCTUM_EXPIRATION', null),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Custom prefix for personal access tokens in the database.
    | Helps identify token type in logs and debugging.
    |
    | Default: Empty string (no prefix)
    | Example: 'easymarket_' results in tokens like "easymarket_1|abc123..."
    |
    | Useful for:
    | - Multi-tenant systems (different prefixes per tenant)
    | - Token type identification (buyer_, seller_, admin_)
    | - Logging and auditing
    |
    | Leave empty unless you have specific token identification needs.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware that Sanctum uses when authenticating first-party SPAs.
    | These middleware handle CSRF protection and cookie encryption.
    |
    | Default middleware:
    | - verify_csrf_token: Protects against Cross-Site Request Forgery
    | - encrypt_cookies: Encrypts authentication cookies
    |
    | ⚠️ Do NOT remove these unless you understand the security implications!
    |
    | For EasyMarket:
    | These protect buyer/seller accounts during checkout and transactions.
    | CSRF tokens prevent malicious sites from making unauthorized purchases.
    |
    | You can add custom middleware here if needed:
    | 'custom_middleware' => App\Http\Middleware\CustomMiddleware::class,
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];



















