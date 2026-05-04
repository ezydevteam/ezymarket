<?php

namespace App\Notifications;

use App\Models\Premium\Premium;

class PremiumNotification extends BaseNotification
{
    public $premium;
    public $status; // 'expiring_soon' or 'expired'

    public function __construct(Premium $premium, string $status)
    {
        $this->premium = $premium;
        $this->user = $premium->user;
        $this->status = $status;
    }

    public function getNotificationPreference(): string
    {
        return $this->status === 'expiring_soon'
            ? 'premium_about_to_expire'
            : 'premium_expired';
    }

    public function toArray($notifiable)
    {
        $data = [
            'premium_id' => $this->premium->id,
            'package_name' => $this->premium->plan->name ?? 'Membership',
            'expiry_date' => dateFormat($this->premium->expiry_at),
            'timestamp' => now()->toISOString(),
        ];

        switch ($this->status) {
            case 'expiring_soon':
                $data['type'] = 'premium_expiring';
                $data['title'] = 'Premium Membership Expiring Soon';
                $data['message'] = "Your premium membership will expire on " . dateFormat($this->premium->expiry_at);
                $data['action_url'] = route('user.settings.premium');
                $data['icon'] = 'clock';
                $data['color'] = 'warning';
                break;

            case 'expired':
                $data['type'] = 'premium_expired';
                $data['title'] = 'Premium Membership Expired';
                $data['message'] = "Your premium membership has expired on " . dateFormat($this->premium->expiry_at);
                $data['action_url'] = route('user.settings.premium');
                $data['icon'] = 'x-circle';
                $data['color'] = 'error';
                break;
        }

        return $data;
    }

    public function getEmailData()
    {
        $user = $this->premium->user;

        $baseData = [
            'username' => $user->full_name,
            'expiry_date' => dateFormat($this->premium->expiry_at),
            'renewing_link' => route('user.settings.premium'),
            'website_name' => @settings('general')->site_name,
        ];

        switch ($this->status) {
            case 'expiring_soon':
                return [
                    'template' => 'premium_about_to_expire',
                    'shortcodes' => array_merge($baseData, [
                        'days_remaining' => $this->premium->expiry_at->diffInDays(now()),
                        'package_name' => $this->premium->plan->name ?? 'Membership',
                    ])
                ];

            case 'expired':
                return [
                    'template' => 'premium_expired',
                    'shortcodes' => array_merge($baseData, [
                        'package_name' => $this->premium->plan->name ?? 'Membership',
                    ])
                ];

            default:
                return null;
        }
    }
}
