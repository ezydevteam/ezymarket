<?php

namespace App\Notifications;

use App\Models\User;

class FollowerNotification extends BaseNotification
{
    public $follower;
    
    public function __construct(User $following, User $follower)
    {   
        $this->user = $following;
        $this->follower = $follower;
    }
    
    public function getNotificationPreference(): string
    {
        return 'new_follower';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_follower',
            'title' => 'New Follower',
            'message' => "{$this->follower->full_name} started following you",
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->full_name,
            'follower_username' => $this->follower->username,
            'preview_image' => $this->follower->avatar_url,
            'action_url' => $this->follower->profile_link,
            'timestamp' => now()->toISOString(),
            'icon' => 'person-check',
            'color' => 'success'
        ];
    }
    
    public function getEmailData()
    {
        return [
            'template' => 'following_update',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'follower_email' => $this->follower->email,
                'message' => "{$this->follower->full_name} started following you",
                'view_link' => $this->follower->profile_link,
                'website_name' => @settings('general')->site_name,
                'website_url' => @settings('general')->site_url,
            ]
        ];
    }
}


















