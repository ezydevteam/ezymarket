<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * GeolocationService
 *
 * Geolocation lookup service for IP addresses.
 * Uses multiple providers with automatic fallback for reliability.
 *
 * Features:
 * - Automatic caching (reduces API calls)
 * - Multiple provider support with fallback
 * - Validation for IP addresses
 * - Handles local/private IPs gracefully
 *
 * Usage:
 *     $info = app(GeolocationService::class)->lookup($ip);
 *     echo $info->country; // "United States"
 *     echo $info->currency; // "USD"
 *
 * @package App\Services
 */
class GeolocationService
{
    /**
     * Cache duration (7 days)
     * IP geolocation data rarely changes, safe to cache long-term
     */
    private const CACHE_TTL = 60 * 60 * 24 * 7; // 7 days in seconds

    /**
     * HTTP request timeout (seconds)
     * Prevents hanging on slow API responses
     */
    private const TIMEOUT = 5;

    /**
     * Lookup IP address geolocation data
     *
     * @param string|null $ip IP address to lookup
     * @return object Geolocation data object
     */
    public function lookup(?string $ip = null): object
    {
        // Get IP if not provided
        $ip = $ip ?: $this->getClientIp();

        // Validate IP address
        if (!$this->isValidIp($ip)) {
            return $this->getDefaultData($ip, 'Invalid IP address');
        }

        // Check if IP is local/private
        if ($this->isLocalIp($ip)) {
            return $this->getDefaultData($ip, 'Local/Private IP');
        }

        // Check cache first
        $cacheKey = "ip_lookup:{$ip}";
        if (Cache::has($cacheKey)) {
            return (object) Cache::get($cacheKey);
        }

        // Try multiple providers with fallback
        $data = $this->fetchFromProviders($ip);

        // Cache the result
        Cache::put($cacheKey, $data, self::CACHE_TTL);

        return (object) $data;
    }

    /**
     * Fetch geolocation data from multiple providers with fallback
     *
     * @param string $ip IP address
     * @return array Geolocation data
     */
    private function fetchFromProviders(string $ip): array
    {
        // Try primary provider: GeoPlugin (free, no API key needed)
        try {
            $data = $this->fetchFromGeoPlugin($ip);
            if (!empty($data['country']) && $data['country'] !== 'Unknown') {
                return $data;
            }
        } catch (Exception $e) {
            Log::warning("GeoPlugin API failed for IP {$ip}: " . $e->getMessage());
        }

        // Try fallback provider: ip-api.com (free, 45 requests/min)
        try {
            $data = $this->fetchFromIpApi($ip);
            if (!empty($data['country']) && $data['country'] !== 'Unknown') {
                return $data;
            }
        } catch (Exception $e) {
            Log::warning("ip-api.com failed for IP {$ip}: " . $e->getMessage());
        }

        // All providers failed, return default data
        Log::error("All IP lookup providers failed for IP: {$ip}");
        return $this->getDefaultDataArray($ip, 'API unavailable');
    }

    /**
     * Fetch data from GeoPlugin API (Primary)
     *
     * @param string $ip IP address
     * @return array Geolocation data
     * @throws Exception
     */
    private function fetchFromGeoPlugin(string $ip): array
    {
        $url = "http://www.geoplugin.net/xml.gp?ip=" . urlencode($ip);

        // Use simplexml for backward compatibility
        $ipInfo = @simplexml_load_file($url);

        if (!$ipInfo) {
            throw new Exception("Failed to fetch from GeoPlugin");
        }

        return [
            'ip' => $ip,
            'country' => !empty((string) $ipInfo->geoplugin_countryName) ? (string) $ipInfo->geoplugin_countryName : 'Unknown',
            'country_code' => !empty((string) $ipInfo->geoplugin_countryCode) ? (string) $ipInfo->geoplugin_countryCode : 'Unknown',
            'city' => !empty((string) $ipInfo->geoplugin_city) ? (string) $ipInfo->geoplugin_city : 'Unknown',
            'timezone' => !empty((string) $ipInfo->geoplugin_timezone) ? (string) $ipInfo->geoplugin_timezone : 'Unknown',
            'latitude' => !empty((string) $ipInfo->geoplugin_latitude) ? (string) $ipInfo->geoplugin_latitude : 'Unknown',
            'longitude' => !empty((string) $ipInfo->geoplugin_longitude) ? (string) $ipInfo->geoplugin_longitude : 'Unknown',
            'currency' => !empty((string) $ipInfo->geoplugin_currencyCode) ? (string) $ipInfo->geoplugin_currencyCode : 'Unknown',
            'location' => $this->formatLocation(
                !empty((string) $ipInfo->geoplugin_city) ? (string) $ipInfo->geoplugin_city : 'Unknown',
                !empty((string) $ipInfo->geoplugin_countryCode) ? (string) $ipInfo->geoplugin_countryCode : 'Unknown'
            ),
            'provider' => 'GeoPlugin',
        ];
    }

