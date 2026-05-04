<?php

namespace App\Http\Middleware;

use Closure;
use Reefki\DeviceDetector\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track GET requests and exclude specific paths
        if ($request->method() === 'GET' && $this->shouldTrack($request)) {
            try {
                // Get or create session ID
                $sessionId = $request->session()->getId();

                // Parse traffic source from referrer
                $referrer = $request->header('referer');
                $trafficSource = $this->determineTrafficSource($referrer, $request);

                // Parse device information using DeviceDetector
                $device = Device::detectRequest($request);

                $deviceType = 'desktop';
                if ($device->isMobile() && !$device->isTablet()) {
                    $deviceType = 'mobile';
                } elseif ($device->isTablet()) {
                    $deviceType = 'tablet';
                }

                $clientInfo = $device->getClient();
                $osInfo = $device->getOs();

                $browser = $clientInfo['name'] ?? 'Unknown';
                $platform = $osInfo['name'] ?? 'Unknown';

                // Insert page view record
                DB::table('page_views')->insert([
                    'session_id' => $sessionId,
                    'user_id' => authUser()?->id,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'referrer' => $referrer,
                    'utm_source' => $request->query('utm_source'),
                    'utm_medium' => $request->query('utm_medium'),
                    'utm_campaign' => $request->query('utm_campaign'),
                    'utm_term' => $request->query('utm_term'),
                    'utm_content' => $request->query('utm_content'),
                    'traffic_source' => $trafficSource,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_type' => $deviceType,
                    'browser' => $browser,
                    'platform' => $platform,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Fail silently - don't break the request if tracking fails
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request should be tracked
     */
    private function shouldTrack(Request $request): bool
    {
        // Exclude admin users (even when visiting frontend)
        if (authAdmin()) {
            return false;
        }

        $path = $request->path();

        // Exclude admin panel routes
        $adminPath = adminPath();
        if (Str::startsWith($path, $adminPath . '/') || $path === $adminPath) {
            return false;
        }

        $excludedPaths = [
            'livewire/*',
            'api/*',
            '_debugbar/*',
            'telescope/*',
            'horizon/*',
            '*.js',
            '*.css',
            '*.jpg',
            '*.jpeg',
            '*.png',
            '*.gif',
            '*.svg',
            '*.ico',
            '*.woff',
            '*.woff2',
            '*.ttf',
            '*.eot',
            '*.map',
            '*.json',
            '*.xml',
        ];

        foreach ($excludedPaths as $pattern) {
            if (Str::is($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine traffic source from referrer and UTM parameters
     */
    private function determineTrafficSource(?string $referrer, Request $request): string
    {
        // Check UTM parameters first
        $utmSource = $request->query('utm_source');
        $utmMedium = $request->query('utm_medium');

        if ($utmSource || $utmMedium) {
            // Email campaigns
            if (Str::contains(strtolower($utmMedium ?? ''), ['email', 'newsletter'])) {
                return 'Email';
            }

            // Paid ads
            if (Str::contains(strtolower($utmMedium ?? ''), ['cpc', 'ppc', 'paid', 'ad', 'ads', 'display'])) {
                return 'Ads';
            }

            // Social media
            if (Str::contains(strtolower($utmSource ?? ''), ['facebook', 'twitter', 'instagram', 'linkedin', 'pinterest', 'youtube', 'tiktok', 'social'])) {
                return 'Social';
            }

            // Referral
            if (Str::contains(strtolower($utmMedium ?? ''), ['referral'])) {
                return 'Referral';
            }
        }

        // No referrer = direct traffic
        if (empty($referrer)) {
            return 'Direct';
        }

        $referrerHost = parse_url($referrer, PHP_URL_HOST);
        $currentHost = $request->getHost();

        // Same domain = direct (or internal navigation)
        if ($referrerHost === $currentHost || empty($referrerHost)) {
            return 'Direct';
        }

        // Check for social media platforms
        $socialPlatforms = [
            'facebook.com',
            'fb.com',
            'm.facebook.com',
            'l.facebook.com',
            'twitter.com',
            't.co',
            'x.com',
            'instagram.com',
            'linkedin.com',
            'lnkd.in',
            'pinterest.com',
            'pin.it',
            'youtube.com',
            'youtu.be',
            'tiktok.com',
            'reddit.com',
            'tumblr.com',
            'snapchat.com',
            'whatsapp.com',
            'wa.me',
            'telegram.org',
            't.me',
        ];

        foreach ($socialPlatforms as $platform) {
            if (Str::contains($referrerHost, $platform)) {
                return 'Social';
            }
        }

        // Check for search engines
        $searchEngines = [
            'google.',
            'bing.com',
            'yahoo.com',
            'duckduckgo.com',
            'baidu.com',
            'yandex.',
            'ask.com',
            'aol.com',
            'search.yahoo.',
            'ecosia.org',
            'qwant.com',
        ];

        foreach ($searchEngines as $engine) {
            if (Str::contains($referrerHost, $engine)) {
                return 'Search';
            }
        }

        // Check for email clients (webmail)
        $emailPlatforms = ['mail.google.com', 'outlook.', 'mail.yahoo.com', 'mail.aol.com'];

        foreach ($emailPlatforms as $platform) {
            if (Str::contains($referrerHost, $platform)) {
                return 'Email';
            }
        }

        // Everything else is referral traffic
        return 'Referral';
    }
}
