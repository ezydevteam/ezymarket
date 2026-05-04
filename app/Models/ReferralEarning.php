<?php

namespace App\Models;

use App\Enums\ReferralEarningStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralEarning Model
 *
 * @property int $id
 * @property int $referral_id
 * @property int $seller_id
 * @property int $sale_id
 * @property float $seller_earning
 * @property ReferralEarningStatus $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Referral $referral
 * @property-read User $seller
 * @property-read Sale $sale
 */
class ReferralEarning extends Model
{
    use HasFactory;

    protected $table = 'referral_earnings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'referral_id',
        'seller_id',
        'sale_id',
        'seller_earning',
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
            'status' => ReferralEarningStatus::class,
            'seller_earning' => 'decimal:2',
        ];
    }

    /* ---------------------- Scopes ---------------------- */

    /**
     * Scope to filter active referral earnings
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ReferralEarningStatus::ACTIVE);
    }

    /**
     * Scope to filter refunded referral earnings
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', ReferralEarningStatus::REFUNDED);
    }

    /**
     * Scope to filter cancelled referral earnings
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', ReferralEarningStatus::CANCELLED);
    }

    /* ---------------------- Methods ---------------------- */

    /**
     * Check if the referral earning is active
     */
    public function isActive(): bool
    {
        return $this->status === ReferralEarningStatus::ACTIVE;
    }

    /**
     * Check if the referral earning is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === ReferralEarningStatus::REFUNDED;
    }

    /**
     * Check if the referral earning is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === ReferralEarningStatus::CANCELLED;
    }

    /* ---------------------- Relationships ---------------------- */

    /**
     * Get the referral that owns the referral earning
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the seller that owns the referral earning
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the sale that owns the referral earning
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

















