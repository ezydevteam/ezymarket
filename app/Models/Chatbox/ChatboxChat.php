<?php

namespace App\Models\Chatbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

class ChatboxChat extends Model
{
    protected $table = 'chatbox_chats';

    protected $fillable = [
        'chatbox_id',
        'sender_id',
        'content',
        'type',
        'is_deleted_by_sender',
        'is_filtered',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted_by_sender' => 'boolean',
            'is_filtered'          => 'boolean',
            'read_at'              => 'datetime',
        ];
    }

    /* ---------- scopes ---------- */

    public function scopeNotDeleted($q)
    {
        return $q->where('is_deleted_by_sender', false);
    }

    public function scopeNotFiltered($q)
    {
        return $q->where('is_filtered', false);
    }

    public function scopeUnreadFor($query, $userId)
	{
		return $query->where('sender_id', '!=', $userId)
					 ->whereNull('read_at');
	}

	public function isUnreadFor($userId)
	{
		return $this->sender_id != $userId && is_null($this->read_at);
	}

    /* ---------- relationships ---------- */
    public function chatboxModel(): BelongsTo
    {
        return $this->belongsTo(Chatbox::class, 'chatbox_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Chatbox::class, 'chatbox_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
