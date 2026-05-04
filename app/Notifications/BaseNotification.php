<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;

abstract class BaseNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $product;
    protected $user;
    protected $notifiableUser;

    /**
     * Get the notification's delivery channels.
     * Only handles real-time channels
     */
    public function via($notifiable)
    {
        $this->notifiableUser = $notifiable;

        // Check if notification should be sent at all
        if (!$this->shouldSendNotification($notifiable)) {
            return [];
        }

        $preference = $this->getNotificationPreference();
        $channels = [];

        // Check preferences if method exists
        if (method_exists($notifiable, 'wantsNotification')) {
            // Check in-app preference (controls database storage)
            if ($notifiable->wantsNotification('in_app', $preference)) {
                $channels[] = 'database';
            }

            // Check push preference (controls broadcast for push notifications)
            if ($notifiable->wantsNotification('push', $preference)) {
                $channels[] = 'broadcast';
            }
        } else {
            // Default channels
            $channels[] = 'database';
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Global check if notification should be sent to this user
     */
    protected function shouldSendNotification($notifiable)
    {
        // Check if user is suspended
        if (method_exists($notifiable, 'isSuspended') && $notifiable->isSuspended()) {
            return false;
        }

        // Check if user account is deleted
        if (method_exists($notifiable, 'trashed') && $notifiable->trashed()) {
            return false;
        }

        // Check if user is active
        if (property_exists($notifiable, 'status') && $notifiable->status === 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        $targetUser = $this->notifiableUser ?? $this->user;

        if ($targetUser && isset($targetUser->username)) {
            return new PrivateChannel("user-{$targetUser->username}");
        }

        return new PrivateChannel("notifications");
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs()
    {
        return 'notification';
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        $data = $this->toArray($notifiable);

        // Add notification ID if not present
        if (!isset($data['id'])) {
            $data['id'] = uniqid('notif_');
        }

        // Add push notification flag
        $data['show_push'] = $this->shouldShowPush($notifiable);

        // Add push notification metadata
        $data['push_notification'] = $this->getPushNotificationData($data);

        // Add timestamp if not present
        if (!isset($data['timestamp'])) {
            $data['timestamp'] = now()->toISOString();
        }

        return $data;
    }

    /**
     * Determine if push notification should be shown
     */
    protected function shouldShowPush($notifiable)
    {
        if (method_exists($notifiable, 'wantsNotification')) {
            $preference = $this->getNotificationPreference();
            return $notifiable->wantsNotification('push', $preference);
        }

        return false;
    }

    /**
     * Get push notification metadata
     */
    protected function getPushNotificationData($data)
    {
        return [
            'title' => $data['title'] ?? 'New Notification',
            'body' => $data['message'] ?? '',
            'icon' => $data['preview_image'] ?? asset('themes/main/images/notification.png'),
            'badge' => asset('themes/main/images/favicon.png'),
            'tag' => 'notification-' . ($data['id'] ?? uniqid()),
            'requireInteraction' => false,
            'silent' => false,
            'data' => [
                'url' => $data['action_url'] ?? '/',
                'type' => $data['type'] ?? 'notification',
                'notification_id' => $data['id'] ?? null
            ]
        ];
    }

    public function replaceShortCode($content, $short_codes)
    {
        foreach ($short_codes as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        return $content;
    }

    /**
     * Get the notification event name for preferences
     */
    abstract public function getNotificationPreference(): string;
}
