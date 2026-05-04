<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RefundStatus;
use App\Traits\CanBeArchived;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Builder;

/**
 * Refund Model
 *
 * Manages refund requests between buyers and sellers.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property int $purchase_id
 * @property string $subject
 * @property RefundStatus $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 *
 * @property-read User $user
 * @property-read User $seller
 * @property-read Purchase $purchase
 * @property-read \Illuminate\Database\Eloquent\Collection<RefundReply> $replies
 */
class Refund extends Model
{
    use HasFactory, CanBeArchived;

    protected $table = 'refunds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'seller_id',
        'purchase_id',
        'subject',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'restored_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include pending refunds.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::PENDING);
    }

    /**
     * Check if the refund is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === RefundStatus::PENDING;
    }

    /**
     * Scope a query to only include accepted refunds.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::ACCEPTED);
    }

    /**
     * Check if the refund is accepted.
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->status === RefundStatus::ACCEPTED;
    }

    /**
     * Scope a query to only include declined refunds.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDeclined(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::DECLINED);
    }

    /**
     * Check if the refund is declined.
     *
     * @return bool
     */
    public function isDeclined(): bool
    {
        return $this->status === RefundStatus::DECLINED;
    }

    /**
     * Scope a query to only include cancelled refunds.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::CANCELLED);
    }

    /**
     * Check if the refund is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === RefundStatus::CANCELLED;
    }

    /**
     * Check if the refund can be cancelled by the user.
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return $this->isPending();
    }

    /**
     * Check if the refund can be deleted by the user.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->isAccepted() || $this->isDeclined() || $this->isCancelled();
    }

    /**
     * Accept the refund request.
     *
     * @return bool
     */
    public function accept(): bool
    {
        return $this->update(['status' => RefundStatus::ACCEPTED]);
    }

    /**
     * Decline the refund request.
     *
     * @return bool
     */
    public function decline(): bool
    {
        return $this->update(['status' => RefundStatus::DECLINED]);
    }

    /**
     * Cancel the refund request.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        return $this->update(['status' => RefundStatus::CANCELLED]);
    }

    /**
     * Get all status options.
     *
     * @return array<int, string>
     */
    public static function getStatusOptions(): array
    {
        return RefundStatus::options();
    }

    /**
     * Get the status label.
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
     * Get the status badge class for UI display.
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
     * Get the status icon.
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
     * Get the user that owns the refund request.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the seller associated with the refund.
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the purchase associated with the refund.
     *
     * @return BelongsTo
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get all replies for the refund.
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(RefundReply::class);
    }
}


















