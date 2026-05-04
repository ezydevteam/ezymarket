<?php

namespace App\Models\Product;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo
};

/**
 * @property int $id
 * @property int $product_id
 * @property int $regular_percentage
 * @property float $regular_price
 * @property int|null $extended_percentage
 * @property float|null $extended_price
 * @property \Illuminate\Support\Carbon $starting_at
 * @property \Illuminate\Support\Carbon $ending_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Product $product
 */
class ProductDiscount extends Model
{
    use HasFactory;

    protected $table = 'product_discounts';

    protected $fillable = [
        'product_id',
        'regular_percentage',
        'regular_price',
        'extended_percentage',
        'extended_price',
        'starting_at',
        'ending_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'regular_percentage' => 'integer',
            'regular_price' => 'decimal:2',
            'extended_percentage' => 'integer',
            'extended_price' => 'decimal:2',
            'starting_at' => 'datetime',
            'ending_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($discount) {
            $product = $discount->product;
            $product->is_on_discount = false;
            $product->save();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeStarted($query)
    {
        return $query->where('starting_at', '<=', Carbon::now());
    }

    public function scopeEnded($query)
    {
        return $query->where('ending_at', '<', Carbon::now());
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Check if the discount is within the grace period (60 seconds after creation).
     * During this window, an active discount can still be deleted.
     */
    public function isWithinGracePeriod(): bool
    {
        return $this->created_at && $this->created_at->diffInSeconds(Carbon::now()) < 60;
    }

    /**
     * Check if the discount can be deleted by the user.
     */
    public function isDeletable(): bool
    {
        return $this->isInactive() || ($this->isActive() && $this->isWithinGracePeriod());
    }

    /**
     * Get the remaining grace period seconds.
     */
    public function getGraceSecondsRemaining(): int
    {
        if (!$this->isWithinGracePeriod()) {
            return 0;
        }

        return max(0, 60 - (int) $this->created_at->diffInSeconds(Carbon::now()));
    }

    public function isStarted(): bool
    {
        return $this->starting_at <= Carbon::now();
    }

    public function isEnded(): bool
    {
        return $this->ending_at < Carbon::now();
    }

    public function hasExtended(): bool
    {
        return !empty($this->extended_percentage) && !empty($this->extended_price);
    }

    /**
     * Get total regular price including buyer fees.
     */
    public function getRegularPrice(): float
    {
        return $this->regular_price + ($this->product?->category?->regular_buyer_fee ?? 0);
    }

    /**
     * Get total extended price including buyer fees.
     */
    public function getExtendedPrice(): ?float
    {
        return $this->extended_price
            ? $this->extended_price + ($this->product?->category?->extended_buyer_fee ?? 0)
            : null;
    }

    /**
     * Get the discount prices as an object for easy access.
     * Usage: $discount->price->regular, $discount->price->extended
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn() => (object) [
                'regular' => $this->regular_price + ($this->product?->category?->regular_buyer_fee ?? 0),
                'extended' => $this->extended_price
                    ? $this->extended_price + ($this->product?->category?->extended_buyer_fee ?? 0)
                    : null,
            ]
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
