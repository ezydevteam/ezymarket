<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    | Supported drivers:
    | - "file"      : File-based caching (default, good for small/medium apps)
    | - "redis"     : Redis caching (best performance for production)
    | - "memcached" : Memcached caching (alternative to Redis)
    | - "database"  : Database caching (when file system isn't shared)
    | - "array"     : In-memory caching (testing only, doesn't persist)
    | - "apc"       : APC/APCu caching (PHP extension required)
    | - "dynamodb"  : AWS DynamoDB caching (cloud deployments)
    | - "octane"    : Laravel Octane caching (high-performance apps)
    | - "null"      : Disables caching (debugging)
    |
    | Recommendation :
    | - Development: "file" (simple, no extra setup)
    | - Production: "redis" (fast, shared across servers, best for marketplace)
    |
    | Cache is used for:
    | - Product listings, categories, search results
    | - User sessions, authentication tokens
    | - API rate limiting, query results
    | - Configuration, routes, views
    |
    */

    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | EasyMarket Usage Examples:
    | - Products: Cache::store('redis')->get('products')
    | - Sessions: Automatic via SESSION_DRIVER=redis
    | - Queries: Cache::store('database')->remember('query', 3600, fn() => ...)
    |
    */

    'stores' => [

        /*
        |----------------------------------------------------------------------
        | APC/APCu Cache Store
        |----------------------------------------------------------------------
        |
        | APCu (Alternative PHP Cache) is a PHP extension for in-memory caching.
        | Very fast but limited to single server (no shared caching).
        |
        | Requirements: APCu PHP extension installed
        | Use case: Single-server deployments, opcode caching
        | Not recommended for: Multi-server load-balanced setups
        |
        */
        'apc' => [
            'driver' => 'apc',
        ],

        /*
        |----------------------------------------------------------------------
        | Array Cache Store (Testing Only)
        |----------------------------------------------------------------------
        |
        | In-memory cache that exists only for the current request.
        | Data is lost after the request completes.
        |
        | Use case: Testing, preventing cache pollution during tests
        | Never use in production!
        |
        */
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Database Cache Store
        |----------------------------------------------------------------------
        |
        | Stores cache in your database using the 'cache' table.
        | Good when file system isn't shared across servers.
        |
        | Setup: php artisan cache:table && php artisan migrate
        |
        | Pros:
        | - Works across multiple servers (shared database)
        | - No additional services needed (Redis/Memcached)
        | - Good for small to medium traffic
        |
        | Cons:
        | - Slower than Redis/Memcached
        | - Adds load to database server
        |
        | Use for: Multi-server apps without Redis/Memcached
        |
        */
        'database' => [
            'driver' => 'database',
            'table' => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => env('CACHE_DATABASE_CONNECTION'),
            'lock_connection' => env('CACHE_DATABASE_LOCK_CONNECTION'),
        ],

        /*
        |----------------------------------------------------------------------
        | File Cache Store (Default)
        |----------------------------------------------------------------------
        |
        | Stores cache as files in storage/framework/cache/data directory.
        | Simple, no external services needed.
        |
        | Pros:
        | - Zero configuration
        | - Works everywhere
        | - Good for development
        |
        | Cons:
        | - Slower than Redis/Memcached
        | - Not shared across servers (unless using shared filesystem)
        | - Can cause I/O bottlenecks under high load
        |
        | Best for: Development, single-server staging, low-traffic sites
        |
        */
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => env('CACHE_FILE_LOCK_PATH', storage_path('framework/cache/data')),
        ],

        /*
        |----------------------------------------------------------------------
        | Memcached Cache Store
        |----------------------------------------------------------------------
        |
        | Memcached is a high-performance distributed memory caching system.
        | Alternative to Redis, widely supported.
        |
        | Requirements: Memcached server + PHP memcached extension
        | Setup: brew install memcached (Mac) or apt install memcached (Linux)
        |
        | Pros:
        | - Very fast (in-memory)
        | - Multi-server support
        | - Battle-tested, mature technology
        |
        | Cons:
        | - No data persistence (restarts = lost cache)
        | - No native pub/sub (unlike Redis)
        |
        | Use for: High-traffic applications, alternative to Redis
        |
        */
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Redis Cache Store (RECOMMENDED FOR PRODUCTION)
        |----------------------------------------------------------------------
        |
        | Redis is the best choice for production Laravel applications.
        | Blazing fast, persistent, supports pub/sub, queues, sessions.
        |
        | Benefits for EasyMarket:
        | - Cache product listings, categories, search results
        | - Store user sessions (set SESSION_DRIVER=redis)
        | - Handle queued jobs (set QUEUE_CONNECTION=redis)
        | - Broadcasting/real-time features
        | - Rate limiting API requests
        |
        | Setup:
        | 1. Install Redis: brew install redis (Mac) or docker run redis
        | 2. Update .env: CACHE_DRIVER=redis
        | 3. Configure: config/database.php (redis connections)
        |
        | Multiple connections:
        | - 'default' : General application data
        | - 'cache'   : Dedicated caching (this connection)
        | - 'session' : User sessions
        |
        | Recommendation: Use Redis for cache, sessions, and queues in production
        |
        */
        'redis' => [
            'driver' => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
            'lock_connection' => env('CACHE_REDIS_LOCK_CONNECTION', 'default'),
        ],

        /*
        |----------------------------------------------------------------------
        | AWS DynamoDB Cache Store
        |----------------------------------------------------------------------
        |
        | DynamoDB is AWS's fully managed NoSQL database service.
        | Good for serverless or AWS-native deployments.
        |
        | Requirements: AWS account, DynamoDB table, AWS SDK
        | Setup: Create table in AWS console, add credentials to .env
        |
        | Pros:
        | - Fully managed (no server maintenance)
        | - Auto-scaling
        | - Global replication available
        |
        | Cons:
        | - Costs money (pay per request)
        | - Higher latency than Redis/Memcached
        | - AWS vendor lock-in
        |
        | Use for: AWS Lambda, serverless apps, global deployments
        |
        */
        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        /*
        |----------------------------------------------------------------------
        | Laravel Octane Cache Store
        |----------------------------------------------------------------------
        |
        | Octane is Laravel's high-performance application server.
        | Uses in-memory caching via Swoole or RoadRunner.
        |
        | Requirements: Laravel Octane installed
        | Setup: composer require laravel/octane
        |
        | Pros:
        | - Extreme performance (persistent in-memory)
        | - No external services needed
        | - Perfect for high-traffic APIs
        |
        | Cons:
        | - Requires Octane setup
        | - More complex deployment
        | - Single-server limitation
        |
        | Use for: High-performance APIs, real-time applications
        |
        */
        'octane' => [
            'driver' => 'octane',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM-based store such as APC, Redis, or Memcached,
    | there might be other applications utilizing the same cache server.
    | So, we'll specify a value to get prefixed to all our keys to avoid
    | collisions with other applications.
    |
    | This is especially important for:
    | - Shared Redis/Memcached servers (multiple apps on same server)
    | - Multi-tenant environments
    | - Staging vs Production on same infrastructure
    |
    | Default: Uses your APP_NAME as prefix (e.g., "easymarket_cache_")
    | Custom: Set CACHE_PREFIX in .env for different environments
    |
    | Examples:
    | - Production: "easymarket_prod_cache"
    | - Staging: "easymarket_staging_cache"
    | - Testing: "easymarket_test_cache"
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),

];



















