<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Addon Model
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string|null $version
 * @property string|null $thumbnail
 * @property string|null $path
 * @property string|null $action
 * @property bool|null $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Addon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'alias',
        'version',
        'thumbnail',
        'path',
        'action',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ============================================
    // Query Scopes
    // ============================================

    /**
     * Scope a query to only include active addons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive addons.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include addons with no status.
     */
    public function scopeWithoutStatus(Builder $query): Builder
    {
        return $query->whereNull('is_active');
    }

    // ============================================
    // Status Checkers
    // ============================================

    /**
     * Check if the addon is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if the addon is inactive.
     */
    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Check if the addon has no status.
     */
    public function hasNoStatus(): bool
    {
        return $this->is_active === null;
    }

    // ============================================
    // Action Methods
    // ============================================

    /**
     * Activate the addon.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the addon.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Find an addon by its alias.
     */
    public static function findByAlias(string $alias): ?self
    {
        return static::where('alias', $alias)->first();
    }

    /**
     * Find an active addon by its alias.
     */
    public static function findActiveByAlias(string $alias): ?self
    {
        return static::active()->where('alias', $alias)->first();
    }

    /**
     * Get all active addons.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->get();
    }
}



















