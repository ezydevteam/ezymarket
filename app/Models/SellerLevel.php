<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SellerLevel extends Model
{
    use HasFactory;

    /*
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'seller_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'icon',
        'min_earnings',
        'fees',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'min_earnings' => 'decimal:2',
        'fees' => 'decimal:2',
    ];

    /**
     * Scope a query to only include default level.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Check if this is the default level.
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }
    
    /**
     * Delete the level icon.
     */
    public function deleteIcon()
    {
        if ($this->icon) {
            removeFile(public_path($this->icon));
            $this->forceFill(['icon' => null])->save();
        }
    }

    /**
     * Get the icon URL.
     */
    public function iconUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->icon ? asset($this->icon) : null
        );
    }

    /**
     * Get the badge associated with this level.
     */
    public function badge(): HasOne
    {
        return $this->hasOne(Badge::class, 'level_id');
    }
}
