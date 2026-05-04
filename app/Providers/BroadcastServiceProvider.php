<?php

namespace App\Providers;

use Ably\AblyRest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Broadcast Service Provider
 *
 * Configures real-time broadcasting services for the application:
 * - WebSocket connections via Ably
 * - Private/presence channel authorization
 * - Broadcasting route registration
 *
 * Features:
 * - Ably REST client singleton registration
 * - Channel authorization routes
 * - Real-time event broadcasting
 *
 * Ably Integration:
 * - Used for real-time notifications
 * - Private channel authentication
 * - Presence channels for online users
 *
 * @package App\Providers
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register application broadcast services
     *
     * Registers the Ably REST client as a singleton in the service container.
     * The client is used for server-side broadcasting operations and token generation.
     *
     * Only registers if ABLY_KEY is configured in the environment.
     *
     * Configuration:
     * - ABLY_KEY: API key from config/services.php
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('ably', function (Application $app): ?AblyRest {
            $ablyKey = $app['config']->get('services.ably.key');

            // Only instantiate if key is configured
            if (empty($ablyKey)) {
                return null;
            }

            return new AblyRest($ablyKey);
        });
    }

    /**
     * Bootstrap broadcast services
     *
     * Sets up broadcasting functionality:
     * 1. Registers broadcast authentication routes
     * 2. Loads channel authorization logic from routes/channels.php
     *
     * The routes allow clients to authenticate for private/presence channels.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register broadcast authentication routes (/broadcasting/auth)
        Broadcast::routes();

        // Load channel authorization definitions
        require base_path('routes/channels.php');
    }
}



















