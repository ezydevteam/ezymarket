<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StorageDriver extends Model
{
    use HasFactory;

    protected $table = 'storage_drivers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'alias',
        'handler',
        'credentials',
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
        ];
    }

    /**
     * Scope to filter the default storage driver.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('alias', env('FILESYSTEM_DRIVER'));
    }

    /**
     * Check if the storage driver is local.
     *
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->alias === "local";
    }

    /**
     * Check if this is the default storage driver.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->alias == env('FILESYSTEM_DRIVER');
    }
}
