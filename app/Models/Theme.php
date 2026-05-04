<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Theme Model
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $version
 * @property string|null $thumbnail
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Theme extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'alias',
        'version',
        'thumbnail',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* ---------------------- Accessors ---------------------- */

    /**
     * Get the full thumbnail URL
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->thumbnail
                ? asset($this->thumbnail)
                : asset('images/placeholders/default-theme.jpg'),
        );
    }

    /* ---------------------- Methods ---------------------- */

    /**
     * Check if this theme is currently active
     */
    public function isActive(): bool
    {
        return $this->alias == activeTheme();
    }
}



