    /**
     * Fetch data from ip-api.com (Fallback)
     *
     * @param string $ip IP address
     * @return array Geolocation data
     * @throws Exception
     */
    private function fetchFromIpApi(string $ip): array
    {
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city,timezone,lat,lon,currency";

        $response = Http::timeout(self::TIMEOUT)->get($url);

        if (!$response->successful() || $response->json('status') !== 'success') {
            throw new Exception("Failed to fetch from ip-api.com");
        }

        $data = $response->json();

        return [
            'ip' => $ip,
            'country' => $data['country'] ?? 'Unknown',
            'country_code' => $data['countryCode'] ?? 'Unknown',
            'city' => $data['city'] ?? 'Unknown',
            'timezone' => $data['timezone'] ?? 'Unknown',
            'latitude' => $data['lat'] ?? 'Unknown',
            'longitude' => $data['lon'] ?? 'Unknown',
            'currency' => $data['currency'] ?? 'Unknown',
            'location' => $this->formatLocation($data['city'] ?? 'Unknown', $data['countryCode'] ?? 'Unknown'),
            'provider' => 'ip-api.com',
        ];
    }

    /**
     * Get default data for invalid/local IPs
     *
     * @param string $ip IP address
     * @param string $reason Reason for default data
     * @return object Default data object
     */
    private function getDefaultData(string $ip, string $reason = 'Unknown'): object
    {
        return (object) $this->getDefaultDataArray($ip, $reason);
    }

    /**
     * Get default data array
     *
     * @param string $ip IP address
     * @param string $reason Reason for default data
     * @return array Default data
     */
    private function getDefaultDataArray(string $ip, string $reason = 'Unknown'): array
    {
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'country_code' => 'Unknown',
            'city' => 'Unknown',
            'timezone' => 'Unknown',
            'latitude' => 'Unknown',
            'longitude' => 'Unknown',
            'currency' => 'Unknown',
            'location' => 'Unknown',
            'provider' => $reason,
        ];
    }

    /**
     * Format location string
     *
     * @param string $city City name
     * @param string $countryCode Country code
     * @return string Formatted location
     */
    private function formatLocation(string $city, string $countryCode): string
    {
        // Don't show "Unknown, Unknown"
        if ($city === 'Unknown' && $countryCode === 'Unknown') {
            return 'Unknown';
        }

        // Don't show city if unknown
        if ($city === 'Unknown') {
            return $countryCode;
        }

        return "{$city}, {$countryCode}";
    }

    /**
     * Validate IP address format
     *
     * @param string $ip IP address
     * @return bool
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if IP is local/private
     *
     * @param string $ip IP address
     * @return bool
     */
    private function isLocalIp(string $ip): bool
    {
        // Check for private/reserved IPs
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Get client IP address
     *
     * @return string Client IP
     */
    private function getClientIp(): string
    {
        // Try various headers (proxy-aware)
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Standard proxy header
            'HTTP_X_REAL_IP',           // Nginx proxy
            'HTTP_CLIENT_IP',           // Proxy header
            'REMOTE_ADDR',              // Direct connection
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Handle comma-separated IPs (X-Forwarded-For)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Clear cache for specific IP
     *
     * @param string $ip IP address
     * @return bool
     */
    public function clearCache(string $ip): bool
    {
        return Cache::forget("ip_lookup:{$ip}");
    }

    /**
     * Get cached data without fetching
     *
     * @param string $ip IP address
     * @return object|null Cached data or null
     */
    public function getCached(string $ip): ?object
    {
        $cacheKey = "ip_lookup:{$ip}";

        if (Cache::has($cacheKey)) {
            return (object) Cache::get($cacheKey);
        }

        return null;
    }
}

















