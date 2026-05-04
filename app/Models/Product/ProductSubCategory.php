<?php

namespace App\Models\Product;

use App\Enums\Product\ProductCategoryPreviewType;
use App\Scopes\SortableScope;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasMany
};

/**
 * Product Sub-Category Model
 *
 * Represents sub-categories that belong to main product categories.
 * Sub-categories help organize products into more specific groups.
 *
 * @property int $id
 * @property string $name Sub-category display name
 * @property string $slug URL-friendly identifier
 * @property string|null $title SEO meta title
 * @property string|null $description SEO meta description
 * @property int $category_id Parent category ID
 * @property int $views Total view count
 * @property int|null $sort_id Sort order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ProductCategory $category Parent category relationship
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products Products in this sub-category
 * @property-read int $products_count Number of products
 */
class ProductSubCategory extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'product_sub_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'title',
        'description',
        'category_id',
        'regular_buyer_fee',
        'extended_buyer_fee',
        'preview_type',
        'preview_file_size',
        'gallery_images_count',
        'main_file_types',
        'main_file_size',
        'total_views',
        'sort_id',
    ];

    protected function casts(): array
    {
        return [
            'preview_type' => ProductCategoryPreviewType::class,
            'preview_file_size' => 'integer',
            'gallery_images_count' => 'integer',
            'main_file_size' => 'integer',
            'total_views' => 'integer',
            'regular_buyer_fee' => 'float',
            'extended_buyer_fee' => 'float',
        ];
    }

    /**
     * Boot the model and add global scopes.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);

        static::creating(function ($subCategory) {
            if (!$subCategory->sort_id) {
                $subCategory->sort_id = self::withoutGlobalScope(SortableScope::class)->max('sort_id') + 1;
            }
        });
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Get the public URL link for this sub-category.
     *
     * @return string
     */

    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn() => route('categories.sub-category', [$this->category->slug, $this->slug]),
        );
    }

    /**
     * Get the section title for this sub-category.
     *
     * @return Attribute
     */
    protected function sectionTitle(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title ? $this->name . ' - ' . $this->title : $this->name
        );
    }

    /**
     * Get the parent product category that owns this sub-category.
     *
     * @return BelongsTo<ProductCategory, ProductSubCategory>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get all products that belong to this sub-category.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }
}
