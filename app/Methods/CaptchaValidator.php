<?php

namespace App\Methods;

use App\Models\Captcha;
use App\Rules\CaptchaRule;
use App\Services\Captcha\CloudflareTurnstileService;
use App\Services\Captcha\GoogleRecaptchaService;
use InvalidArgumentException;

/**
 * CaptchaValidator - Captcha Validation Handler
 *
 * Manages captcha provider validation and service resolution for the application.
 * Supports multiple captcha providers including Google reCAPTCHA and
 * Cloudflare Turnstile.
 *
 * Features:
 * - Multi-provider support (Google reCAPTCHA, Cloudflare Turnstile)
 * - Dynamic service resolution based on active provider
 * - Validation rule generation for forms
 * - Automatic provider detection and configuration
 *
 * Usage:
 * ```php
 * // Get validation rules for forms
 * $captchaValidator = new CaptchaValidator();
 * $rules = $captchaValidator->validate();
 *
 * // Get captcha service instance
 * $service = $captchaValidator->getService();
 *
 * // Get default captcha provider
 * $provider = $captchaValidator->getDefaultCaptchaProvider();
 * ```
 *
 * @package App\Methods
 * @author Codebay Team
 * @version 1.0.0
 */
class CaptchaValidator
{
    /**
     * Get the captcha service instance for the active provider
     *
     * Resolves and returns the appropriate captcha service based on
     * the currently active and default captcha provider.
     *
     * @return GoogleRecaptchaService|CloudflareTurnstileService
     * @throws InvalidArgumentException If no valid provider is configured
     *
     * @example
     * ```php
     * $validator = new CaptchaValidator();
     * $service = $validator->getService();
     * $isValid = $service->validate($captchaResponse);
     * ```
     */
    public function getService()
    {
        $captchaProvider = $this->getDefaultCaptchaProvider();

        return $this->resolveCaptchaService($captchaProvider);
    }

    /**
     * Get validation rules for captcha fields
     *
     * Generates Laravel validation rules array for the active captcha provider.
     * Returns the appropriate response field name and validation rules.
     *
     * @return array Validation rules array [field => rules] or empty array if no provider
     *
     * @example
     * ```php
     * $validator = new CaptchaValidator();
     * $rules = $validator->validate();
     * // Returns: ['g-recaptcha-response' => ['required', CaptchaRule]] for Google reCAPTCHA
     * ```
     */
    public function validate()
    {
        $captchaProvider = $this->getDefaultCaptchaProvider();

        if ($captchaProvider) {
            $captchaResponseKey = $this->getCaptchaResponseKey($captchaProvider->alias);
            return [$captchaResponseKey => ['required', new CaptchaRule]];
        }

        return [];
    }

    /**
     * Resolve captcha service instance based on provider
     *
     * Factory method that returns the appropriate captcha service instance
     * based on the provider's alias.
     *
     * @param Captcha|null $captchaProvider The captcha provider model
     * @return GoogleRecaptchaService|CloudflareTurnstileService
     * @throws InvalidArgumentException If provider is null or has invalid alias
     */
    private function resolveCaptchaService($captchaProvider)
    {
        if (!$captchaProvider) {
            throw new InvalidArgumentException(translate('Invalid captcha provider'));
        }

        switch ($captchaProvider->alias) {
            case 'google_recaptcha':
                return app(GoogleRecaptchaService::class);
            case 'cloudflare_turnstile':
                return app(CloudflareTurnstileService::class);
            default:
                throw new InvalidArgumentException(translate('Invalid captcha provider'));
        }
    }

    /**
     * Get the default active captcha provider
     *
     * Retrieves the captcha provider that is currently active and set as default
     * in the system.
     *
     * @return Captcha|null The default active provider or null if none configured
     */
    public function getDefaultCaptchaProvider()
    {
        return Captcha::active()->default()->first();
    }

    /**
     * Get the response field name for a captcha provider
     *
     * Returns the field name used in form submissions for the given
     * captcha provider alias.
     *
     * @param string $alias The captcha provider alias
     * @return string The response field name
     * @throws InvalidArgumentException If alias is not recognized
     *
     * @example
     * ```php
     * $fieldName = $this->getCaptchaResponseKey('google_recaptcha');
     * // Returns: 'g-recaptcha-response'
     * ```
     */
    private function getCaptchaResponseKey(string $alias): string
    {
        switch ($alias) {
            case 'google_recaptcha':
                return 'g-recaptcha-response';
            case 'cloudflare_turnstile':
                return 'cf-turnstile-response';
            default:
                throw new InvalidArgumentException(translate('Invalid captcha provider'));
        }
    }
}


















