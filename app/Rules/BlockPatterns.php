<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * BlockPatterns - Security Validation Rule
 *
 * Laravel 11 validation rule that blocks potentially dangerous patterns
 * in user input to prevent code injection, XSS attacks, and template
 * engine exploits.
 *
 * This rule protects against:
 * - HTML/Script tags injection
 * - Laravel Blade template syntax ({{ }}, {!! !!})
 * - PHP code injection (<?php)
 * - Malicious curly brace patterns
 * - Template engine exploits
 *
 * Blocked Patterns:
 * - `{{ }}` - Blade echo syntax
 * - `{!! !!}` - Blade unescaped output
 * - `<?php` - PHP opening tags
 * - `{}` - Empty curly braces
 * - `{...}` - Any content in curly braces
 * - HTML tags (via strip_tags check)
 *
 * Use Cases:
 * - User input fields (names, descriptions, comments)
 * - Product titles and descriptions
 * - Blog post content
 * - Form submissions
 * - Any user-generated content
 *
 * Usage:
 * ```php
 * // In controller validation
 * $request->validate([
 *     'title' => ['required', 'string', 'block_patterns'],
 *     'description' => ['required', 'block_patterns'],
 * ]);
 *
 * // As validation rule object
 * $request->validate([
 *     'content' => ['required', new BlockPatterns],
 * ]);
 * ```
 *
 * @package App\Rules
 * @author EasyMarket Team
 * @version 2.0.0
 */
class BlockPatterns implements ValidationRule
{
    /**
     * Dangerous patterns to block
     *
     * These regex patterns detect potentially malicious code:
     * - Blade template syntax: {{ }}, {!! !!}
     * - PHP tags: <?php
     * - Empty or generic curly braces: {}, {...}
     */
    private const BLOCKED_PATTERNS = '/\{\{[^}]*\}\}|{!![^}]*!!}|<\?php|\{\}|\{[^}]*\}/';

    /**
     * Run the validation rule
     *
     * Validates the input by checking for:
     * 1. HTML tags (using strip_tags comparison)
     * 2. Dangerous patterns (Blade syntax, PHP code, curly braces)
     *
     * Null values are allowed (use 'required' rule if needed).
     *
     * @param string $attribute The attribute name being validated
     * @param mixed $value The value to validate
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail Callback to mark validation as failed
     * @return void
     *
     * @example
     * ```php
     * // Valid inputs
     * "Hello World" // ✓ Pass
     * "Product Name 123" // ✓ Pass
     * "Price: $50.00" // ✓ Pass
     * null // ✓ Pass (null allowed)
     *
     * // Invalid inputs
     * "<script>alert('xss')</script>" // ✗ Fail (HTML tags)
     * "{{ $variable }}" // ✗ Fail (Blade syntax)
     * "{!! $html !!}" // ✗ Fail (Blade unescaped)
     * "<?php echo 'hack'; ?>" // ✗ Fail (PHP code)
     * "Text {} more text" // ✗ Fail (curly braces)
     * ```
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow null values (use 'required' rule if null should fail)
        if ($value === null) {
            return;
        }

        // Convert value to string for validation (handle arrays)
        $stringValue = is_array($value) ? implode(' ', $value) : (string) $value;

        // Check for HTML tags
        if (!$this->isCleanOfHtmlTags($stringValue)) {
            $fail(__('validation.block_patterns'));
            return;
        }

        // Check for dangerous patterns
        if ($this->containsBlockedPatterns($stringValue)) {
            $fail(__('validation.block_patterns'));
        }
    }

    /**
     * Check if value contains HTML tags
     *
     * Compares the original value with its strip_tags() result.
     * If they differ, HTML tags are present.
     *
     * @param string $value The value to check
     * @return bool True if no HTML tags found, false otherwise
     */
    private function isCleanOfHtmlTags(string $value): bool
    {
        return strip_tags($value) === $value;
    }

    /**
     * Check if value contains blocked patterns
     *
     * Tests the value against regex patterns for:
     * - Blade template syntax
     * - PHP code tags
     * - Curly brace patterns
     *
     * @param string $value The value to check
     * @return bool True if blocked patterns found, false otherwise
     */
    private function containsBlockedPatterns(string $value): bool
    {
        return preg_match(self::BLOCKED_PATTERNS, $value) === 1;
    }
}


















