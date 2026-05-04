<?php

declare(strict_types=1);

namespace App\Models\Premium;

use App\Enums\PremiumPlanInterval;
use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PremiumPlan extends Model
{
    use HasFactory;

    protected $table = 'premium_plans';

    protected $fillable = [
        'name',
        'description',
        'interval',
        'price',
        'seller_earning_percentage',
        'downloads',
        'custom_features',
        'is_active',
        'is_featured',
        'featured_label',
        'sort_id',
    ];

    protected function casts(): array
    {
        return [
            'interval' => PremiumPlanInterval::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'custom_features' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);
    }

    // ==================== Relationships ====================

    public function premiums(): HasMany
    {
        return $this->hasMany(Premium::class, 'plan_id');
    }

    // ==================== Query Scopes ====================

    public function scopeInterval($query, PremiumPlanInterval $interval)
    {
        return $query->where('interval', $interval->value);
    }

    public function scopeWeekly($query)
    {
        return $query->where('interval', PremiumPlanInterval::WEEK->value);
    }

    public function scopeMonthly($query)
    {
        return $query->where('interval', PremiumPlanInterval::MONTH->value);
    }

    public function scopeYearly($query)
    {
        return $query->where('interval', PremiumPlanInterval::YEAR->value);
    }

    public function scopeLifetime($query)
    {
        return $query->where('interval', PremiumPlanInterval::LIFETIME->value);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    public function scopeNotFree($query)
    {
        return $query->where('price', '>', 0);
    }

    // ==================== Accessor Attributes ====================

    protected function intervalName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->interval->label()
        );
    }

    protected function intervalDays(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->interval->days()
        );
    }

    protected function intervalBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->interval->badgeClass()
        );
    }

    protected function priceLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->isFree() ? translate('Free') : getAmount($this->price)
        );
    }

    protected function downloadLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->hasUnlimitedDownloads()
                ? translate('Unlimited')
                : number_format($this->downloads)
        );
    }

    protected function featuredBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_featured
                ? ($this->featured_label ?: translate('Featured'))
                : ''
        );
    }

    // ==================== Helper Methods ====================

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function isLifetime(): bool
    {
        return $this->interval === PremiumPlanInterval::LIFETIME;
    }

    public function hasUnlimitedDownloads(): bool
    {
        return is_null($this->downloads) || $this->downloads < 0;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function getSellerEarningAmount(float $premiumPrice): float
    {
        if ($this->seller_earning_percentage <= 0) {
            return 0;
        }

        return ($premiumPrice * $this->seller_earning_percentage) / 100;
    }

    public function getDurationInDays(): ?int
    {
        return match ($this->interval) {
            PremiumPlanInterval::WEEK => 7,
            PremiumPlanInterval::MONTH => 30,
            PremiumPlanInterval::YEAR => 365,
            PremiumPlanInterval::LIFETIME => null,
            default => null,
        };
    }

    public function getExpiryDate(): ?\Carbon\Carbon
    {
        $days = $this->getDurationInDays();

        if (is_null($days)) {
            return null;
        }

        return now()->addDays($days);
    }
}
