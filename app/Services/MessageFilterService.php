<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Enhanced message filtering service with comprehensive pattern matching
 */
class MessageFilterService
{
    /* ---------- Configuration Properties ---------- */
    public array $bannedKeywords = [];
    public bool $blockLinks = true;
    public bool $blockEmails = true;
    public bool $blockPhones = true;
    public bool $blockSocialMedia = true;
    public bool $blockAddresses = true;
    public bool $strictMode = true;

    /* ---------- Enhanced Regex Patterns ---------- */
    private array $linkPatterns = [
        // HTTP/HTTPS URLs
        '/https?:\/\/(?:[-\w.])+(?:[:\d]+)?(?:\/(?:[\w\/_.])*(?:\?(?:[\w&=%.])*)?(?:#(?:[\w.])*)?)?/i',
        // www URLs without protocol
        '/www\.(?:[-\w.]){2,}\.(?:[a-z]{2,})/i',
        // Domain.TLD patterns (more specific)
        '/\b(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,})\b/i',
        // IP addresses
        '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}(?::[0-9]+)?\b/',
        // Shortened URLs
        '/\b(?:bit\.ly|tinyurl\.com|t\.co|goo\.gl|short\.link|ow\.ly|buff\.ly)\/\w+/i',
    ];

    private array $emailPatterns = [
        // Comprehensive email pattern
        '/\b[A-Za-z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*\b/',
        // Obfuscated emails (dot, at replacements)
        '/\b\w+[\s]*(?:at|@|AT|\[at\]|\(at\))[\s]*\w+[\s]*(?:dot|DOT|\[dot\]|\(dot\))[\s]*\w+\b/i',
        // Email with spaces
        '/\b[A-Za-z0-9._%+-]+[\s]+@[\s]*[A-Za-z0-9.-]+[\s]*\.[\s]*[A-Za-z]{2,}\b/',
    ];

    private array $phonePatterns = [
        // International format
        '/(?:\+[1-9]\d{0,3})?[\s.-]?(?:\([0-9]{1,4}\))?[\s.-]?[0-9]{1,4}[\s.-]?[0-9]{1,4}[\s.-]?[0-9]{1,9}/',
        // US format variations
        '/(?:\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4})/',
        // WhatsApp format
        '/(?:whatsapp|wa\.me)[:\s]*(?:\+?[0-9\s-]{8,15})/',
        // Telegram format
        '/(?:telegram|t\.me)[:\s]*(?:@?[a-zA-Z0-9_]{5,32})/',
        // Obfuscated numbers
        '/(?:call|text|phone|mobile|cell)[:\s]*(?:\+?[0-9\s.-]{8,15})/',
    ];

    private array $socialMediaPatterns = [
        // Social media usernames
        '/(?:@|#)[a-zA-Z0-9_]{1,30}/',
        // Platform-specific patterns
        '/(?:instagram\.com|ig\.com|instagr\.am)\/[a-zA-Z0-9_.]+/i',
        '/(?:facebook\.com|fb\.com|fb\.me)\/[a-zA-Z0-9_.]+/i',
        '/(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+/i',
        '/(?:tiktok\.com)\/[@a-zA-Z0-9_.]+/i',
        '/(?:youtube\.com|youtu\.be)\/[a-zA-Z0-9_]+/i',
        '/(?:snapchat\.com|snapchat)[\s:\/]*[a-zA-Z0-9_.]+/i',
        '/(?:telegram|t\.me)[\s:\/]*[a-zA-Z0-9_]+/i',
        '/(?:whatsapp|wa\.me)[\s:\/]*[a-zA-Z0-9_+]+/i',
        '/(?:linkedin\.com)\/in\/[a-zA-Z0-9_-]+/i',
        '/(?:discord\.gg|discord\.com)\/[a-zA-Z0-9]+/i',
        '/(?:pinterest\.com)\/[a-zA-Z0-9_]+/i',
        '/(?:reddit\.com)\/u\/[a-zA-Z0-9_]+/i',
        // Social media invitation phrases
        '/(?:follow|add|find)\s+me\s+on\s+(?:instagram|facebook|twitter|tiktok|snapchat|telegram|whatsapp)/i',
        '/(?:my|check)\s+(?:ig|insta|fb|twitter|snap)\s*[@:]\s*[a-zA-Z0-9_.]+/i',
    ];

    private array $addressPatterns = [
        // Street addresses
        '/\d+\s+(?:[NSEW]\s+)?(?:[A-Za-z0-9\s]+\s+)(?:Street|St\.?|Road|Rd\.?|Avenue|Ave\.?|Lane|Ln\.?|Drive|Dr\.?|Boulevard|Blvd\.?|Circle|Cir\.?|Court|Ct\.?|Place|Pl\.?)/i',
        // PO Box
        '/P\.?O\.?\s*Box\s+\d+/i',
        // Zip codes
        '/\b\d{5}(?:-\d{4})?\b/',
        // International postal codes
        '/\b[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}\b/', // UK
        '/\b[A-Z]\d[A-Z]\s*\d[A-Z]\d\b/', // Canada
        // City, State format
        '/\b[A-Za-z\s]+,\s*[A-Z]{2}\s*\d{5}\b/',
        // GPS coordinates
        '/(?:lat|latitude|long|longitude)[:\s]*[-]?\d{1,3}\.\d+/i',
    ];

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load settings from database with caching
     */
    private function loadSettings(): void
    {
        $settings = Cache::remember('chatbox_filter_settings', 300, function () {
            return settings('chatbox');
        });

        $this->bannedKeywords = !empty($settings?->banned_keywords)
            ? array_map('strtolower', $settings->banned_keywords)
            : $this->getDefaultBannedKeywords();

        $this->blockLinks = $settings->block_links ?? true;
        $this->blockEmails = $settings->block_emails ?? true;
        $this->blockPhones = $settings->block_phones ?? true;
        $this->blockSocialMedia = $settings->block_social_media ?? true;
        $this->blockAddresses = $settings->block_addresses ?? true;
        $this->strictMode = $settings->strict_mode ?? false;
    }

