<?php

namespace App\Notifications;

use App\Models\User;

class BecomeSellerNotification extends BaseNotification
{

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'become_seller';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'become_seller',
            'title' => 'Congratulations!',
            'message' => "You are now a seller on DiziPlace, {$this->user->full_name}!",
            'user_id' => $this->user->id,
            'action_url' => route('user.dashboard'),
            'timestamp' => now()->toISOString(),
            'icon' => 'shop',
            'color' => 'success'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'become_seller',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'view_link' => route('user.product.index'),
                'website_name' => @settings('general')->site_name,
                'website_url' => @settings('general')->site_url,
            ]
        ];
    }
}
