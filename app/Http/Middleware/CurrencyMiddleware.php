<?php

namespace App\Http\Middleware;

use App\Services\GeolocationService;
use App\Models\Financial\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CurrencyMiddleware
{
    private const COOKIE_NAME = 'currency';
    private const COOKIE_LIFETIME = 43200; // 30 days in minutes (60 * 24 * 30)

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSetCurrency($request)) {
            $currencyCode = $this->getCurrencyCode($request);

            if ($currencyCode) {
                $this->setCurrency($currencyCode);
            } else {
                // Set default currency if no valid currency found
                $this->setDefaultCurrency();
            }
        }

        return $next($request);
    }
    /**
     * Determine if currency should be set
     */
    private function shouldSetCurrency(Request $request): bool
    {
        return config('system.install.complete')
            && !$this->isAdminPath($request);
    }

    /**
     * Check if current path is admin
     */
    private function isAdminPath(Request $request): bool
    {
        return $request->segment(1) === adminPath();
    }

    /**
     * Get currency code from cookie or IP lookup
     */
    private function getCurrencyCode(Request $request): ?string
    {
        if ($request->hasCookie(self::COOKIE_NAME)) {
            return $this->getCurrencyFromCookie($request);
        }

        return $this->getCurrencyFromIpLookup();
    }

    /**
     * Get currency from cookie if valid
     */
    private function getCurrencyFromCookie(Request $request): ?string
    {
        $currencyCode = $request->cookie(self::COOKIE_NAME);

        if ($currencyCode && Currency::where('code', $currencyCode)->exists()) {
            return $currencyCode;
        }

        return null;
    }

    /**
     * Get currency from IP lookup
     */
    private function getCurrencyFromIpLookup(): ?string
    {
        $clientCurrency = app(GeolocationService::class)->lookup(getIp())->currency;

        if ($clientCurrency && Currency::where('code', $clientCurrency)->exists()) {
            Cookie::queue(self::COOKIE_NAME, $clientCurrency, self::COOKIE_LIFETIME);
            return $clientCurrency;
        }

        return null;
    }

    /**
     * Set currency in application config
     */
    private function setCurrency(string $currencyCode): void
    {
        config(['app.currency' => $currencyCode]);
    }

    /**
     * Set default currency from database
     */
    private function setDefaultCurrency(): void
    {
        $defaultCurrency = currency();

        config(['app.currency' => $defaultCurrency->code]);
    }
}
