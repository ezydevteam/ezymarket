<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Authentication & Authorization Service Provider
 *
 * Handles application-wide authentication and authorization configuration:
 * - Policy registrations for models
 * - Gate definitions for custom authorization logic
 * - Authentication guard configurations
 *
 * Features:
 * - Automatic policy discovery via model conventions
 * - Custom authorization gates
 * - Policy mappings for models
 *
 * Laravel 11 Changes:
 * - Policy auto-discovery enabled by default
 * - registerPolicies() called automatically (no manual call needed)
 * - Policies registered via $policies property
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application
     *
     * Maps models to their corresponding policy classes.
     * Laravel 11 auto-discovers policies by convention:
     * - Model: App\Models\Post → Policy: App\Policies\PostPolicy
     * - Model: App\Models\User → Policy: App\Policies\UserPolicy
     *
     * Manual mappings only needed for non-standard locations or naming.
     *
     * @var array<class-string, class-string>
     *
     * @example
     * protected $policies = [
     *     Upload::class => UploadPolicy::class,
     *     Product::class => ProductPolicy::class,
     * ];
     */
    protected $policies = [];

    /**
     * Register authentication and authorization services
     *
     * Define custom gates, policies, and authorization logic here.
     * In Laravel 11, registerPolicies() is called automatically,
     * so no manual call is needed in boot().
     *
     * @return void
     *
     * @example Define custom gates:
     * Gate::define('manage-settings', function (User $user) {
     *     return $user->isAdmin();
     * });
     */
    public function boot(): void
    {
        // Policy registration happens automatically in Laravel 11
        // Add custom gates or authorization logic below if needed
    }
}



















