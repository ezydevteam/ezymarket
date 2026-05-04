<?php

namespace App\Services\Captcha;

use App\Models\Captcha;
use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile Captcha Service
 *
 * Handles verification and rendering of Cloudflare Turnstile captcha.
 *
 * @package App\Services\Captcha
 */
class CloudflareTurnstileService
{
    /**
     * The Cloudflare Turnstile API verification endpoint
     */
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * The Cloudflare Turnstile JavaScript API URL
     */
    private const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    /**
     * Create a new Cloudflare Turnstile service instance.
     *
     * @param Captcha|null $captchaProvider The captcha provider model
     */
    public function __construct(
        protected ?Captcha $captchaProvider = null
    ) {
        $this->captchaProvider ??= getCaptcha('cloudflare_turnstile');
    }

    /**
     * Verify the captcha token with Cloudflare Turnstile.
     *
     * @param string|null $token The captcha response token
     * @return bool True if verification succeeds, false otherwise
     */
    public function verify(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret' => $this->captchaProvider?->secret_key ?? '',
                'response' => $token,
            ]);

            return (bool) ($response->json('success') ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Render the Cloudflare Turnstile captcha HTML.
     *
     * @param string $lang The language code (default: 'en')
     * @return string The captcha HTML markup
     */
    public function render(string $lang = 'en'): string
    {
        $siteKey = $this->captchaProvider?->site_key ?? '';

        return sprintf(
            '<script src="%s" async defer></script>
                <div class="cf-turnstile" data-theme="light" data-language="%s" data-sitekey="%s"></div>',
            self::SCRIPT_URL,
            htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8')
        );
    }
}

















