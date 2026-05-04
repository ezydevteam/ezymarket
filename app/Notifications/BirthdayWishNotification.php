<?php

namespace App\Notifications;

use App\Models\User;

class BirthdayWishNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'general';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'birthday',
            'title' => '🎂 ' . translate('Happy Birthday!'),
            'message' => translate("Wishing you a wonderful day filled with joy and happiness!"),
            'user_id' => $this->user->id,
            'action_url' => route('user.dashboard'),
            'timestamp' => now()->toISOString(),
            'icon' => 'gift',
            'color' => 'pink'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'birthday_wishes',
            'shortcodes' => [
                'user_name' => $this->user->full_name,
                'message' => translate("Wishing you a wonderful day filled with joy and happiness!"),
                'action_url' => route('user.dashboard'),
                'website_name' => @settings('general')->site_name,
                'website_url' => @settings('general')->site_url,
            ]
        ];
    }
}
