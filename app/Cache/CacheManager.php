<?php

declare(strict_types=1);

namespace App\Cache;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Cache, Artisan};

/**
 * CacheManager
 *
 * A generalized cache handler for blade views and data sections.
 * Provides simple methods to remember, forget, and flush cache data.
 */
class CacheManager
{
    /**
     * Cache key prefix
     *
     * @var string
     */
    protected string $prefix = 'blade_cache_';

    /**
     * Default cache expiry time in minutes
     *
     * @var int
     */
    protected int $defaultExpiryMinutes = 60;

    /**
     * Create a new CacheManager instance
     *
     * @param string|null $prefix Custom cache key prefix
     * @param int|null $defaultExpiryMinutes Default expiry time in minutes
     */
    public function __construct(?string $prefix = null, ?int $defaultExpiryMinutes = null)
    {
        if ($prefix !== null) {
            $this->prefix = $prefix;
        }

        if ($defaultExpiryMinutes !== null) {
            $this->defaultExpiryMinutes = $defaultExpiryMinutes;
        }
    }

    /**
     * Remember data in cache with expiry time
     *
     * @param string $key Cache key
     * @param Closure $callback Callback to execute if cache miss
     * @param int|null $expiryMinutes Cache expiry time in minutes
     * @return mixed
     */
    public function remember(string $key, Closure $callback, ?int $expiryMinutes = null): mixed
    {
        $cacheKey = $this->getCacheKey($key);
        $expiryMinutes = $expiryMinutes ?? $this->defaultExpiryMinutes;
        $cacheExpiry = Carbon::now()->addMinutes($expiryMinutes);

        return Cache::remember($cacheKey, $cacheExpiry, $callback);
    }

    /**
     * Remember data forever in cache
     *
     * @param string $key Cache key
     * @param Closure $callback Callback to execute if cache miss
     * @return mixed
     */
    public function rememberForever(string $key, Closure $callback): mixed
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::rememberForever($cacheKey, $callback);
    }

    /**
     * Get data from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if cache miss
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::get($cacheKey, $default);
    }

    /**
     * Put data in cache with expiry time
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $expiryMinutes Cache expiry time in minutes
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $expiryMinutes = null): bool
    {
        $cacheKey = $this->getCacheKey($key);
        $expiryMinutes = $expiryMinutes ?? $this->defaultExpiryMinutes;
        $cacheExpiry = Carbon::now()->addMinutes($expiryMinutes);

        return Cache::put($cacheKey, $value, $cacheExpiry);
    }

    /**
     * Put data in cache forever
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @return bool
     */
    public function putForever(string $key, mixed $value): bool
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::forever($cacheKey, $value);
    }

    /**
     * Check if cache key exists
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::has($cacheKey);
    }

    /**
     * Forget a specific cache key
     *
     * @param string $key Cache key
     * @return bool
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::forget($cacheKey);
    }

    /**
     * Forget multiple cache keys
     *
     * @param array $keys Array of cache keys
     * @return void
     */
    public function forgetMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Flush all cache with the current prefix
     * Note: This clears all cache keys that start with the prefix
     *
     * @return bool
     */
    public function flush(): bool
    {
        // Get all cache keys with this prefix and delete them
        $keys = $this->getAllCacheKeys();

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return true;
    }

    /**
     * Get all cache keys with the current prefix
     *
     * @return array
     */
    protected function getAllCacheKeys(): array
    {
        // For file/database cache drivers, we need to manually track keys
        // For now, return known keys for home_ prefix
        if ($this->prefix === 'home_') {
            return [
                $this->getCacheKey('categories_cache'),
                $this->getCacheKey('trending_products_cache'),
                $this->getCacheKey('best_selling_products_cache'),
                $this->getCacheKey('sale_products_cache'),
                $this->getCacheKey('free_products_cache'),
                $this->getCacheKey('premium_products_cache'),
                $this->getCacheKey('featured_products_cache'),
                $this->getCacheKey('featured_seller_cache'),
                $this->getCacheKey('latest_products_cache'),
                $this->getCacheKey('latest_products_categories_cache'),
                $this->getCacheKey('faqs_cache'),
                $this->getCacheKey('testimonials_cache'),
                $this->getCacheKey('blog_articles_cache'),
            ];
        }

        return [];
    }

    /**
     * Get full cache key with prefix
     *
     * @param string $key Original key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * Set a new cache key prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the current cache key prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set default expiry time in minutes
     *
     * @param int $minutes
     * @return self
     */
    public function setDefaultExpiry(int $minutes): self
    {
        $this->defaultExpiryMinutes = $minutes;

        return $this;
    }

    /**
     * Get default expiry time in minutes
     *
     * @return int
     */
    public function getDefaultExpiry(): int
    {
        return $this->defaultExpiryMinutes;
    }

    /**
     * Create a scoped cache instance with a specific prefix
     *
     * @param string $prefix Cache key prefix
     * @param int|null $expiryMinutes Default expiry time
     * @return self
     */
    public static function scope(string $prefix, ?int $expiryMinutes = null): self
    {
        return new self($prefix, $expiryMinutes);
    }

    /**
     * Clear all Laravel caches using optimize:clear
     *
     * @return bool
     */
    public static function clearAllCaches(): bool
    {
        try {
            Artisan::call('optimize:clear');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
