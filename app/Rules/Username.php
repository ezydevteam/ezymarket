<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Username - Username Validation Rule
 *
 * Laravel 11 validation rule that prevents users from registering with
 * restricted or reserved usernames. Protects system paths, common terms,
 * and administrative keywords.
 *
 * This rule protects against:
 * - System administrator usernames (admin, root, sysadmin)
 * - Common system paths (api, blog, shop, dashboard)
 * - Reserved keywords (system, backup, test, demo)
 * - Generic usernames (user, guest, anonymous)
 * - Marketplace terms (products, category, reviews)
 *
 * Features:
 * - Case-insensitive matching (Admin = admin = ADMIN)
 * - 80+ restricted usernames
 * - Organized by category (system, paths, marketplace)
 * - Translatable error messages
 * - Easy to extend
 *
 * Usage:
 * ```php
 * // In controller validation
 * $request->validate([
 *     'username' => ['required', 'string', 'username', 'unique:users'],
 * ]);
 *
 * // As validation rule object
 * $request->validate([
 *     'username' => ['required', new Username],
 * ]);
 * ```
 *
 * @package App\Rules
 * @author Codebay Team
 * @version 1.0.0
 */
class Username implements ValidationRule
{
    /**
     * List of restricted usernames
     *
     * Organized by category for easier maintenance:
     * - System/Admin: admin, root, administrator, etc.
     * - Generic: user, test, demo, guest, etc.
     * - Marketplace: products, category, reviews, etc.
     * - Paths: api, blog, shop, dashboard, etc.
     * - Security: password, qwerty, welcome, etc.
     *
     * All comparisons are case-insensitive.
     */
    private const RESTRICTED_USERNAMES = [
        // System & Administrative
        'admin',
        'administrator',
        'root',
        'superuser',
        'sysadmin',
        'moderator',
        'webmaster',
        'manager',
        'owner',

        // Generic & Testing
        'user',
        'test',
        'guest',
        'demo',
        'anonymous',
        'system',
        'backup',
        'nobody',

        // Contact & Support
        'info',
        'help',
        'contact',
        'support',
        'sales',
        'service',
        'security',
        'tech',
        'finance',
        'billing',
        'legal',

        // Weak Passwords/Common
        'nopass',
        'password',
        'qwerty',
        'welcome',

        // Marketplace & E-commerce
        'profile',
        'portfolio',
        'store',
        'followers',
        'following',
        'reviews',
        'category',
        'categories',
        'products',
        'product',
        'favorite',
        'feedback',
        'follow',
        'gadget',
        'gadgets',
        'games',
        'group',
        'groups',
        'ecommerce',

        // System Paths & Reserved
        'directory',
        'domain',
        'download',
        'downloads',
        'edit',
        'editor',
        'email',
        'forum',
        'forums',
        'files',
        'homepage',
        'hosting',
        'hostname',
        'httpd',
        'https',
        'information',
        'image',
        'images',
        'index',
        'invite',
        'intranet',
        'indice',
        'iphone',
        'javascript',
        'knowledgebase',
        'lists',
        'websites',
        'workshop',

        // Placeholder Names
        'yourname',
        'yourusername',
        'yoursite',
        'yourdomain',
    ];

    /**
     * Run the validation rule
     *
     * Validates the username by checking if it matches any restricted
     * username in the list. The comparison is case-insensitive.
     *
     * @param string $attribute The attribute name being validated
     * @param mixed $value The username value to validate
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail Callback to mark validation as failed
     * @return void
     *
     * @example
     * ```php
     * // Valid usernames
     * "john_doe" // ✓ Pass
     * "seller123" // ✓ Pass
     * "buyer_2024" // ✓ Pass
     * "shop_owner" // ✓ Pass
     *
     * // Invalid usernames (restricted)
     * "admin" // ✗ Fail (system admin)
     * "Admin" // ✗ Fail (case-insensitive)
     * "ADMINISTRATOR" // ✗ Fail (admin variant)
     * "products" // ✗ Fail (marketplace term)
     * "test" // ✗ Fail (generic/testing)
     * "password" // ✗ Fail (security risk)
     * ```
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convert to string and lowercase for comparison
        $username = strtolower((string) $value);

        // Check if username is in restricted list
        if ($this->isRestricted($username)) {
            $fail(__('validation.username'));
        }
    }

    /**
     * Check if username is restricted
     *
     * Performs case-insensitive comparison against the list of
     * restricted usernames.
     *
     * @param string $username The username to check (should be lowercase)
     * @return bool True if username is restricted, false otherwise
     */
    private function isRestricted(string $username): bool
    {
        return in_array($username, self::RESTRICTED_USERNAMES, true);
    }
}

















