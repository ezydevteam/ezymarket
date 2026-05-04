<?php

declare(strict_types=1);

namespace App\Concerns;

/**
 * HasSocialAuth Trait
 *
 * Provides social authentication functionality for User models.
 * Handles Facebook and Google OAuth integration.
 *
 * @package App\Concerns
 */
trait HasSocialAuth
{
    /**
     * Check if user has a Facebook account linked.
     *
     * @return bool
     */
    public function hasFacebookAccount(): bool
    {
        return filled($this->facebook_id);
    }

    /**
     * Check if user has a Google account linked.
     *
     * @return bool
     */
    public function hasGoogleAccount(): bool
    {
        return filled($this->google_id);
    }

    /**
     * Check if user has any social account linked.
     *
     * @return bool
     */
    public function hasSocialAccount(): bool
    {
        return $this->hasFacebookAccount() || $this->hasGoogleAccount();
    }

    /**
     * Get the social provider(s) linked to this user.
     *
     * @return array<string>
     */
    public function getSocialProviders(): array
    {
        $providers = [];

        if ($this->hasFacebookAccount()) {
            $providers[] = 'facebook';
        }

        if ($this->hasGoogleAccount()) {
            $providers[] = 'google';
        }

        return $providers;
    }

    /**
     * Check if user registered via social authentication.
     *
     * @return bool
     */
    public function isRegisteredViaSocial(): bool
    {
        // User is considered social registered if they have a social ID
        // but no password set (or it's empty)
        return $this->hasSocialAccount() && blank($this->password);
    }

    /**
     * Link a Facebook account to this user.
     *
     * @param string $facebookId
     * @return bool
     */
    public function linkFacebookAccount(string $facebookId): bool
    {
        $this->facebook_id = $facebookId;
        return $this->save();
    }

    /**
     * Link a Google account to this user.
     *
     * @param string $googleId
     * @return bool
     */
    public function linkGoogleAccount(string $googleId): bool
    {
        $this->google_id = $googleId;
        return $this->save();
    }

    /**
     * Unlink Facebook account from this user.
     *
     * @return bool
     */
    public function unlinkFacebookAccount(): bool
    {
        $this->facebook_id = null;
        return $this->save();
    }

    /**
     * Unlink Google account from this user.
     *
     * @return bool
     */
    public function unlinkGoogleAccount(): bool
    {
        $this->google_id = null;
        return $this->save();
    }

    /**
     * Unlink all social accounts from this user.
     *
     * @return bool
     */
    public function unlinkAllSocialAccounts(): bool
    {
        $this->facebook_id = null;
        $this->google_id = null;
        return $this->save();
    }

    /**
     * Check if user can unlink a specific social provider.
     * User must have either a password or another social account linked.
     *
     * @param string $provider ('facebook' or 'google')
     * @return bool
     */
    public function canUnlinkSocialAccount(string $provider): bool
    {
        // User must have a way to log in after unlinking
        // Either password is set OR another social account exists
        $hasPassword = filled($this->password);

        if ($provider === 'facebook') {
            return $hasPassword || $this->hasGoogleAccount();
        }

        if ($provider === 'google') {
            return $hasPassword || $this->hasFacebookAccount();
        }

        return false;
    }

    /**
     * Get user's social profile links if available.
     *
     * @return array
     */
    public function getSocialLinks(): array
    {
        $basicInfo = $this->basic_info ?? [];
        $socialKeys = ['facebook', 'x', 'youtube', 'linkedin', 'instagram', 'pinterest'];
        return array_intersect_key($basicInfo, array_flip($socialKeys));
    }

    /**
     * Check if user has social profile links.
     *
     * @return bool
     */
    public function hasSocialLinks(): bool
    {
        return !empty($this->getSocialLinks());
    }

    /**
     * Update user's social profile links.
     *
     * @param array<string, string> $links
     * @return bool
     */
    public function updateSocialLinks(array $links): bool
    {
        $basicInfo = $this->basic_info ?? [];
        $basicInfo = array_merge($basicInfo, $links);
        $this->basic_info = $basicInfo;
        return $this->save();
    }

    /**
     * Get a specific social link by platform.
     *
     * @param string $platform (e.g., 'twitter', 'linkedin', 'instagram')
     * @return string|null
     */
    public function getSocialLink(string $platform): ?string
    {
        $basicInfo = $this->basic_info ?? [];
        return $basicInfo[$platform] ?? null;
    }

    /**
     * Add or update a specific social link.
     *
     * @param string $platform
     * @param string $url
     * @return bool
     */
    public function addSocialLink(string $platform, string $url): bool
    {
        $basicInfo = $this->basic_info ?? [];
        $basicInfo[$platform] = $url;
        $this->basic_info = $basicInfo;
        return $this->save();
    }

    /**
     * Remove a specific social link.
     *
     * @param string $platform
     * @return bool
     */
    public function removeSocialLink(string $platform): bool
    {
        $basicInfo = $this->basic_info ?? [];
        unset($basicInfo[$platform]);
        $this->basic_info = $basicInfo;
        return $this->save();
    }
}
