<?php

namespace App\Models\Financial;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutMethod extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'payout_methods';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'amount_limit',
        'monthly_limit',
        'fees',
        'instructions',
        'sort_id',
        'is_active',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount_limit' => 'array',
            'monthly_limit' => 'integer',
            'fees' => 'array',
            'sort_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active withdrawal methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_id.
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_id');
    }

    /**
     * Check if withdrawal method is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get available status options.
     */

    public static function getStatusOptions(): array
    {
        return [
            true => 'Active',
            false => 'Inactive',
        ];
    }

    /**
     * Available attributes.
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_active ? 'Active' : 'Inactive',
        );
    }

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            '<span class="badge ' . ($this->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger') . '">
                <i class="bi ' . ($this->is_active ? 'bi-check-circle' : 'bi-x-circle') . ' me-1"></i>' . $this->status_name . '</span>',
        );
    }

    protected function minAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => isset($this->amount_limit['min']) ? (float) $this->amount_limit['min'] : null,
        );
    }

    protected function maxAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => isset($this->amount_limit['max']) && (float) $this->amount_limit['max'] > 0
                ? (float) $this->amount_limit['max']
                : null,
        );
    }

    protected function monthlyLimits(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) ($this->monthly_limit ?? 0),
        );
    }

    protected function feesType(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->fees['type'] ?? null,
        );
    }

    protected function feesValue(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->fees['value'] ?? 0,
        );
    }

    /**
     * Check if the amount is valid for this payout method.
     */
    public function isAmountValid(float $amount): bool
    {
        $min = $this->min_amount;
        $max = $this->max_amount;

        // Check minimum (if set and > 0)
        if ($min > 0 && $amount < $min) {
            return false;
        }

        // Check maximum (if set and > 0)
        if ($max > 0 && $amount > $max) {
            return false;
        }

        return true;
    }

    /**
     * Check if this method has fees configured.
     */
    public function hasFees(): bool
    {
        return !empty($this->fees) && !empty($this->fees['type']) && !empty($this->fees['value']);
    }

    /**
     * Calculate fees for a given amount.
     * Fees can be configured as:
     * - ['type' => 'percentage', 'value' => 2.5] for 2.5% fee
     * - ['type' => 'fixed', 'value' => 5] for fixed $5 fee
     *
     * @param float $amount The payout amount
     * @return float The calculated fee
     */
    public function calculateFees(float $amount): float
    {
        if (!$this->fees || !is_array($this->fees)) {
            return 0;
        }

        $type = $this->fees['type'] ?? null;
        $value = $this->fees['value'] ?? 0;

        return match ($type) {
            'percentage' => ($amount * $value) / 100,
            'fixed' => (float) $value,
            default => 0,
        };
    }

    /**
     * Get the processing fee for this payout method.
     */
    public function getProcessingFee(): Mixed
    {
        if ($this->fees_type === 'percentage') {
            return ($this->fees_value) . '%';
        } elseif ($this->fees_type === 'fixed') {
            return getAmount($this->fees_value);
        }

        return null;
    }

    /**
     * Check if user can make withdrawal based on monthly limit.
     */
    public function canWithdrawThisMonth(int $currentMonthWithdrawals): bool
    {
        $limit = $this->monthly_limit;

        // If no limit set, unlimited withdrawals allowed
        if ($limit === null || $limit === 0) {
            return true;
        }

        return $currentMonthWithdrawals < $limit;
    }
}
