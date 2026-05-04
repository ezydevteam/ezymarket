<?php

namespace App\Models\Blog;

use App\Enums\BlogCommentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model, Builder,
    Relations\BelongsTo,
    Casts\Attribute,
    Factories\HasFactory
};

/**
 * BlogComment Model
 *
 * @property int $id
 * @property int $user_id
 * @property int $blog_article_id
 * @property string $body
 * @property BlogCommentStatus $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read BlogArticle $article
 */
class BlogComment extends Model
{
    use HasFactory;

    protected $table = 'blog_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'blog_article_id',
        'body',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'blog_article_id' => 'integer',
            'status' => BlogCommentStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* ---------------------- Scopes ---------------------- */

    /**
     * Scope to filter pending comments
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BlogCommentStatus::PENDING);
    }

    /**
     * Scope to filter hold comments
     */
    public function scopeHold(Builder $query): Builder
    {
        return $query->where('status', BlogCommentStatus::HOLD);
    }

    /**
     * Scope to filter published comments
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BlogCommentStatus::PUBLISHED);
    }

    /**
     * Scope to filter by article
     */
    public function scopeForArticle(Builder $query, int $articleId): Builder
    {
        return $query->where('blog_article_id', $articleId);
    }

    /* ---------------------- Accessors ---------------------- */

    /**
     * Get the status badge HTML
     */
    public function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->badge()
        );
    }

    /* ---------------------- Methods ---------------------- */

    /**
     * Check if the comment is pending
     */
    public function isPending(): bool
    {
        return $this->status === BlogCommentStatus::PENDING;
    }

    /**
     * Check if the comment is on hold
     */
    public function isHold(): bool
    {
        return $this->status === BlogCommentStatus::HOLD;
    }

    /**
     * Check if the comment is published
     */
    public function isPublished(): bool
    {
        return $this->status === BlogCommentStatus::PUBLISHED;
    }

    /* ---------------------- Relationships ---------------------- */

    /**
     * Get the user that owns the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the article that owns the comment
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(BlogArticle::class, 'blog_article_id');
    }
}
