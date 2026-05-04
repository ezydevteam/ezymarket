<?php

namespace App\Methods;

/**
 * Avatar Generator
 *
 * Generates avatar URLs for users without custom avatars.
 * Supports multiple avatar services:
 * - UI Avatars (initials-based, colorful)
 * - Gravatar (email-based, global avatars)
 *
 * @package App\Methods
 */
class AvatarGenerator
{
    /**
     * Generate an initials-based avatar URL using UI Avatars service
     *
     * Creates colorful avatars with user initials.
     * Falls back to single letter if no name provided.
     *
     * @param string|null $firstname First name
     * @param string|null $lastname Last name
     * @param string|null $username Username (fallback if no name)
     * @param string|null $email Email (fallback if no name/username)
     * @param int $size Avatar size in pixels (default: 200)
     * @return string Avatar URL
     */
    public static function initials(
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $username = null,
        ?string $email = null,
        int $size = 200
    ): string {
        $initials = '';

        if ($firstname && $lastname) {
            // Use first letter of firstname + first letter of lastname
            $initials = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
        } elseif ($username) {
            // Use first two letters of username
            $initials = strtoupper(substr($username, 0, 2));
        } elseif ($email) {
            // Use first two letters of email (before @)
            $emailPrefix = explode('@', $email)[0];
            $initials = strtoupper(substr($emailPrefix, 0, 2));
        } else {
            $initials = 'U'; // Unknown user
        }

        return "https://ui-avatars.com/api/?name={$initials}&size={$size}&background=random&color=fff&bold=true";
    }

    /**
     * Generate a Gravatar URL
     *
     * Uses email to fetch avatar from Gravatar service.
     * Shows mystery person icon if no Gravatar exists.
     *
     * @param string|null $email Email address
     * @param int $size Avatar size in pixels (default: 200)
     * @return string Gravatar URL
     */
    public static function gravatar(?string $email = null, int $size = 200): string
    {
        $avatar = "https://www.gravatar.com/avatar";

        if ($email) {
            $avatar = $avatar . "/" . md5(strtolower(trim($email)));
        }

        return $avatar . "?d=mp&s={$size}";
    }
}


















