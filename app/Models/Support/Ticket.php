<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Enums\TicketStatus;
use App\Traits\CanBeArchived;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ticket Model
 *
 * Represents a support ticket in the system.
 *
 * @property int $id
 * @property int $user_id
 * @property int $ticket_category_id
 * @property string $subject
 * @property TicketStatus $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read TicketCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection|TicketReply[] $replies
 * @property-read string $status_name
 * @property-read string $status_badge_class
 * @property-read string $status_icon
 */
class Ticket extends Model
{
    use HasFactory, CanBeArchived;

    protected $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ticket_category_id',
        'subject',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'ticket_category_id' => 'integer',
        'status' => TicketStatus::class,
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'status_name',
        'status_badge_class',
        'status_icon',
    ];

    /**
     * Boot the model and register event handlers.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::forceDeleted(function (Ticket $ticket): void {
            $driver = storageDriver();
            $disk = $driver ? $driver->alias : 'local';

            $path = "tickets/{$ticket->id}";

            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->deleteDirectory($path);
            }
        });
    }

    /**
     * Scope query to opened tickets.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOpened(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::OPENED->value);
    }

    /**
     * Scope query to closed tickets.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::CLOSED->value);
    }

    /**
     * Scope query to cancelled tickets.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::CANCELLED->value);
    }

    /**
     * Scope query to include ticket replies with attachments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithAttachments(Builder $query): Builder
    {
        return $query->with('replies.attachments');
    }

    /**
     * Check if ticket is opened.
     *
     * @return bool
     */
    public function isOpened(): bool
    {
        return $this->status === TicketStatus::OPENED;
    }

    /**
     * Check if ticket is closed.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->status === TicketStatus::CLOSED;
    }

    /**
     * Check if ticket is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === TicketStatus::CANCELLED;
    }

    /**
     * Check if the ticket can be cancelled by the user.
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return $this->isOpened();
    }

    /**
     * Check if the ticket can be deleted by the user.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->isClosed() || $this->isCancelled();
    }

    /**
     * Cancel the ticket.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        return $this->update(['status' => TicketStatus::CANCELLED]);
    }

    /**
     * Get the ticket's status name.
     *
     * @return Attribute
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label(),
        );
    }

    /**
     * Get the ticket's status badge class.
     *
     * @return Attribute
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badgeClass(),
        );
    }

    /**
     * Get the ticket's status icon.
     *
     * @return Attribute
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->icon(),
        );
    }

    /**
     * Get the user that owns the ticket.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticket category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    /**
     * Get all replies for the ticket.
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Get ticket status options.
     *
     * @return array<int, string>
     */
    public static function getStatusOptions(): array
    {
        return TicketStatus::options();
    }
}
