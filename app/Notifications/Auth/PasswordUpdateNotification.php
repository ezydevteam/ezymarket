<?php

namespace App\Notifications\Auth;

use App\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordUpdateNotification extends BaseNotification
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function getNotificationPreference(): string
    {
        return 'password_update';
    }

    public function toMail($notifiable)
    {
        $mailTemplate = mailTemplate('password_update');

        if (!$mailTemplate){
            return null;
        }

        $shortCodes = [
            'username' => $this->user->full_name,
            'message' => "Your account password has been updated successfully. If you didn't make this change, please contact support immediately",
            //'date_time' => null,
            'action_url' => route('user.settings.password'),
            'website_name' => @settings('general')->site_name,
        ];

        $subject = $this->replaceShortCode($mailTemplate->subject, $shortCodes);
        $body = $this->replaceShortCode($mailTemplate->content, $shortCodes);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.default', ['body' => $body]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'password_update',
            'title' => 'Password Changed',
            'message' => "Your account password has been updated successfully. If you didn't make this change, please contact support immediately",
            'user_id' => $this->user->id,
            'action_url' => route('user.settings.password'),
            'timestamp' => now()->toISOString(),
            'icon' => 'shield-check',
            'color' => 'success'
        ];
    }
}

















