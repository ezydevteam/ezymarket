<?php

namespace App\Models\Product;

use App\Models\User;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\BelongsTo, Model};

/**
 * @property int $id
 * @property int $product_review_id
 * @property int $user_id
 * @property string $body
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read ProductReview $review
 */
class ProductReviewReply extends Model
{
    use HasFactory;

    protected $table = 'product_review_replies';

    protected $fillable = [
        'product_review_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }
}
