<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseType;
use App\Enums\SaleStatus;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasOne};


/**
 * Sale Model
 *
 * Represents a sale transaction in the system.
 *
 * @property int $id
 * @property int $seller_id
 * @property int $user_id
 * @property int $product_id
 * @property LicenseType $license_type
 * @property float $price
 * @property float $buyer_fee
 * @property object $buyer_tax
 * @property float $seller_fee
 * @property object $seller_tax
 * @property float $seller_earning
 * @property string|null $country
 * @property SaleStatus $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $seller
 * @property-read User $user
 * @property-read Product $product
 * @property-read Purchase|null $purchase
 * @property-read ReferralEarning|null $referralEarning
 * @property-read string $status_name
 * @property-read string $status_badge_class
 * @property-read string $status_icon
 * @property-read string $license_type_name
 * @property-read string $license_type_badge_class
 * @property-read string $license_type_icon
 *
 * @package App\Models
 */
class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'seller_id',
        'user_id',
        'product_id',
        'license_type',
        'price',
        'buyer_fee',
        'buyer_tax',
        'seller_fee',
        'seller_tax',
        'seller_earning',
        'country',
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
            'license_type' => LicenseType::class,
            'status' => SaleStatus::class,
            'buyer_tax' => 'object',
            'seller_tax' => 'object',
        ];
    }

    /**
     * Scope a query to only include active sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::ACTIVE->value);
    }

    /**
     * Scope a query to only include refunded sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::REFUNDED->value);
    }

    /**
     * Scope a query to only include cancelled sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::CANCELLED->value);
    }

    /**
     * Scope a query to only include regular license sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRegularLicense(Builder $query): Builder
    {
        return $query->where('license_type', LicenseType::REGULAR->value);
    }

    /**
     * Scope a query to only include extended license sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExtendedLicense(Builder $query): Builder
    {
        return $query->where('license_type', LicenseType::EXTENDED->value);
    }

    /**
     * Check if the sale is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === SaleStatus::ACTIVE;
    }

    /**
     * Check if the sale is refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === SaleStatus::REFUNDED;
    }

    /**
     * Check if the sale is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === SaleStatus::CANCELLED;
    }

    /**
     * Check if the license type is regular.
     *
     * @return bool
     */
    public function isRegularLicense(): bool
    {
        return $this->license_type === LicenseType::REGULAR;
    }

    /**
     * Check if the license type is extended.
     *
     * @return bool
     */
    public function isExtendedLicense(): bool
    {
        return $this->license_type === LicenseType::EXTENDED;
    }

    /**
     * Get the status name.
     *
     * @return Attribute
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label()
        );
    }

    /**
     * Get the status badge CSS class.
     *
     * @return Attribute
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badgeClass()
        );
    }

    /**
     * Get the status icon class.
     *
     * @return Attribute
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->icon()
        );
    }

    /**
     * Get the license type name.
     *
     * @return Attribute
     */
    protected function licenseTypeName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->label()
        );
    }

    /**
     * Get the license type short name.
     *
     * @return Attribute
     */
    protected function licenseTypeShortName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->shortLabel()
        );
    }

    /**
     * Get the license type badge CSS class.
     *
     * @return Attribute
     */
    protected function licenseTypeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->badgeClass()
        );
    }

    /**
     * Get the license type icon class.
     *
     * @return Attribute
     */
    protected function licenseTypeIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->icon()
        );
    }

    /**
     * Get the transaction associated with the sale.
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Financial\Transaction::class, 'transaction_id');
    }

    /**
     * Get the seller that owns the sale.
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }

    /**
     * Get the buyer (user) that owns the sale.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the product associated with the sale.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the purchase associated with the sale.
     *
     * @return HasOne
     */
    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }

    /**
     * Get the referral earning associated with the sale.
     *
     * @return HasOne
     */
    public function referralEarning(): HasOne
    {
        return $this->hasOne(ReferralEarning::class);
    }
}

















