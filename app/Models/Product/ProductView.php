<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\{
    Model,
    Factories\HasFactory,
    Relations\BelongsTo
};

class ProductView extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'ip',
        'referrer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
