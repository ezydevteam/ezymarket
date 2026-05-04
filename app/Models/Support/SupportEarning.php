<?php

namespace App\Models\Support;

use App\Enums\SupportEarningStatus;
use App\Models\{Purchase, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $seller_id
 * @property int $purchase_id
 * @property string $name
 * @property string $title
 * @property int $days
 * @property float $price
 * @property object|null $buyer_tax
 * @property float $seller_fee
 * @property object|null $seller_tax
 * @property float $seller_earning
 * @property SupportEarningStatus $status
 * @property \Carbon\Carbon $support_expiry_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $seller
 * @property-read Purchase $purchase
 */
class SupportEarning extends Model
{
    use HasFactory;

    protected $table = 'support_earnings';

    protected $fillable = [
        'seller_id',
        'purchase_id',
        'name',
        'title',
        'days',
        'price',
        'buyer_tax',
        'seller_fee',
        'seller_tax',
        'seller_earning',
        'status',
        'support_expiry_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SupportEarningStatus::class,
            'days' => 'integer',
            'price' => 'decimal:2',
            'seller_fee' => 'decimal:2',
            'seller_earning' => 'decimal:2',
            'buyer_tax' => 'object',
            'seller_tax' => 'object',
            'support_expiry_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', SupportEarningStatus::ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === SupportEarningStatus::ACTIVE;
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', SupportEarningStatus::REFUNDED);
    }

    public function isRefunded(): bool
    {
        return $this->status === SupportEarningStatus::REFUNDED;
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', SupportEarningStatus::CANCELLED);
    }

    public function isCancelled(): bool
    {
        return $this->status === SupportEarningStatus::CANCELLED;
    }

    public function isSupportExpired(): bool
    {
        return Carbon::now()->greaterThan($this->support_expiry_at);
    }

    public function isSupportActive(): bool
    {
        return $this->isActive() && !$this->isSupportExpired();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
