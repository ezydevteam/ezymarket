<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    | Supported: "sqlite", "mysql", "pgsql", "sqlsrv"
    |
    | EasyMarket uses MySQL by default for:
    | - Products, orders, users, transactions
    | - Categories, reviews, notifications
    | - All marketplace data
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        /*
        |----------------------------------------------------------------------
        | MySQL Connection (Primary Database for EasyMarket)
        |----------------------------------------------------------------------
        |
        | MySQL is the recommended database for EasyMarket marketplace.
        | This configuration handles all your marketplace data including:
        | - Products, categories, and inventory
        | - Users, sellers, and buyers
        | - Orders, transactions, and payments
        | - Reviews, ratings, and comments
        |
        | Performance optimizations:
        | - charset: utf8mb4 (supports emojis, international characters)
        | - collation: utf8mb4_unicode_ci (proper Unicode sorting)
        | - strict mode: Prevents invalid data insertion
        | - prefix_indexes: Better index performance for string columns
        |
        | SSL/TLS Support:
        | - Set MYSQL_ATTR_SSL_CA for encrypted database connections
        | - Required for: AWS RDS, Google Cloud SQL, remote databases
        |
        */
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'prefix_indexes' => true,
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', 'InnoDB'),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::ATTR_EMULATE_PREPARES => true,
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    | Redis Clients:
    | - "phpredis" : PHP extension (recommended - faster, native C)
    | - "predis"   : Pure PHP library (no extension needed, slower)
    |
    | EasyMarket Redis Usage:
    | - Caching: Product data, search results, API responses
    | - Sessions: User login sessions (faster than file/database)
    | - Queues: Background jobs (emails, notifications, reports)
    | - Broadcasting: Real-time events (WebSockets, Pusher alternative)
    |
    | Multiple databases (0-15):
    | - DB 0 (default) : General application data, queues
    | - DB 1 (cache)   : Cache data (can flush without affecting other data)
    | - DB 2 (session) : User sessions (optional)
    | - DB 3 (queue)   : Queue jobs (optional)
    |
    | Cluster mode:
    | - "redis" : Redis Cluster (multi-node setup)
    | - false   : Single Redis server (most common)
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        /*
        |----------------------------------------------------------------------
        | Default Redis Connection
        |----------------------------------------------------------------------
        |
        | Used for: General application data, queues, locks
        | Database: 0 (default)
        |
        */
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', '60'),
            'context' => [],
        ],

        /*
        |----------------------------------------------------------------------
        | Cache Redis Connection
        |----------------------------------------------------------------------
        |
        | Used for: Cache::store('redis') - Application caching
        | Database: 1 (separate from default)
        | Benefit: Can flush cache without affecting sessions/queues
        |
        */
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', '60'),
            'context' => [],
        ],

        /*
        |----------------------------------------------------------------------
        | Session Redis Connection (Optional)
        |----------------------------------------------------------------------
        |
        | Used for: User sessions (set SESSION_DRIVER=redis)
        | Database: 2 (separate from cache and default)
        | Benefit: Isolated session storage, better performance
        |
        */
        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', '60'),
            'context' => [],
        ],

        /*
        |----------------------------------------------------------------------
        | Queue Redis Connection (Optional)
        |----------------------------------------------------------------------
        |
        | Used for: Background jobs (set QUEUE_CONNECTION=redis)
        | Database: 3 (separate from cache, sessions, and default)
        | Benefit: Dedicated queue storage, better job processing
        |
        */
        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', '60'),
            'context' => [],
        ],

    ],

];



















