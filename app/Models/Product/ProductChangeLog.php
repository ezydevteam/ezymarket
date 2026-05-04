<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\{
    Model,
    Factories\HasFactory,
    Relations\BelongsTo
};

/**
 * @property int $id
 * @property int $product_id
 * @property string $version
 * @property string $log
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ProductChangeLog extends Model
{
    use HasFactory;

    protected $table = 'product_change_logs';

    protected $fillable = [
        'product_id',
        'version',
        'log',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
