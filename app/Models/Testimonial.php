<?php

namespace App\Models;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Testimonial Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string $title
 * @property string $content
 * @property int $sort_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'testimonials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'image',
        'title',
        'content',
        'sort_id',
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
            'sort_id' => 'integer',
        ];
    }

    /* ---------------------- Accessors ---------------------- */

    /**
     * Get the full image URL
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image ? asset($this->image) : asset('images/default/default-testimonial.png'),
        );
    }

    /**
     * Deprecated: Use getImageUrl() or image_url attribute
     * @deprecated Use image_url attribute
     */
    public function getAvatar()
    {
        return $this->image_url;
    }
}



















