<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use App\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class EmailOtpNotification
 *
 * Universal notification for sending OTP codes for registration, password reset, and email change.
 */
class EmailOtpNotification extends BaseNotification
{
    private string $otp;
    private string $purpose;

    /**
     * @param User $user
     * @param string $otp
     * @param string $purpose ['registration', 'password_reset', 'email_change']
     */
    public function __construct(User $user, string $otp, string $purpose = 'registration')
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->purpose = $purpose;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function getNotificationPreference(): string
    {
        return match ($this->purpose) {
            'password_reset' => 'password_reset',
            default => 'email_verification',
        };
    }

    public function toMail($notifiable): MailMessage
    {
        // Map purpose to mail template alias
        $alias = match ($this->purpose) {
            'password_reset' => 'password_reset_otp',
            'email_change' => 'email_change_otp',
            default => 'registration_otp',
        };

        $mailTemplate = mailTemplate($alias);

        // If template doesn't exist for some reason, use registration_otp as fallback
        if (!$mailTemplate) {
            $mailTemplate = mailTemplate('registration_otp');
        }

        $shortCodes = [
            'otp' => $this->otp,
            'user_name' => $this->user->full_name,
            'expiry_minutes' => '5',
            'site_name' => getSiteName(),
        ];

        // Replace shortcodes in subject and content
        $subject = $this->replaceShortCode($mailTemplate->subject, $shortCodes);
        $body = $this->replaceShortCode($mailTemplate->content, $shortCodes);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.default', ['body' => $body]);
    }

    /**
     * Get the array representation of the notification.
     * This is will be use in futute for in-app notification
     * @param mixed $notifiable
     * @return array|null
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'email_verification',
            'title' => 'Email Verification Required',
            'message' => "A verification code has been sent to your email address",
            'action_url' => route('verification.notice'),
            'user_id' => $this->user->id,
            'timestamp' => now()->toISOString(),
            'icon' => 'link-45deg',
            'color' => 'info'
        ];
    }
}
