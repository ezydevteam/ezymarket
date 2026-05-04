<?php

namespace App\Models\Product;

use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasOne
};

/**
 * @property int $id
 * @property int $user_id
 * @property int $seller_id
 * @property int $product_id
 * @property int $stars
 * @property string $subject
 * @property string $body
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read User $seller
 * @property-read Product $product
 * @property-read ProductReviewReply|null $reply
 */
class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            self::updateProductAndSellerReviews($review->seller, $review->product);
        });

        static::updated(function ($review) {
            self::updateProductAndSellerReviews($review->seller, $review->product);
        });

        static::deleted(function ($review) {
            self::updateProductAndSellerReviews($review->seller, $review->product);
        });
    }

    protected static function updateProductAndSellerReviews($seller, $product)
    {
        $product->total_reviews = $product->reviews->count();
        $product->avg_reviews = $product->reviews->count() > 0 ? $product->reviews->avg('stars') : 0;
        $product->update();

        $seller->total_reviews = $seller->reviews->count();
        $seller->avg_reviews = $seller->reviews->count() > 0 ? $seller->reviews->avg('stars') : 0;
        $seller->update();
    }

    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'stars',
        'subject',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn() => route('products.review', [
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

    public function reply(): HasOne
    {
        return $this->hasOne(ProductReviewReply::class, 'product_review_id');
    }
}
