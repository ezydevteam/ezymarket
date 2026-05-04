<?php

namespace App\Notifications;

use App\Enums\BadgeAlias;
use App\Models\Badge;
use App\Models\User;

class BadgeChangeNotification extends BaseNotification
{
    public $badge;
    public $changeType;

    public function __construct(Badge $badge, User $user, $changeType = 'new')
    {
        $this->badge = $badge;
        $this->user = $user;
        $this->changeType = $changeType;
    }

    public function getNotificationPreference(): string
    {
        return 'new_badge';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'badge_' . $this->changeType,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_title' => $this->badge->full_title,
            'badge_alias' => $this->badge->alias,
            'preview_image' => $this->badge->image_url,
            'change_type' => $this->changeType,
            'action_url' => route('user.settings.badges'),
            'timestamp' => now()->toISOString(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'badge_changed',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'subject' => $this->getTitle(),
                'message' => $this->getMessage(),
                'badge_name' => $this->badge->name,
                'badge_title' => $this->badge->full_title,
                'badge_image' => $this->badge->image_url,
                'change_type' => $this->changeType,
                'view_link' => route('user.settings.badges'),
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }

    private function getTitle()
    {
        switch ($this->changeType) {
            case 'new':
                return 'New Badge Awarded!';
            case 'upgrade':
                return 'Badge Upgraded!';
            case 'downgrade':
                return 'Badge Changed';
            case 'removed':
                return 'Badge Removed';
            case 'updated':
                return 'Badge Updated';
            default:
                return 'Badge Changed';
        }
    }

    private function getMessage()
    {
        switch ($this->changeType) {
            case 'new':
                return $this->getNewBadgeMessage();
            case 'upgrade':
                return $this->getUpgradeMessage();
            case 'downgrade':
                return $this->getDowngradeMessage();
            case 'removed':
                return $this->getRemovedMessage();
            case 'updated':
                return $this->getUpdatedMessage();
            default:
                return $this->getNewBadgeMessage();
        }
    }

    private function getNewBadgeMessage()
    {
        switch ($this->badge->alias) {
            case BadgeAlias::VERIFIED_ACCOUNT:
                return "Congratulations! Your account has been verified";
            case BadgeAlias::EXCLUSIVE_SELLER:
                return "Congratulations! You've earned the 'Exclusive Seller' badge";
            default:
                return "Congratulations! You've earned the '{$this->badge->name}' badge";
        }
    }

    private function getUpgradeMessage()
	{
		switch ($this->badge->alias) {
			case BadgeAlias::SELLER_LEVEL:
				return "Congratulations! Your seller level has been upgraded to '{$this->badge->name}'!";
			case BadgeAlias::MEMBERSHIP_YEARS:
				return "Congratulations! Your membership badge has been upgraded to '{$this->badge->name}'!";
			default:
				return "Your badge has been upgraded to '{$this->badge->name}'!";
		}
	}

	private function getDowngradeMessage()
	{
		switch ($this->badge->alias) {
			case BadgeAlias::SELLER_LEVEL:
				return "Your seller level has been changed to '{$this->badge->name}'";
			case BadgeAlias::MEMBERSHIP_YEARS:
				return "Your membership badge has been changed to '{$this->badge->name}'";
			default:
				return "Your badge has been changed to '{$this->badge->name}'";
		}
	}

    private function getRemovedMessage()
    {
        return "Your '{$this->badge->name}' badge has been removed";
    }

    private function getUpdatedMessage()
    {
        return "Your badge has been updated to '{$this->badge->name}'";
    }

    private function getIcon()
    {
        switch ($this->changeType) {
            case 'new':
            case 'upgrade':
                return 'star-fill';
            case 'downgrade':
                return 'arrow-down-circle';
            case 'removed':
                return 'x-circle';
            case 'updated':
                return 'arrow-repeat';
            default:
                return 'star';
        }
    }

    private function getColor()
    {
        switch ($this->changeType) {
            case 'new':
            case 'upgrade':
                return 'success';
            case 'downgrade':
                return 'warning';
            case 'removed':
                return 'danger';
            case 'updated':
                return 'info';
            default:
                return 'info';
        }
    }
}

















