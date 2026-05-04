<?php

namespace App\Notifications\Auth;

use App\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class EmailUpdateNotification extends BaseNotification
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
        return 'email_update';
    }

    public function toMail($notifiable)
    {
        $mailTemplate = mailTemplate('email_update');

        $shortCodes = [
            'username' => $this->user->full_name,
            'user_email' => $this->user->email,
            'message' => "Your email address has been updated to {$this->user->email}. If you didn't make this change, please contact support immediately",
            'view_link' => route('user.settings.index'),
            'website_name' => @settings('general')->site_name,
            'website_url' => @settings('general')->site_url,
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
            'type' => 'email_update',
            'title' => 'Email Updated',
            'message' => "Your email address has been updated to {$this->user->email}. If you didn't make this change, please contact support immediately",
            'user_id' => $this->user->id,
            'action_url' => route('user.settings.index'),
            'timestamp' => now()->toISOString(),
            'icon' => 'envelope',
            'color' => 'success'
        ];
    }
}

















