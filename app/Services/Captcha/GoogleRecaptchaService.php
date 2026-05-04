<?php

namespace App\Services\Captcha;

use App\Models\Captcha;
use Illuminate\Support\Facades\Http;

/**
 * Google reCAPTCHA Service
 *
 * Handles verification and rendering of Google reCAPTCHA v2.
 *
 * @package App\Services\Captcha
 */
class GoogleRecaptchaService
{
    /**
     * The Google reCAPTCHA API verification endpoint
     */
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * The Google reCAPTCHA JavaScript API URL
     */
    private const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    /**
     * Create a new Google reCAPTCHA service instance.
     *
     * @param Captcha|null $captchaProvider The captcha provider model
     */
    public function __construct(
        protected ?Captcha $captchaProvider = null
    ) {
        $this->captchaProvider ??= getCaptcha('google_recaptcha');
    }

    /**
     * Verify the captcha token with Google reCAPTCHA.
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
     * Render the Google reCAPTCHA HTML.
     *
     * @param string $lang The language code (default: 'en')
     * @return string The captcha HTML markup
     */
    public function render(string $lang = 'en'): string
    {
        $siteKey = $this->captchaProvider?->site_key ?? '';

        return sprintf(
            '<script src="%s?hl=%s" async defer></script>
                <div class="g-recaptcha" data-sitekey="%s"></div>',
            self::SCRIPT_URL,
            htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8')
        );
    }
}


















