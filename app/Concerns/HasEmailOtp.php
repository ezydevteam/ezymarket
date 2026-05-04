<?php

declare(strict_types=1);

namespace App\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Trait HasEmailOtp
 *
 * Provides shared OTP logic for email verification, password reset, and email change flows.
 */
trait HasEmailOtp
{
    /**
     * Generate a new 6-digit OTP, hash it, and store it with 5-minute expiry.
     *
     * @return string The plaintext OTP (to be sent via email)
     */
    public function generateEmailOtp(): string
    {
        $otp = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_otp' => Hash::make($otp),
            'email_otp_expires_at' => Carbon::now()->addMinutes(5),
        ])->save();

        return $otp;
    }

    /**
     * Verify if the provided OTP matches the hashed version and is not expired.
     *
     * @param string $otp
     * @return bool
     */
    public function verifyEmailOtp(string $otp): bool
    {
        if (!$this->email_otp || !$this->email_otp_expires_at) {
            return false;
        }

        if (Carbon::now()->isAfter($this->email_otp_expires_at)) {
            $this->clearEmailOtp();
            return false;
        }

        if (Hash::check($otp, $this->email_otp)) {
            return true;
        }

        return false;
    }

    /**
     * Clear OTP fields from the user.
     */
    public function clearEmailOtp(): void
    {
        $this->forceFill([
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ])->save();
    }

    /**
     * Check if the user has a valid, non-expired OTP.
     */
    public function hasValidOtp(): bool
    {
        return $this->email_otp && $this->email_otp_expires_at && Carbon::now()->isBefore($this->email_otp_expires_at);
    }
}
