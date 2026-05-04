<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Google 2FA (Two-Factor Authentication)
    |--------------------------------------------------------------------------
    |
    | Controls whether 2FA is enabled for your EasyMarket application.
    |
    | Security recommendation for EasyMarket:
    | - Admin accounts: ALWAYS enabled (protect seller funds, user data)
    | - Seller accounts: STRONGLY recommended (protect earnings, products)
    | - Buyer accounts: Optional (user preference for account security)
    |
    | Set to false to completely disable 2FA feature.
    |
    */
    'enabled' => env('OTP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | OTP Lifetime (Session Timeout)
    |--------------------------------------------------------------------------
    |
    | How long (in minutes) a 2FA verification remains valid before requiring
    | the user to enter another OTP code.
    |
    | Values:
    | - 0       : Eternal (verify once per login, never expire during session)
    | - 30      : Re-verify every 30 minutes (high security)
    | - 1440    : Re-verify once per day (24 hours)
    |
    | Recommendation for EasyMarket:
    | - Admin panel: 60 minutes (balance security and convenience)
    | - Sellers: 0 (eternal, verify once per login)
    | - Financial transactions: Always verify (handled separately in code)
    |
    */
    'lifetime' => env('OTP_LIFETIME', 0), // 0 = eternal (verify once per login)

    /*
    |--------------------------------------------------------------------------
    | Keep Alive (Renew on Activity)
    |--------------------------------------------------------------------------
    |
    | If true, the OTP lifetime is renewed with every request.
    | If false, OTP expires after the lifetime duration regardless of activity.
    |
    | Example with lifetime=60 and keep_alive=true:
    | - User makes requests continuously → 2FA stays valid
    | - User idle for 60 minutes → 2FA expires, requires re-verification
    |
    | Recommendation: true (better user experience, still secure)
    |
    */
    'keep_alive' => env('OTP_KEEP_ALIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Auth Container Binding
    |--------------------------------------------------------------------------
    |
    | Laravel's authentication container name.
    | Default 'auth' works for standard Laravel applications.
    |
    */
    'auth' => 'auth',

    /*
    |--------------------------------------------------------------------------
    | Guard Configuration
    |--------------------------------------------------------------------------
    |
    | Specify which authentication guard to use for 2FA.
    | Leave empty to use the default guard.
    |
    | EasyMarket has multiple guards:
    | - '' (default) : Regular users
    | - 'admin'      : Admin users
    | - 'editor'     : Editor users
    |
    | This should remain empty to work across all guards.
    |
    */
    'guard' => env('OTP_GUARD', ''),

    /*
    |--------------------------------------------------------------------------
    | Session Variable Name
    |--------------------------------------------------------------------------
    |
    | Session variable name to store 2FA verification status.
    | Change only if you have conflicts with other packages.
    |
    */
    'session_var' => 'google2fa',

    /*
    |--------------------------------------------------------------------------
    | OTP Input Field Name
    |--------------------------------------------------------------------------
    |
    | The name of the input field in your 2FA verification form.
    | Must match: <input name="one_time_password">
    |
    */
    'otp_input' => 'one_time_password',

    /*
    |--------------------------------------------------------------------------
    | OTP Verification Window
    |--------------------------------------------------------------------------
    |
    | Time window (in 30-second intervals) to accept OTP codes.
    |
    | Values:
    | - 0 : Only accept current code (strictest, may have sync issues)
    | - 1 : Accept previous, current, and next code (recommended)
    | - 2 : Accept 2 codes before and after (more lenient)
    |
    | Recommendation: 1 (balances security with clock sync tolerance)
    |
    | Why needed: Handles slight time differences between server and
    | authenticator app (Google Authenticator, Authy, etc.)
    |
    */
    'window' => env('OTP_WINDOW', 1),

    /*
    |--------------------------------------------------------------------------
    | Forbid Reusing Old Passwords
    |--------------------------------------------------------------------------
    |
    | Prevents users from reusing previously used OTP codes.
    | Requires database storage of used OTPs.
    |
    | Security consideration:
    | - true  : Higher security (prevents replay attacks)
    | - false : Lower security but simpler (OTPs expire after 30s anyway)
    |
    | Recommendation for EasyMarket: false
    | - OTP codes naturally expire every 30 seconds
    | - Additional database queries for minimal security gain
    | - Most marketplaces don't enable this
    |
    */
    'forbid_old_passwords' => env('OTP_FORBID_OLD_PASSWORDS', false),

    /*
    |--------------------------------------------------------------------------
    | OTP Secret Database Column
    |--------------------------------------------------------------------------
    |
    | Database column name where the 2FA secret key is stored.
    | This column should exist in your users/admins/editors tables.
    |
    | EasyMarket database tables:
    | - users table: google2fa_secret column
    | - admins table: google2fa_secret column (if enabled for admins)
    | - editors table: google2fa_secret column (if enabled for editors)
    |
    */
    'otp_secret_column' => 'google2fa_secret',

    /*
    |--------------------------------------------------------------------------
    | 2FA Verification View
    |--------------------------------------------------------------------------
    |
    | The Blade view to display the OTP verification form.
    | Located in: resources/views/google2fa/index.blade.php
    |
    | Customize this view to match your EasyMarket theme.
    |
    */
    'view' => 'google2fa.index',

    /*
    |--------------------------------------------------------------------------
    | OTP Error Messages
    |--------------------------------------------------------------------------
    |
    | Customizable error messages for 2FA verification failures.
    | These can be translated using Laravel's localization system.
    |
    | For multilingual support, use: __('google2fa.wrong_otp')
    |
    */
    'error_messages' => [
        'wrong_otp'       => "The 'One Time Password' typed was wrong.",
        'cannot_be_empty' => 'One Time Password cannot be empty.',
        'unknown'         => 'An unknown error has occurred. Please try again.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Throw Exceptions
    |--------------------------------------------------------------------------
    |
    | Controls error handling behavior:
    | - true  : Throws exceptions (good for debugging, catch in try-catch)
    | - false : Fires events only (silent failure, handle via event listeners)
    |
    | Recommendation for EasyMarket:
    | - Development: true (easier debugging)
    | - Production: true (proper error handling in controllers)
    |
    */
    'throw_exceptions' => env('OTP_THROW_EXCEPTION', true),

    /*
    |--------------------------------------------------------------------------
    | QR Code Image Backend
    |--------------------------------------------------------------------------
    |
    | Image format for generating QR codes during 2FA setup.
    |
    | Options:
    | - QRCODE_IMAGE_BACKEND_SVG        : SVG format (recommended - scalable, small file size)
    | - QRCODE_IMAGE_BACKEND_IMAGEMAGICK: PNG via ImageMagick (requires extension)
    | - QRCODE_IMAGE_BACKEND_EPS        : EPS format (for print)
    |
    | Recommendation for EasyMarket: SVG
    | - Works in all modern browsers
    | - No external dependencies
    | - Scales perfectly on all devices (mobile, desktop)
    | - Smaller bandwidth usage
    |
    */
    'qrcode_image_backend' => env(
        'OTP_QRCODE_BACKEND',
        \PragmaRX\Google2FALaravel\Support\Constants::QRCODE_IMAGE_BACKEND_SVG
    ),

];



















