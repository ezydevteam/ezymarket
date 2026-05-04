<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported drivers:
    | - "bcrypt"   : BCrypt algorithm (default, widely supported, good balance)
    | - "argon"    : Argon2i algorithm (more secure, requires PHP 7.2+)
    | - "argon2id" : Argon2id algorithm (most secure, requires PHP 7.3+)
    |
    | EasyMarket Security:
    | - Hashes user passwords (buyers, sellers, admins)
    | - One-way encryption (passwords cannot be decrypted)
    | - Automatic rehashing on login if algorithm parameters change
    |
    | Recommendation for EasyMarket:
    | - Production: "bcrypt" (default, best compatibility)
    | - High Security: "argon2id" (if PHP 7.3+ available)
    |
    | Note: Changing this will NOT automatically rehash existing passwords.
    | Users will be rehashed on their next successful login.
    |
    */

    'driver' => env('HASH_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | BCrypt work factor ("rounds" or "cost") controls how computationally
    | expensive it is to hash a password. Higher rounds = more secure but
    | slower login/registration.
    |
    | Work Factor Guide:
    | - 10 rounds : ~100ms to hash (default, good for most applications)
    | - 12 rounds : ~400ms to hash (recommended for high-security)
    | - 14 rounds : ~1.6s to hash (very high security, slow)
    | - 16 rounds : ~6.4s to hash (extreme security, too slow for web)
    |
    | Each increment doubles the processing time!
    |
    | Recommendation for EasyMarket:
    | - Current: 10 rounds (good balance for marketplace with many users)
    | - High Traffic: 10 rounds (prevents login bottlenecks)
    | - Admin Panel: Consider 12 rounds for admin-only authentication
    |
    | Security vs. Performance:
    | - Too low: Vulnerable to brute-force attacks
    | - Too high: Slow registration/login, poor user experience
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
        'verify' => env('BCRYPT_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon2i Options
    |--------------------------------------------------------------------------
    |
    | Argon2i is a modern password hashing algorithm designed to be resistant
    | to GPU-based attacks. It's more secure than BCrypt but requires more
    | server resources.
    |
    | Parameters:
    | - memory  : Memory cost in KB (1024 = 1MB)
    | - threads : Number of parallel threads to use
    | - time    : Number of iterations (time cost)
    |
    | Default values (1024 KB, 2 threads, 2 iterations) are balanced for
    | web applications. Increase these for higher security if you have
    | sufficient server resources.
    |
    | Recommendation for EasyMarket:
    | - Keep defaults unless you have dedicated servers with plenty of RAM
    | - Monitor server load if you switch from bcrypt to argon
    |
    | When to use Argon2i:
    | - High-security requirements (financial transactions)
    | - Dedicated servers with good resources
    | - Lower user concurrency (fewer simultaneous logins)
    |
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 2),
        'time' => env('ARGON_TIME', 4),         
        'verify' => env('ARGON_VERIFY', true),
    ],

];



















