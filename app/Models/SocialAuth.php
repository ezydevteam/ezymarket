<?php

namespace App\Models;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * SocialAuth Model
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string|null $logo
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property int $type
 * @property int $sort_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SocialAuth extends Model
{
    use HasFactory;

    /**
     * Display type constants
     */
    const TYPE_MODERN = 1;           // Modern logo with name
    const TYPE_MINIMALISTIC = 2;     // Minimalistic logo with name
    const TYPE_LOGO_ONLY = 3;        // Logo only

    /**
     * The table associated with the model.
     */
    protected $table = "social_authetication";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alias',
        'name',
        'logo',
        'client_id',
        'client_secret',
        'type',
        'sort_id',
        'is_active',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new SortableScope);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'sort_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ---------------------- Scopes ---------------------- */

    /**
     * Scope a query to only include active social auths.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive social auths.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to order by sort_id.
     */
    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('sort_id');
    }

    /* ---------------------- Methods ---------------------- */

    /**
     * Check if social auth is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if display type is modern.
     */
    public function isModern(): bool
    {
        return $this->type == self::TYPE_MODERN;
    }

    /**
     * Check if display type is minimalistic.
     */
    public function isMinimalistic(): bool
    {
        return $this->type == self::TYPE_MINIMALISTIC;
    }

    /**
     * Check if display type is logo only.
     */
    public function isLogoOnly(): bool
    {
        return $this->type == self::TYPE_LOGO_ONLY;
    }

    /**
     * Get logo URL.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->logo ? asset($this->logo) : null,
        );
    }

    /**
     * Get the display type name.
     */
    protected function typeName(): Attribute
    {
        return Attribute::make(
            get: fn() => self::getTypeOptions()[$this->type] ?? 'Unknown',
        );
    }

    /**
     * Get available display types.
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_MODERN => 'Modern Logo with Name',
            self::TYPE_MINIMALISTIC => 'Minimalistic Logo with Name',
            self::TYPE_LOGO_ONLY => 'Logo Only',
        ];
    }

    /**
     * Set environment credentials for this social provider.
     */
    public function setCredentials(): void
    {
        $providerMap = [
            'facebook' => 'FACEBOOK',
            'google' => 'GOOGLE',
            'microsoft' => 'MICROSOFT',
            'vkontakte' => 'VKONTAKTE',
            'envato' => 'ENVATO',
            'github' => 'GITHUB',
        ];

        if (isset($providerMap[$this->alias])) {
            $prefix = $providerMap[$this->alias];
            setEnv("{$prefix}_CLIENT_ID", $this->client_id);
            setEnv("{$prefix}_CLIENT_SECRET", $this->client_secret);
        }
    }
}
