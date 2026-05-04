<?php

namespace App\Models\Financial;

use App\Enums\LicenseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\Product\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property int $transaction_id
 * @property int $product_id
 * @property LicenseType $license_type
 * @property float $price
 * @property int $quantity
 * @property float $total
 * @property object|null $support
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TransactionProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'license_type',
        'price',
        'quantity',
        'total',
        'support',
    ];

    /**
     * The relationships that should always be eager loaded.
     */
    protected $with = [
        'product',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'license_type' => LicenseType::class,
            'price' => 'decimal:2',
            'total' => 'decimal:2',
            'support' => 'object',
        ];
    }

    /**
     * Get the product for this transaction product.
     */
    protected function licenseLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type?->label(),
        );
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Check if the transaction product has a regular license.
     */
    public function isRegularLicense(): bool
    {
        return $this->license_type === LicenseType::REGULAR;
    }

    /**
     * Check if the transaction product has an extended license.
     */
    public function isExtendedLicense(): bool
    {
        return $this->license_type === LicenseType::EXTENDED;
    }

    /**
     * Get the total amount for this transaction product.
     */
    public function getTotalAmount(): float
    {
        $total = ($this->price * $this->quantity);

        if ($this->support) {
            $total += $this->support->total;
        }

        return (float) $total;
    }

    /**
     * Get the seller earning for this transaction product.
     */
    public function getSellerEarning(): float
    {
        $sale = $this->sale;

        if ($sale?->seller_earning) {
            return $sale->seller_earning;
        }
        // Fallback: calculate 70% of product price
        return $this->price * 0.70;
    }

    // ============================================
    // Relationships
    // ============================================

    /**
     * Get the product for this transaction product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the sale record for this transaction product.
     */
    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'product_id', 'product_id')
            ->where('license_type', $this->license_type)
            ->active()
            ->latest();
    }
}
