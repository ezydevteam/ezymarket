<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported drivers:
    | - "pusher" : Pusher Channels (3rd party WebSocket service)
    | - "ably"   : Ably Realtime (3rd party WebSocket service)
    | - "reverb" : Laravel Reverb (Laravel's native WebSocket server)
    | - "redis"  : Redis Pub/Sub (requires Redis server)
    | - "log"    : Logs broadcasts to storage/logs (development/testing)
    | - "null"   : Disables broadcasting (no-op)
    |
    | Recommendation for EasyMarket:
    | - Development: "log" (default) - Easy debugging without external services
    | - Production: "pusher" or "reverb" - Real-time notifications for users
    |
    */

    'default' => env('BROADCAST_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    | Each connection can be used for real-time features like:
    | - Live notifications (new orders, messages, comments)
    | - Real-time product updates (price changes, stock alerts)
    | - Chat functionality between buyers and sellers
    | - Live dashboard updates for admin panel
    |
    */

    'connections' => [

        /*
        |----------------------------------------------------------------------
        | Ably Configuration
        |----------------------------------------------------------------------
        |
        | Ably is a realtime messaging service with excellent global infrastructure.
        | Good for: Multi-region applications, high scalability requirements.
        |
        | Setup: Get API key from https://ably.com
        | Cost: Free tier available, scales with usage.
        |
        */
        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        /*
        |----------------------------------------------------------------------
        | Laravel Reverb Configuration (Laravel 11+ Native WebSocket Server)
        |----------------------------------------------------------------------
        |
        | Reverb is Laravel's official WebSocket server introduced in Laravel 11.
        | It's blazingly fast and requires no external services.
        |
        | Benefits:
        | - Free and open-source (no monthly fees)
        | - Native Laravel integration
        | - Easy to deploy alongside your application
        | - Perfect for self-hosted applications
        |
        | Setup: php artisan reverb:start
        | Docs: https://laravel.com/docs/11.x/reverb
        |
        */
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Pusher Channels Configuration (Recommended for Production)
        |----------------------------------------------------------------------
        |
        | Pusher is the most popular WebSocket service for Laravel applications.
        | Excellent reliability, global CDN, and easy setup.
        |
        | Benefits for EasyMarket:
        | - Easy integration with Laravel Echo (frontend)
        | - Presence channels (see who's online)
        | - Private channels (secure user notifications)
        | - Mobile SDK support (iOS/Android apps)
        |
        | Setup: Get credentials from https://pusher.com
        | Cost: Free tier: 200k messages/day, 100 concurrent connections
        |
        */
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Redis Pub/Sub Configuration
        |----------------------------------------------------------------------
        |
        | Redis can be used for broadcasting via its Pub/Sub feature.
        | Requires a separate WebSocket server like Laravel Echo Server or Soketi.
        |
        | Good for: Self-hosted solutions, already using Redis for caching.
        | Requires: Redis server + WebSocket server (Soketi recommended).
        |
        | Note: Redis connection uses the 'default' connection from config/database.php
        |
        */
        'redis' => [
            'driver' => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],

        /*
        |----------------------------------------------------------------------
        | Log Driver (Development/Testing)
        |----------------------------------------------------------------------
        |
        | Logs all broadcast events to storage/logs/laravel.log.
        | Perfect for development and testing without setting up external services.
        |
        | Use this while building features, then switch to pusher/reverb for production.
        |
        */
        'log' => [
            'driver' => 'log',
        ],

        /*
        |----------------------------------------------------------------------
        | Null Driver (Disable Broadcasting)
        |----------------------------------------------------------------------
        |
        | Completely disables broadcasting. Events marked with ShouldBroadcast
        | will be silently ignored. Useful for testing or temporarily disabling
        | real-time features.
        |
        */
        'null' => [
            'driver' => 'null',
        ],

    ],

];

















