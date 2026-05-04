<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string|null $image
 * @property string|null $link
 * @property bool $is_unread
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AdminNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'image',
        'link',
        'is_unread',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_unread' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread(Builder $query): void
    {
        $query->where('is_unread', true);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead(Builder $query): void
    {
        $query->where('is_unread', false);
    }

    /**
     * Determine if the notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->is_unread === true;
    }

    /**
     * Determine if the notification is read.
     */
    public function isRead(): bool
    {
        return $this->is_unread === false;
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_unread' => false]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_unread' => true]);
    }
}
