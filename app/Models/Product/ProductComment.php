<?php

namespace App\Models\Product;

use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasMany
};

/**
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property int $product_id
 * @property bool $notify_replies
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read User $seller
 * @property-read Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCommentReply> $replies
 */
class ProductComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'notify_replies',
    ];

    protected $with = [
        'replies',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'notify_replies' => 'bool',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            $comment->product->increment('total_comments');
        });

        static::deleted(function ($comment) {
            $comment->product->decrement('total_comments');
        });
    }

    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn() => route('products.comment', [
                $this->product->slug,
                $this->product->id,
                $this->id,
            ]),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProductCommentReply::class);
    }
}
