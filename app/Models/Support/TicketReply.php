<?php

namespace App\Models\Support;

use App\Models\{Admin, User};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'user_id',
        'admin_id',
        'ticket_id',
    ];

    protected $touches = ['ticket'];

    /**
     * Get the user who created the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created the reply.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the ticket this reply belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get all attachments for this reply.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketReplyAttachment::class);
    }

    /**
     * Check if reply has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Check if reply is from admin.
     */
    public function isFromAdmin(): bool
    {
        return !is_null($this->admin_id);
    }

    /**
     * Check if reply is from user.
     */
    public function isFromUser(): bool
    {
        return !is_null($this->user_id);
    }
}
