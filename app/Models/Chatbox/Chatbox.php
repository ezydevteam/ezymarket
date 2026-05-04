<?php

namespace App\Models\Chatbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};

class Chatbox extends Model
{
    protected $table = 'chatbox';

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'user_one_deleted_at',
        'user_two_deleted_at',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'user_one_deleted_at' => 'datetime',
            'user_two_deleted_at' => 'datetime',
            'last_message_at'      => 'datetime',
        ];
    }

    /* ---------- scopes & helpers ---------- */

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where(function ($subQ) use ($userId) {
                $subQ->where('user_one_id', $userId)
                     ->whereNull('user_one_deleted_at');
            })
            ->orWhere(function ($subQ) use ($userId) {
                $subQ->where('user_two_id', $userId)
                     ->whereNull('user_two_deleted_at');
            });
        });
    }

    public function scopeWithUnreadCount($query, $userId)
	{
		return $query->withCount(['messages as unread_count' => function($subQuery) use ($userId) {
			$subQuery->unreadFor($userId);
		}]);
	}

    public function scopeHasMessagesEfficient($query)
    {
        return $query->whereExists(function ($subQuery) {
            $subQuery->select('id')
                     ->from('chatbox_chats')
                     ->whereColumn('chatbox_chats.chatbox_id', 'chatbox.id')
                     ->where('is_deleted_by_sender', false);
        });
    }

    public function scopeHasMessages($query)
    {
        return $query->has('messages');
    }

    public function scopeWithMessageCount($query, $minCount = 1)
    {
        return $query->withCount([
            'messages' => function ($q) {
                $q->where('is_deleted_by_sender', false);
            }
        ])->having('messages_count', '>=', $minCount);
    }

    public function scopeOrderByRecentActivity($query, $direction = 'desc')
	{
		return $query->orderByRaw(
			'GREATEST(COALESCE(last_message_at, created_at), updated_at) ' . strtoupper($direction)
		);
	}

	public function getUnreadCountFor($userId)
	{
		return $this->messages()->unreadFor($userId)->count();
	}

	public function hasUnreadFor($userId)
	{
		return $this->messages()->unreadFor($userId)->exists();
	}

    public function getOtherUser(int $userId)
    {
        return $this->user_one_id === $userId ? $this->userTwo : $this->userOne;
    }

    public function wasRecentlyReactivated(): bool
    {
        $recentlyReactivated = $this->updated_at && $this->updated_at->diffInHours(now()) < 1;
        $hasDeletedTimestamps = $this->user_one_deleted_at || $this->user_two_deleted_at;

        return $recentlyReactivated && $hasDeletedTimestamps;
    }

    /* ---------- relationships ---------- */

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatboxChat::class, 'chatbox_id')
                    ->latestOfMany()
                    ->where('is_deleted_by_sender', false);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatboxChat::class, 'chatbox_id');
    }
}