    /**
     * Default banned keywords
     */
    private function getDefaultBannedKeywords(): array
    {
        return [
            'spam', 'scam', 'phishing', 'fraud', 'fake', 'money laundering',
            'bitcoin', 'cryptocurrency', 'investment opportunity', 'get rich quick',
            'nigerian prince', 'lottery winner', 'inheritance', 'wire transfer',
            'bank transfer', 'western union', 'moneygram', 'paypal me',
            'click here', 'urgent', 'confidential', 'secret', 'exclusive offer'
        ];
    }

    /**
     * Enhanced filter method with detailed logging
     */
    public function filter(string $content): ?string
    {
        $originalContent = $content;
        $violations = [];

        try {
            // Check banned keywords
            if ($keywordViolation = $this->checkBannedKeywords($content)) {
                $violations[] = "banned_keyword: {$keywordViolation}";
                if ($this->strictMode) {
                    //$this->logViolation($originalContent, $violations);
                    return null;
                }
            }

            // Filter links
            if ($this->blockLinks) {
                $content = $this->filterLinks($content, $violations);
            }

            // Filter emails
            if ($this->blockEmails) {
                $content = $this->filterEmails($content, $violations);
            }

            // Filter phone numbers
            if ($this->blockPhones) {
                $content = $this->filterPhones($content, $violations);
            }

            // Filter social media
            if ($this->blockSocialMedia) {
                $content = $this->filterSocialMedia($content, $violations);
            }

            // Filter addresses
            if ($this->blockAddresses) {
                $content = $this->filterAddresses($content, $violations);
            }

            // Log violations if any
            if (!empty($violations)) {
                //$this->logViolation($originalContent, $violations);
            }

            // Return filtered content or placeholder
            $filtered = trim($content);
            return $filtered === '' ? '[message filtered]' : $filtered;

        } catch (\Exception $e) {
            // In case of error, return filtered placeholder
            return $this->strictMode ? null : '[message filtered - error]';
        }
    }

    /**
     * Check for banned keywords
     */
    private function checkBannedKeywords(string $content): ?string
    {
        $lowerContent = strtolower($content);

        foreach ($this->bannedKeywords as $keyword) {
            if (str_contains($lowerContent, $keyword)) {
                return $keyword;
            }
        }

        return null;
    }

    /**
     * Filter links with comprehensive patterns
     */
    private function filterLinks(string $content, array &$violations): string
    {
        foreach ($this->linkPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'link_detected';
                $content = preg_replace($pattern, '[link removed]', $content);
            }
        }

        return $content;
    }

    /**
     * Filter emails including obfuscated formats
     */
    private function filterEmails(string $content, array &$violations): string
    {
        foreach ($this->emailPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'email_detected';
                $content = preg_replace($pattern, '[email removed]', $content);
            }
        }

        return $content;
    }

    /**
     * Filter phone numbers including international formats
     */
    private function filterPhones(string $content, array &$violations): string
    {
        foreach ($this->phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'phone_detected';
                $content = preg_replace($pattern, '[phone removed]', $content);
            }
        }

        return $content;
    }

    /**
     * Filter social media with extensive platform coverage
     */
    private function filterSocialMedia(string $content, array &$violations): string
    {
        foreach ($this->socialMediaPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'social_media_detected';
                $content = preg_replace($pattern, '[social removed]', $content);
            }
        }

        return $content;
    }

    /**
     * Filter addresses including international formats
     */
    private function filterAddresses(string $content, array &$violations): string
    {
        foreach ($this->addressPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'address_detected';
                $content = preg_replace($pattern, '[address removed]', $content);
            }
        }

        return $content;
    }

    /**
     * Log filter violations for monitoring
     */
    private function logViolation(string $originalContent, array $violations): void
    {
        Log::info('Message filter violation', [
            'content' => substr($originalContent, 0, 200), // Truncated for logs
            'violations' => $violations,
            'user_id' => authUser()->id() ?? 'unknown',
            'ip' => request()->ip(),
            'timestamp' => now()
        ]);
    }

    /**
     * Reload settings and clear cache
     */
    public function reload(): void
    {
        Cache::forget('chatbox_filter_settings');
        $this->loadSettings();
    }

    /**
     * Test a message without filtering (for admin preview)
     */
    public function analyze(string $content): array
    {
        $violations = [];

        // Check each filter type
        if ($this->checkBannedKeywords($content)) {
            $violations['banned_keywords'] = true;
        }

        foreach ($this->linkPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations['links'] = true;
                break;
            }
        }

        foreach ($this->emailPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations['emails'] = true;
                break;
            }
        }

        foreach ($this->phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations['phones'] = true;
                break;
            }
        }

        foreach ($this->socialMediaPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations['social_media'] = true;
                break;
            }
        }

        foreach ($this->addressPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations['addresses'] = true;
                break;
            }
        }

        return [
            'violations' => $violations,
            'would_be_filtered' => !empty($violations),
            'would_be_blocked' => $this->strictMode && isset($violations['banned_keywords'])
        ];
    }
}

















