<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\ProductHistoryTitle;
use App\Models\{Admin, User};
use Illuminate\Database\Eloquent\{
    Model,
    Factories\HasFactory,
    Casts\Attribute,
    Relations\BelongsTo
};

/**
 * @property int $id
 * @property int|null $seller_id
 * @property int|null $editor_id
 * @property int|null $admin_id
 * @property int $product_id
 * @property ProductHistoryTitle $title
 * @property string|null $body
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User|null $seller
 * @property-read Editor|null $editor
 * @property-read Admin|null $admin
 * @property-read Product $product
 */
class ProductHistory extends Model
{
    use HasFactory;

    protected $table = 'product_histories';

    protected $fillable = [
        'seller_id',
        'admin_id',
        'product_id',
        'title',
        'body',
    ];

    protected $with = [
        'seller',
        'admin',
    ];

    protected function casts(): array
    {
        return [
            'title' => ProductHistoryTitle::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function badge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title->badgeHTML(),
        );
    }

    public function icon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title->icon(),
        );
    }

    public function titleLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title->label(),
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
