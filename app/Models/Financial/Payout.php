<?php

namespace App\Models\Financial;

use App\Enums\PayoutStatus;
use App\Models\User;
use App\Models\Financial\PayoutMethod;
use App\Traits\CanBeArchived;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $seller_id
 * @property int|null $payout_method_id
 * @property float $amount
 * @property float $fees
 * @property string $method
 * @property string $account
 * @property string|null $admin_note
 * @property PayoutStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Payout extends Model
{
    use HasFactory, CanBeArchived;

    /**
     * The table associated with the model.
     */
    protected $table = 'payouts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seller_id',
        'payout_method_id',
        'amount',
        'fees',
        'method',
        'account',
        'admin_note',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'status' => PayoutStatus::class,
            'restored_at' => 'datetime',
        ];
    }

    // ============================================
    // Relationships
    // ============================================

    /**
     * Get the seller that owns the payout.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Alias for seller relationship.
     */
    public function user(): BelongsTo
    {
        return $this->seller();
    }

    /**
     * Get the payout method for this payout.
     */
    public function payoutMethod(): BelongsTo
    {
        return $this->belongsTo(PayoutMethod::class);
    }

    // ============================================
    // Query Scopes
    // ============================================

    /**
     * Scope a query to only include pending payouts.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', PayoutStatus::PENDING);
    }

    /**
     * Scope a query to only include returned payouts.
     */
    public function scopeReturned(Builder $query): void
    {
        $query->where('status', PayoutStatus::RETURNED);
    }

    /**
     * Scope a query to only include approved payouts.
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', PayoutStatus::APPROVED);
    }

    /**
     * Scope a query to only include completed payouts.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', PayoutStatus::COMPLETED);
    }

    /**
     * Scope a query to only include cancelled payouts.
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', PayoutStatus::CANCELLED);
    }

    // ============================================
    // Status Checkers
    // ============================================

    /**
     * Check if payout is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PayoutStatus::PENDING;
    }

    /**
     * Check if payout is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === PayoutStatus::RETURNED;
    }

    /**
     * Check if payout is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === PayoutStatus::APPROVED;
    }

    /**
     * Check if payout is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PayoutStatus::COMPLETED;
    }

    /**
     * Check if the payout is recalled.
     */
    public function isRecalled(): bool
    {
        return $this->status === PayoutStatus::RECALLED;
    }

    /**
     * Check if payout is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === PayoutStatus::CANCELLED;
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Get all available status options.
     */
    public static function getStatusOptions(): array
    {
        return PayoutStatus::labels();
    }

    /**
     * Get the status name for the current payout.
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badge(),
        );
    }

    /**
     * Get the fees label for the current payout.
     */
    protected function feesLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->isCompleted()) {
                    return translate('N/A');
                }
                return $this->fees > 0 ? getAmount($this->fees) : translate('Free');
            }
        );
    }

    /**
     * Get the amount information (value and dynamic label) based on status.
     *
     * @return Attribute
     */
    protected function amountInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                $status = $this->status;
                $amount = $status === PayoutStatus::COMPLETED
                    ? $this->net_amount
                    : $this->amount;

                $label = match ($status) {
                    PayoutStatus::COMPLETED => translate('Received'),
                    PayoutStatus::APPROVED  => translate('Processing'),
                    PayoutStatus::RETURNED  => translate('Disbursed'),
                    PayoutStatus::CANCELLED => translate('Deducted'),
                    PayoutStatus::RECALLED  => translate('Reversed'),
                    default => translate('Requested'),
                };

                return (object) [
                    'amount' => getAmount($amount),
                    'label' => $label,
                ];
            }
        );
    }

    /**
     * Get the payout method label for the current payout.
     */
    protected function payoutMethodLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->method ?: translate('N/A'),
        );
    }

    /**
     * Get the net amount (amount - fees).
     */
    protected function netAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->amount - $this->fees,
        );
    }
}
