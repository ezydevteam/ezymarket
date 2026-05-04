<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Advertisement Model
 *
 * Manages advertisement placements throughout the application.
 *
 * @property int $id
 * @property string $alias
 * @property string $position
 * @property string $size
 * @property string|null $ad_code
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder active()
 */
class Advertisement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'advertisements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alias',
        'position',
        'size',
        'ad_code',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active advertisements.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive advertisements.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include home advertisements.
     */
    public function scopeHome(Builder $query): Builder
    {
        return $query->where('alias', 'LIKE', '%home%');
    }

    /**
     * Check if the advertisement is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if the advertisement is inactive.
     */
    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Activate the advertisement.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the advertisement.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get advertisement by alias.
     */
    public static function findByAlias(string $alias): ?self
    {
        return static::where('alias', $alias)->first();
    }

    /**
     * Get active advertisement by alias.
     */
    public static function findActiveByAlias(string $alias): ?self
    {
        return static::where('alias', $alias)->active()->first();
    }
}
