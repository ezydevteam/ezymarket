<?php

namespace App\Rules;

use App\Methods\CaptchaValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * CaptchaRule - Captcha Validation Rule
 *
 * Laravel 11 validation rule for verifying captcha responses from various
 * captcha providers (Google reCAPTCHA, Cloudflare Turnstile).
 *
 * This rule integrates with the CaptchaValidator to verify user responses
 * against the active captcha provider's API.
 *
 * Features:
 * - Multi-provider support (Google reCAPTCHA, Cloudflare Turnstile)
 * - Automatic service resolution based on active provider
 * - Translatable error messages
 * - Empty value validation
 * - IP-based verification support
 *
 * Usage:
 * ```php
 * // In controller validation
 * $request->validate([
 *     'g-recaptcha-response' => ['required', new CaptchaRule],
 * ]);
 *
 * // With CaptchaValidator helper
 * $rules = [
 *     'email' => 'required|email',
 * ] + app(CaptchaValidator::class)->validate();
 * ```
 *
 * @package App\Rules
 * @author EasyMarket Team
 * @version 2.0.0
 */
class CaptchaRule implements ValidationRule
{
    /**
     * The captcha service instance
     *
     * @var \App\Services\Captcha\GoogleRecaptchaService|\App\Services\Captcha\CloudflareTurnstileService
     */
    protected mixed $service;

    /**
     * Initialize the captcha validation rule
     *
     * Resolves the appropriate captcha service based on the currently
     * active captcha provider.
     *
     * @throws \InvalidArgumentException If no valid captcha provider is configured
     */
    public function __construct()
    {
        $this->service = app(CaptchaValidator::class)->getService();
    }

    /**
     * Run the validation rule
     *
     * Validates the captcha response by verifying it with the active
     * captcha provider's API. The response is checked for both presence
     * and validity.
     *
     * @param string $attribute The attribute name being validated
     * @param mixed $value The captcha response value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail Callback to mark validation as failed
     * @return void
     *
     * @example
     * ```php
     * // This method is called automatically by Laravel's validator
     * // when the rule is applied to a field
     * ```
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if value is empty
        if (empty($value)) {
            $fail(__('validation.captcha'));
            return;
        }

        // Verify captcha with provider service
        if (!$this->service->verify($value)) {
            $fail(__('validation.captcha'));
        }
    }
}

















