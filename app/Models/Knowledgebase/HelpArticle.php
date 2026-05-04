<?php

namespace App\Models\Knowledgebase;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $description
 * @property int $total_views
 * @property int $likes
 * @property int $dislikes
 * @property int $help_category_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class HelpArticle extends Model
{
    use HasFactory, Sluggable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    protected $fillable = [
        'title',
        'slug',
        'content',
        'description',
        'total_views',
        'likes',
        'dislikes',
        'help_category_id',
    ];

    /**
     * Get the article's URL
     */
    public function view_link: string
    {
        return route('help.article', $this->slug);
    }

    /**
     * Get the category that this article belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }

    /**
     * Backward compatibility accessor for body (deprecated - use content)
     * @deprecated Use content property instead
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->content,
        );
    }

    /**
     * Backward compatibility accessor for short_description (deprecated - use description)
     * @deprecated Use description property instead
     */
    protected function shortDescription(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->description,
        );
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
