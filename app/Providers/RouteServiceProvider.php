<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Route Service Provider
 *
 * Configures routing for the application including:
 * - Web routes (main application)
 * - Admin routes (administration panel)
 * - Payment routes (payment processing)
 * - API routes (REST API endpoints)
 *
 * Features:
 * - Rate limiting configuration
 * - URL normalization
 * - Namespace grouping for controllers
 * - Middleware application per route group
 *
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the application
     *
     * This is used by Laravel routes to make old-style routes working
     * with namespace-based controller resolution.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define route model bindings, pattern filters, and other route configuration
     *
     * Registers all application route groups:
     * - Admin panel routes with custom prefix
     * - Payment processing routes
     * - Main web application routes
     * - API routes with rate limiting
     *
     * @return void
     */
    public function boot(): void
    {
        $this->removeIndexFromUrl();
        $this->configureRateLimiting();

        $this->routes(function (): void {
            Route::group(['namespace' => $this->namespace], function (): void {
                // Admin routes - administration panel
                Route::middleware(['web', 'notInstalled'])
                    ->namespace('Admin')
                    ->prefix(adminPath())
                    ->group(base_path('routes/admin.php'));

                // Payment routes - payment processing
                Route::middleware(['web', 'notInstalled'])
                    ->group(base_path('routes/payments.php'));

                // Web routes - main application
                Route::middleware(['web', 'notInstalled'])
                    ->group(base_path('routes/web.php'));

                // API routes - REST API endpoints
                Route::prefix('api')
                    ->middleware('api')
                    ->group(base_path('routes/api.php'));
            });
        });
    }

    /**
     * Configure the rate limiters for the application
     *
     * Sets up rate limiting for API endpoints:
     * - 60 requests per minute per user/IP
     * - Authenticated users tracked by user ID
     * - Anonymous users tracked by IP address
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     *
     * Handles legacy URLs containing 'index.php' by removing it
     * and performing a 301 permanent redirect to the clean URL.
     *
     * This ensures:
     * - SEO-friendly URLs
     * - Backward compatibility with old links
     * - Proper HTTP status code (301 Moved Permanently)
     *
     * @return void
     */
    protected function removeIndexFromUrl(): void
    {
        $requestUri = request()->getRequestUri();

        if (Str::contains($requestUri, '/index.php/')) {
            $cleanUrl = str_replace('index.php/', '', $requestUri);

            if (strlen($cleanUrl) > 0) {
                header("Location: {$cleanUrl}", true, 301);
                exit;
            }
        }
    }
}



















