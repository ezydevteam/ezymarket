<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Send Mail Service
 *
 * Handles email sending with template system and short code replacement.
 * Central service for all transactional emails in EasyMarket.
 *
 * Features:
 * - Email validation before sending
 * - Template-based email system
 * - Short code replacement (placeholders)
 * - Mail status checking (enabled/disabled)
 * - Exception handling and logging
 *
 * Short Codes:
 * Short codes are template placeholders like {{username}}, {{link}}, etc.
 * that get replaced with actual values when sending emails.
 *
 * Usage:
 * ```php
 * SendMail::send(
 *     receiver_email: 'user@example.com',
 *     mail_template_alias: 'welcome_email',
 *     short_codes: [
 *         'username' => 'John Doe',
 *         'link' => 'https://example.com/verify'
 *     ]
 * );
 * ```
 *
 * @package App\Mail
 */
class SendMail
{
    /**
     * Send an email using a template with short code replacement
     *
     * Validates the email address, checks if mail is enabled in settings,
     * retrieves the template, replaces short codes, and sends the email.
     *
     * @param string $receiver_email Recipient email address
     * @param string $mail_template_alias Template identifier (e.g., 'welcome_email')
     * @param array<string, mixed> $short_codes Key-value pairs for placeholder replacement
     * @return void
     */
    public static function send(string $receiver_email, string $mail_template_alias, array $short_codes): void
    {
        // Validate email address
        $validator = Validator::make(['email' => $receiver_email], [
            'email' => 'required|email',
        ]);

        if (empty($receiver_email) || $validator->fails()) {
            Log::warning('Invalid email address for mail sending', [
                'email' => $receiver_email,
                'template' => $mail_template_alias,
                'errors' => $validator->errors()->toArray(),
            ]);
            return;
        }

        try {
            // Check if mail system is enabled
            $mailSettings = @settings('mail');
            if (!$mailSettings || !$mailSettings->status) {
                Log::info('Mail system is disabled, skipping email', [
                    'template' => $mail_template_alias,
                    'recipient' => $receiver_email,
                ]);
                return;
            }

            // Get mail template
            $mailTemplate = mailTemplate($mail_template_alias);

            if (!$mailTemplate || !$mailTemplate->status) {
                Log::warning('Mail template not found or disabled', [
                    'template' => $mail_template_alias,
                    'recipient' => $receiver_email,
                ]);
                return;
            }

            // Replace short codes in subject and body
            $subject = self::replaceShortCodes($mailTemplate->subject, $short_codes);
            $body = self::replaceShortCodes($mailTemplate->body, $short_codes);

            // Send email
            Mail::to($receiver_email)->send(new CustomMail($subject, $body));

        } catch (Throwable $e) {
            Log::error('Failed to send email', [
                'template' => $mail_template_alias,
                'recipient' => $receiver_email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Replace short codes (placeholders) in content
     *
     * Replaces all {{key}} placeholders with their corresponding values
     * from the short_codes array.
     *
     * Example:
     * - Content: "Hello {{username}}, welcome to {{site_name}}!"
     * - Short codes: ['username' => 'John', 'site_name' => 'EasyMarket']
     * - Result: "Hello John, welcome to EasyMarket!"
     *
     * @param string $content Content with {{placeholder}} tags
     * @param array<string, mixed> $short_codes Key-value pairs for replacement
     * @return string Content with placeholders replaced
     */
    public static function replaceShortCodes(string $content, array $short_codes): string
    {
        foreach ($short_codes as $key => $value) {
            // Replace {{key}} with value
            $content = str_replace("{{" . $key . "}}", (string) $value, $content);
        }

        return $content;
    }
}


















