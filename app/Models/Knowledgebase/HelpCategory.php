<?php

namespace App\Models\Knowledgebase;

use App\Scopes\SortableScope;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $total_views
 * @property int $sort_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class HelpCategory extends Model
{
    use HasFactory, Sluggable;

    protected static function booted()
    {
        static::addGlobalScope(new SortableScope);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    protected $fillable = [
        'name',
        'slug',
        'total_views',
        'sort_id',
    ];

    /**
     * Get the category's URL
     */
    public function view_link: string
    {
        return route('help.category', $this->slug);
    }

    /**
     * Get all articles in this category
     */
    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'help_category_id');
    }

    /**
     * Backward compatibility accessor for views (deprecated - use total_views)
     * @deprecated Use total_views property instead
     */
    protected function views(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->total_views,
        );
    }
}
