<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Product\Product;
use App\Models\Product\ProductChangeLog;

class ProductChangeLogNotification extends BaseNotification
{
    public $changelog;
    public $user;
    public $product;

    public function __construct(User $user, Product $product, ProductChangeLog $changelog)
    {
        $this->user = $user;
        $this->product = $product;
        $this->changelog = $changelog;
    }

    public function getNotificationPreference(): string
    {
        return 'product_changelog';
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product_changelog',
            'title' => 'Version Updated',
            'message' => "'{$this->product->name}' is updated to version {$this->changelog->version}",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'preview_image' => $this->product->thumbnail_url,
            'version' => $this->changelog->version,
            'change_log_body' => $this->changelog->body,
            'action_url' => $this->product->view_link,
            'timestamp' => now()->toISOString(),
            'icon' => 'file-text',
            'color' => 'info'
        ];
    }

    public function getEmailData()
    {
        return [
            'template' => 'product_changelog_update',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'user_email' => $this->user->email,
                'message' => "'{$this->product->name}' is updated to version {$this->changelog->version}",
                'product_name' => $this->product->name,
                'preview_image' => $this->product->thumbnail_url,
                'product_version' => $this->changelog->version,
                'changelog_body' => $this->changelog->body,
                'view_link' => $this->product->view_link,
                'website_name' => @settings('general')->site_name,
                'website_url' => @settings('general')->site_url,
            ]
        ];
    }
}
