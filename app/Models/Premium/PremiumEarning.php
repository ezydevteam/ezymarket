<?php

declare(strict_types=1);

namespace App\Models\Premium;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Premium Earning Model
 *
 * Represents earnings distributed to sellers from premium membership.
 * Tracks the percentage-based earnings when users purchase premium packages.
 *
 * @package App\Models\Premium
 *
 * @property int $id
 * @property int $seller_id
 * @property int $premium_id
 * @property int $product_id
 * @property string $name
 * @property float $percentage
 * @property float $price
 * @property float $seller_earning
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $seller
 * @property-read Premium $premium
 * @property-read Product $product
 * @property-read string $formatted_price
 * @property-read string $formatted_earning
 * @property-read string $percentage_display
 */
class PremiumEarning extends Model
{
    use HasFactory;

    protected $table = 'premium_earnings';

    protected $fillable = [
        'seller_id',
        'premium_id',
        'product_id',
        'name',
        'percentage',
        'price',
        'seller_earning',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'seller_earning' => 'float',
        ];
    }

    // ==================== Helper Methods ====================

    /**
     * Calculate the earning amount based on percentage
     *
     * @return float
     */
    public function calculateEarning(): float
    {
        return ($this->price * $this->percentage) / 100;
    }

    /**
     * Check if earning has been distributed to seller
     *
     * @return bool
     */
    public function isDistributed(): bool
    {
        return $this->seller_earning > 0;
    }

    // ==================== Relationships ====================

    /**
     * Get the seller who received this earning
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the premium membership that generated this earning
     *
     * @return BelongsTo
     */
    public function premium(): BelongsTo
    {
        return $this->belongsTo(Premium::class);
    }

    /**
     * Get the product associated with this earning
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
