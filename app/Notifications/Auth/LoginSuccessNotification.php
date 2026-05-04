<?php

namespace App\Notifications\Auth;

use App\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class LoginSuccessNotification extends BaseNotification
{

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
        return 'login_success';
    }

    public function toMail($notifiable)
    {
        $mailTemplate = mailTemplate('account_login_success');

        $shortCodes = [
            'username' => $this->user->full_name,
            'message' => "You've successfully logged in",
            'action_url' => $this->user->profile_link,
            //'date_time' => null,
            'website_name' => @settings('general')->site_name,
        ];

        if (!$mailTemplate) {
            return (new MailMessage)
                ->subject('Login Successful - ' . @settings('general')->site_name)
                ->line("You've successfully logged in");
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
            'type' => 'login_success',
            'title' => 'Login Successful',
            'message' => "You've successfully logged in",
            'user_id' => $this->user->id,
            'action_url' => $this->user->profile_link,
            'timestamp' => now()->toISOString(),
            'icon' => 'box-arrow-in-right',
            'color' => 'success'
        ];
    }
}
