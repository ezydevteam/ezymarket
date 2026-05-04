<?php

namespace App\Models\Product;

use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasOne
};

/**
 * @property int $id
 * @property int $product_comment_id
 * @property int $user_id
 * @property string $body
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read ProductComment $comment
 * @property-read ProductCommentReport|null $report
 */
class ProductCommentReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_comment_id',
        'user_id',
        'body',
    ];

    protected $with = [
        'user',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function hasReported(): bool
    {
        return $this->report !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ProductComment::class, 'product_comment_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ProductCommentReport::class, 'product_comment_reply_id');
    }
}
