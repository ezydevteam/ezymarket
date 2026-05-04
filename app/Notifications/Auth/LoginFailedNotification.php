<?php

namespace App\Notifications\Auth;

use App\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class LoginFailedNotification extends BaseNotification
{
    public static $createUrlCallback;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    // for immediate broadcasting
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function getNotificationPreference(): string
    {
        return 'login_failed';
    }

    public function toMail($notifiable)
    {
        $mailTemplate = mailTemplate('account_login_attempt');

        $shortCodes = [
            'user_username' => $this->user->full_name,
            'message' => "Someone tried to login your account. If that wasn't you, change your account password immediately",
            'action_url' => route('user.settings.password'),
            //'date_time' => null,
            'website_name' => @settings('general')->site_name,
        ];

        if (!$mailTemplate) {
            return (new MailMessage)
                ->subject('Login Attempt - ' . @settings('general')->site_name)
                ->line("Someone tried to login your account. If that wasn't you, change your account password immediately");
        }

        $subject = $this->replaceShortCode($mailTemplate->subject, $shortCodes);
        $body = $this->replaceShortCode($mailTemplate->content, $shortCodes);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.default', ['body' => $body]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'login_failed',
            'title' => 'Login Attempt',
            'message' => "Someone tried to login your account. If that wasn't you, change your account password immediately",
            'user_id' => $this->user->id,
            'action_url' => route('user.settings.2fa'),
            'timestamp' => now()->toISOString(),
            'icon' => 'exclamation',
            'color' => 'warning'
        ];
    }
}
