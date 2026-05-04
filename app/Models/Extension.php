<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Extension Model
 *
 * Represents third-party service integrations and extensions
 * that can be enabled/disabled in the application.
 *
 * @property int $id
 * @property string $name Extension display name
 * @property string $alias Unique identifier/alias
 * @property string|null $logo Logo path or URL
 * @property object|null $credentials API credentials and configuration
 * @property bool $is_active Active status (true = active, false = inactive)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @package App\Models
 */
class Extension extends Model
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
        'logo',
        'description',
        'credentials',
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
            'credentials' => 'object',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active extensions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive extensions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }


    /**
     * Check if the extension is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if the extension is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Get the extension's logo URL.
     *
     * @return Attribute
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->logo ? asset($this->logo) : null,
        );
    }

    /**
     * Apply extension credentials to environment.
     *
     * Sets necessary environment variables based on extension configuration.
     *
     * @return void
     */
    public function applyCredentials(): void
    {
        if (!$this->isActive() || !$this->credentials) {
            return;
        }

        match ($this->alias) {
            'trustip' => $this->applyTrustipCredentials(),
            default => null,
        };
    }

    /**
     * Apply TrustIP extension credentials.
     *
     * @return void
     */
    private function applyTrustipCredentials(): void
    {
        if (isset($this->credentials->api_key)) {
            setEnv('TRUSTIP_API_KEY', $this->credentials->api_key);
        }
    }
}

















