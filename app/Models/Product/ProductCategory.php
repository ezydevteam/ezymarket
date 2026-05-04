<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\ProductCategoryPreviewType;
use App\Scopes\SortableScope;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\HasMany
};

class ProductCategory extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'product_categories';

    public const int SINGLE_SELECT = 1;
    public const int MULTIPLE_SELECT = 2;

    protected $fillable = [
        'name',
        'slug',
        'title',
        'description',
        'options',
        'regular_buyer_fee',
        'extended_buyer_fee',
        'preview_type',
        'preview_file_size',
        'gallery_images_count',
        'main_file_types',
        'main_file_size',
        'total_views',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'preview_type' => ProductCategoryPreviewType::class,
            'preview_file_size' => 'integer',
            'gallery_images_count' => 'integer',
            'main_file_size' => 'integer',
            'total_views' => 'integer',
        ];
    }


    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);

        static::creating(function ($category) {
            if (!$category->sort_id) {
                $category->sort_id = self::withoutGlobalScope(SortableScope::class)->max('sort_id') + 1;
            }
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn() => route('categories.category', $this->slug)
        );
    }

    protected function sectionTitle(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title ? $this->name . ' - ' . $this->title : $this->name
        );
    }

    protected function previewTypeName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->preview_type?->label() ?? 'Unknown'
        );
    }

    public function isImagePreview(): bool
    {
        return $this->preview_type === ProductCategoryPreviewType::IMAGE;
    }

    public function isVideoPreview(): bool
    {
        return $this->preview_type === ProductCategoryPreviewType::VIDEO;
    }

    public function isAudioPreview(): bool
    {
        return $this->preview_type === ProductCategoryPreviewType::AUDIO;
    }

    public function getAllowedFileTypes(): string
    {
        $types = array_filter(array_map('trim', explode(',', (string) $this->main_file_types)));
        $extraTypes = [];

        if ($this->isVideoPreview()) {
            $extraTypes = array_merge($extraTypes, ['mp4', 'webm']);
        }

        if ($this->isAudioPreview()) {
            $extraTypes = array_merge($extraTypes, ['mp3', 'wav']);
        }

        $types = array_merge($types, $extraTypes, ['jpeg', 'jpg', 'png']);
        $types = array_unique($types);

        return implode(',', array_map(fn($type) => '.' . ltrim($type, '.'), $types));
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(ProductSubCategory::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public static function getTypeOptions(): array
    {
        return [
            self::SINGLE_SELECT => translate('Single Select'),
            self::MULTIPLE_SELECT => translate('Multiple Select'),
        ];
    }
}
