<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\{PurchaseStatus, LicenseType};
use App\Models\Product\Product;
use App\Models\Product\ProductReview;
use App\Models\Support\SupportEarning;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

/**
 * Purchase Model
 *
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property int $sale_id
 * @property int $product_id
 * @property LicenseType $license_type
 * @property bool $is_downloaded
 * @property PurchaseStatus $status
 * @property Carbon $support_expiry_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read string $status_name
 * @property-read string $status_badge_class
 * @property-read string $status_icon
 * @property-read string $license_type_name
 * @property-read string $license_type_short_name
 * @property-read string $license_type_badge_class
 * @property-read string $license_type_icon
 * @property-read mixed $review
 *
 * @property-read User $user
 * @property-read User $seller
 * @property-read Sale $sale
 * @property-read Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<SupportEarning> $supportEarnings
 */
class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'user_id',
        'seller_id',
        'sale_id',
        'product_id',
        'license_type',
        'code',
        'support_expiry_at',
        'is_downloaded',
        'status',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'license_type' => LicenseType::class,
            'status' => PurchaseStatus::class,
            'is_downloaded' => 'boolean',
            'support_expiry_at' => 'datetime',
        ];
    }

    /**
     * Get the human-readable status name
     *
     * @return Attribute<string, never>
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label()
        );
    }

    /**
     * Get the Bootstrap badge class for status
     *
     * @return Attribute<string, never>
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badgeClass()
        );
    }

    /**
     * Get the FontAwesome icon for status
     *
     * @return Attribute<string, never>
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->icon()
        );
    }

    /**
     * Get the full license type name
     *
     * @return Attribute<string, never>
     */
    protected function licenseTypeName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->label()
        );
    }

    /**
     * Get the short license type name
     *
     * @return Attribute<string, never>
     */
    protected function licenseTypeShortName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->shortLabel()
        );
    }

    /**
     * Get the Bootstrap badge class for license type
     *
     * @return Attribute<string, never>
     */
    protected function licenseTypeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->badgeClass()
        );
    }

    /**
     * Get the FontAwesome icon for license type
     *
     * @return Attribute<string, never>
     */
    protected function licenseTypeIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_type->icon()
        );
    }

    /**
     * Check if purchase has regular license
     *
     * @return bool
     */
    public function isRegularLicense(): bool
    {
        return $this->license_type === LicenseType::REGULAR;
    }

    /**
     * Check if purchase has extended license
     *
     * @return bool
     */
    public function isExtendedLicense(): bool
    {
        return $this->license_type === LicenseType::EXTENDED;
    }

    /**
     * Check if product has been downloaded
     *
     * @return bool
     */
    public function isDownloaded(): bool
    {
        return $this->is_downloaded;
    }

    /**
     * Check if purchase is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === PurchaseStatus::ACTIVE;
    }

    /**
     * Check if purchase is refunded
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === PurchaseStatus::REFUNDED;
    }

    /**
     * Check if purchase is cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === PurchaseStatus::CANCELLED;
    }

    /**
     * Check if support period has expired
     *
     * @return bool
     */
    public function isSupportExpired(): bool
    {
        if (!$this->support_expiry_at) {
            return true;
        }

        return Carbon::now()->greaterThan($this->support_expiry_at);
    }

    /**
     * Check if support period is still active
     *
     * @return bool
     */
    public function isSupportActive(): bool
    {
        return !$this->isSupportExpired();
    }

    /**
     * Scope query to active purchases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeActive($query): void
    {
        $query->where($this->getTable() . '.status', PurchaseStatus::ACTIVE->value);
    }

    /**
     * Scope query to refunded purchases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeRefunded($query): void
    {
        $query->where($this->getTable() . '.status', PurchaseStatus::REFUNDED->value);
    }

    /**
     * Scope query to cancelled purchases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeCancelled($query): void
    {
        $query->where($this->getTable() . '.status', PurchaseStatus::CANCELLED->value);
    }

    /**
     * Scope query to regular license purchases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeRegularLicense($query): void
    {
        $query->where($this->getTable() . '.license_type', LicenseType::REGULAR->value);
    }

    /**
     * Scope query to extended license purchases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeExtendedLicense($query): void
    {
        $query->where($this->getTable() . '.license_type', LicenseType::EXTENDED->value);
    }

    /**
     * Scope query to purchases with active support
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeSupportActive($query): void
    {
        $query->where($this->getTable() . '.support_expiry_at', '>=', Carbon::now());
    }

    /**
     * Scope query to purchases with expired support
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeSupportExpired($query): void
    {
        $query->where($this->getTable() . '.support_expiry_at', '<', Carbon::now());
    }

    /**
     * Get all reviews for the product associated with this purchase
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'product_id');
    }

    /**
     * Get the review attribute for this specific user
     *
     * @return mixed
     */
    public function getReviewAttribute()
    {
        return $this->reviews()->where('user_id', $this->user_id)->first();
    }

    /**
     * Get the user who made the purchase
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the seller of the purchase
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }

    /**
     * Get the associated sale
     *
     * @return BelongsTo
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the purchased product
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get all support earnings for this purchase
     *
     * @return HasMany
     */
    public function supportEarnings(): HasMany
    {
        return $this->hasMany(SupportEarning::class);
    }
}
















