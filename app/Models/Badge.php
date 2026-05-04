<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BadgeAlias;
use App\Models\SellerLevel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Badge Model
 *
 * @property int $id
 * @property string $name
 * @property \App\Enums\BadgeAlias $alias
 * @property string|null $title
 * @property string $image
 * @property string|null $country
 * @property int|null $level_id
 * @property int|null $membership_years
 * @property bool $is_permanent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Level|null $level
 */
class Badge extends Model
{
    use HasFactory;

    protected $table = 'badges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'alias',
        'title',
        'image',
        'country',
        'level_id',
        'membership_years',
        'is_permanent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_permanent' => 'boolean',
            'level_id' => 'integer',
            'membership_years' => 'integer',
            'alias' => BadgeAlias::class,
        ];
    }

    /**
     * Scope a query to only include country badges.
     */
    public function scopeCountryBadge(Builder $query): void
    {
        $query->where('alias', BadgeAlias::COUNTRY);
    }

    /**
     * Scope a query to only include seller level badges.
     */
    public function scopeSellerLevelBadge(Builder $query): void
    {
        $query->where('alias', BadgeAlias::SELLER_LEVEL);
    }

    /**
     * Scope a query to only include membership years badges.
     */
    public function scopeMembershipYearsBadge(Builder $query): void
    {
        $query->where('alias', BadgeAlias::MEMBERSHIP_YEARS);
    }

    /**
     * Scope a query to only include exclusive seller badges.
     */
    public function scopeExclusiveSellerBadge(Builder $query): void
    {
        $query->where('alias', BadgeAlias::EXCLUSIVE_SELLER);
    }

    /**
     * Scope a query to exclude premium badges if not licensed.
     */
    public function scopeExcludePremiumIfNotLicensed(Builder $query): void
    {
        if (!isPremiumAvailable()) {
            $query->whereNotIn('alias', [BadgeAlias::PREMIUMER, BadgeAlias::PREMIUM_MEMBERSHIP]);
        }
    }

    /**
     * Check if this is a default badge.
     */
    public function isDefaultBadge(): bool
    {
        return !in_array($this->alias, [
            BadgeAlias::COUNTRY,
            BadgeAlias::SELLER_LEVEL,
            BadgeAlias::MEMBERSHIP_YEARS,
        ]);
    }

    /**
     * Check if this is a country badge.
     */
    public function isCountryBadge(): bool
    {
        return $this->alias === BadgeAlias::COUNTRY;
    }

    /**
     * Check if this is a seller level badge.
     */
    public function isSellerLevelBadge(): bool
    {
        return $this->alias === BadgeAlias::SELLER_LEVEL;
    }

    /**
     * Check if this is a membership years badge.
     */
    public function isMembershipYearsBadge(): bool
    {
        return $this->alias === BadgeAlias::MEMBERSHIP_YEARS;
    }

    /**
     * Get the badge image URL attribute.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => asset($this->image),
        );
    }

    /**
     * Get the badge full title attribute.
     */
    protected function fullTitle(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title ?: $this->name,
        );
    }

    /**
     * Delete the badge image file.
     */
    public function deleteImage(): void
    {
        if ($this->image) {
            removeFile(public_path($this->image));
            $this->forceFill(['image' => null])->save();
        }
    }

    /**
     * Get the level that owns the badge.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(SellerLevel::class);
    }
}
