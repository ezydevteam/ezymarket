<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Middleware, Exceptions};
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        then: function () {
            // Load additional route files with proper prefixes
            Route::middleware('web')
                ->prefix(config('system.admin.path', 'admin'))
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/payments.php'));

            // Note: Breadcrumbs are loaded via diglactic/laravel-breadcrumbs service provider
            // Not as route files to prevent duplicate registration
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Essential Global Middleware
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);

        // Official Laravel 11 configuration for Encrypted Cookies
        $middleware->encryptCookies(except: [
            'announce_close',
            'gdpr_cookie',
            '_ref',
        ]);

        // Official Laravel 11 configuration for CSRF Protection
        $middleware->validateCsrfTokens(except: [
            'payments/webhooks/*',
            'payments/notifications/*',
            'payments/ipn/iyzico',
        ]);

        // Add custom middlewares specifically up to the 'web' group
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SwitchLanguageDirection::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CurrencyMiddleware::class,
            \App\Http\Middleware\TrackUserActivity::class,
            \App\Http\Middleware\UserLastActive::class,
            \App\Http\Middleware\TrackPageView::class,
        ]);

        // Add custom middlewares specifically to the 'api' group
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\DisableApiDuringDemoMode::class,
            \App\Http\Middleware\PreventApiDirectAccess::class,
        ]);

        // Route middleware aliases
        $middleware->alias([
            'demo' => \App\Http\Middleware\DemoMode::class,
            'addon.active' => \App\Http\Middleware\IsAddonActive::class,
            'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
            'mail' => \App\Http\Middleware\MailMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'oauth.complete' => \App\Http\Middleware\OAuthDataComplete::class,
            'seller' => \App\Http\Middleware\IsSeller::class,
            'not.seller' => \App\Http\Middleware\NotSeller::class,
            'seller.disable' => \App\Http\Middleware\Actions\BecomeSellerDisable::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'license' => \App\Http\Middleware\LicenseMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'ajax.only' => \App\Http\Middleware\AjaxOnlyMiddleware::class,
            'registration.disable' => \App\Http\Middleware\Actions\RegistrationDisable::class,
            'blog.disable' => \App\Http\Middleware\Actions\BlogDisable::class,
            'api.disable' => \App\Http\Middleware\Actions\ApiDisable::class,
            'tickets.disable' => \App\Http\Middleware\Actions\TicketsDisable::class,
            'refunds.disable' => \App\Http\Middleware\Actions\RefundsDisable::class,
            'contact.disable' => \App\Http\Middleware\Actions\ContactDisable::class,
            'referral.disable' => \App\Http\Middleware\Actions\ReferralDisable::class,
            'id-verification.disable' => \App\Http\Middleware\Actions\IdVerificationDisable::class,
            'id-verification.required' => \App\Http\Middleware\IdVerificationRequired::class,
            'discount.disable' => \App\Http\Middleware\Actions\DiscountDisable::class,
            'product_reviews.disable' => \App\Http\Middleware\Actions\ProductReviewsDisable::class,
            'product_comments.disable' => \App\Http\Middleware\Actions\ProductCommentsDisable::class,
            'product_changelogs.disable' => \App\Http\Middleware\Actions\ProductChangeLogsDisable::class,
            'product_support.disable' => \App\Http\Middleware\Actions\ProductSupportDisable::class,
            'free_products_login' => \App\Http\Middleware\Actions\FreeproductsRequireLogin::class,
            'buy_now.disable' => \App\Http\Middleware\Actions\BuyNowDisable::class,
            'deposit.disable' => \App\Http\Middleware\Actions\DepositDisable::class,
            'premium.disable' => \App\Http\Middleware\Actions\PremiumDisable::class,
            'user.status' => \App\Http\Middleware\UserStatusCheck::class,
            'chatbox.disable' => \App\Http\Middleware\Actions\ChatboxDisable::class,
            '2fa.verify' => \App\Http\Middleware\TwoFactorVerify::class,
            'referral' => \App\Http\Middleware\ReferralMiddleware::class,
            'lowercase' => \App\Http\Middleware\ConvertUrlParametersToLowerCase::class,
            'product.views' => \App\Http\Middleware\productViews::class,
            'maintenance' => \App\Http\Middleware\MaintenanceMode::class,
            'trustip' => \App\Http\Middleware\Trustip::class,
            'draft.owner' => \App\Http\Middleware\CheckDraftOwnership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle exceptions
    })
    ->create();
