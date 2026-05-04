<?php

namespace App\Models\Blog;

use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Admin;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasMany
};

class BlogArticle extends Model
{
    use HasFactory, Sluggable;

    /**
     * The table associated with the model.
     */
    protected $table = 'blog_articles';

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'image',
        'body',
        'short_description',
        'total_views',
        'blog_category_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admin_id' => 'integer',
            'blog_category_id' => 'integer',
            'total_views' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the view link attribute.
     */
    public function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn() => route('blog.article', ['slug' => $this->slug])
        );
    }

    /**
     * Get the image link attribute.
     */
    public function imageLink(): Attribute
    {
        return Attribute::make(
            get: fn() =>  asset($this->image)
        );
    }

    /**
     * Get the author that owns the blog article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the category that owns the blog article.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /**
     * Get the comments for the blog article.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

}
