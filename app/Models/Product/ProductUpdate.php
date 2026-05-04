<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\ProductPreviewType;
use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model,
    Builder,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
};
use Illuminate\Support\{Facades\File, Str};

/**
 * ProductUpdate Model
 *
 * Represents pending product updates submitted by sellers for review.
 *
 * @property int $id
 * @property int $seller_id
 * @property int $product_id
 * @property int $category_id
 * @property int|null $sub_category_id
 * @property string $name
 * @property string|null $description
 * @property array|null $options
 * @property string|null $version
 * @property string|null $demo_link
 * @property string|null $tags
 * @property string|null $preview_type
 * @property string|null $preview_image
 * @property string|null $preview_video
 * @property string|null $preview_audio
 * @property array|null $main_file ['type' => 'local'|'external', 'path' => string, 'name' => string|null, 'source' => string|null]
 * @property array|null $gallery
 * @property float|null $regular_price
 * @property float|null $extended_price
 * @property bool $is_supported
 * @property string|null $support_instructions
 * @property bool $purchasing_status
 * @property bool $is_free
 * @property string|null $regular_price_label
 * @property string|null $extended_price_label
 * @property array|null $regular_extra_features
 * @property array|null $extended_extra_features
 * @property bool $has_custom_services
 * @property string|null $custom_services
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read User $seller
 * @property-read Product $product
 * @property-read ProductCategory $category
 * @property-read ProductSubCategory|null $subCategory
 * @property-read object $price
 * @property-read string $thumbnail_url
 * @property-read string $preview_image_url
 * @property-read string|null $preview_video_url
 * @property-read string|null $preview_audio_url
 * @property-read array $gallery_links
 *
 * @method static Builder whereEditorCategories($editor)
 * @method static Builder supported()
 */
class ProductUpdate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_updates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'product_id',
        'name',
        'description',
        'category_id',
        'sub_category_id',
        'options',
        'version',
        'demo_link',
        'tags',
        'preview_type',
        'preview_image',
        'preview_video',
        'preview_audio',
        'main_file',
        'gallery',
        'regular_price',
        'extended_price',
        'is_supported',
        'support_instructions',
        'purchasing_status',
        'is_free',
        'support_package_id',
        'regular_price_label',
        'extended_price_label',
        'regular_extra_features',
        'extended_extra_features',
        'has_custom_services',
        'custom_services',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'category',
        'subCategory',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'main_file' => 'array',
            'gallery' => 'array',
            'regular_extra_features' => 'array',
            'extended_extra_features' => 'array',
            'has_custom_services' => 'boolean',
            'is_supported' => 'boolean',
            'is_free' => 'boolean',
            'purchasing_status' => 'boolean',
            'regular_price' => 'float',
            'extended_price' => 'float',
            'preview_type' => ProductPreviewType::class,
        ];
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter supported products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSupported(Builder $query): Builder
    {
        return $query->where('is_supported', true);
    }

    // =========================================================================
    // BOOLEAN CHECK METHODS
    // =========================================================================

    /**
     * Check if the product update is free.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->is_free === true;
    }

    /**
     * Check if purchasing is enabled.
     *
     * @return bool
     */
    public function isPurchasingEnabled(): bool
    {
        return $this->purchasing_status === true;
    }

    /**
     * Check if the product update has support enabled.
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return $this->is_supported === true;
    }

    /**
     * Check if custom services are enabled.
     *
     * @return bool
     */
    public function hasCustomServices(): bool
    {
        return $this->has_custom_services === true;
    }

    /**
     * Check if main file is stored externally.
     *
     * @return bool
     */
    public function isMainFileExternal(): bool
    {
        return isset($this->main_file['type']) && $this->main_file['type'] === 'external';
    }

    /**
     * Check if preview type is image.
     *
     * @return bool
     */
    public function isImagePreview(): bool
    {
        return $this->preview_type === ProductPreviewType::IMAGE->value;
    }

    /**
     * Check if preview type is video.
     *
     * @return bool
     */
    public function isVideoPreview(): bool
    {
        return $this->preview_type === ProductPreviewType::VIDEO->value;
    }

    /**
     * Check if preview type is audio.
     *
     * @return bool
     */
    public function isAudioPreview(): bool
    {
        return $this->preview_type === ProductPreviewType::AUDIO->value;
    }

    // =========================================================================
    // GETTER METHODS
    // =========================================================================

    /**
     * Get total regular price including buyer fees.
     */
    public function getRegularPrice(): float
    {
        return $this->regular_price + ($this->category?->regular_buyer_fee ?? 0);
    }

    /**
     * Get total extended price including buyer fees.
     */
    public function getExtendedPrice(): float
    {
        return $this->extended_price
            ? $this->extended_price + ($this->category?->extended_buyer_fee ?? 0)
            : null;
    }

    /**
     * Get filtered regular extra features.
     *
     * @return array
     */
    public function getRegularExtraFeatures(): array
    {
        $features = $this->regular_extra_features;
        return is_array($features) ? array_filter($features) : [];
    }

    /**
     * Get filtered extended extra features.
     *
     * @return array
     */
    public function getExtendedExtraFeatures(): array
    {
        $features = $this->extended_extra_features;
        return is_array($features) ? array_filter($features) : [];
    }

    /**
     * Get tags as object.
     *
     * @return object
     */
    public function getTags(): object
    {
        $tags = explode(',', $this->tags ?? '');
        return (object) $tags;
    }

    /**
     * Get an array of only the properties that have been updated compared to the original product.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function getUpdatedProperties(): array
    {
        $product = $this->product;
        $updated = [];

        // Define the properties to compare with their labels
        $comparableProperties = [
            'name' => 'Name',
            'description' => 'Description',
            'category_id' => 'Category',
            'sub_category_id' => 'Sub Category',
            'options' => 'Options',
            'version' => 'Version',
            'demo_link' => 'Demo Link',
            'tags' => 'Tags',
            'preview_type' => 'Preview Type',
            'preview_image' => 'Preview Image',
            'preview_video' => 'Preview Video',
            'preview_audio' => 'Preview Audio',
            'main_file' => 'Main File',
            'gallery' => 'Gallery',
            'regular_price' => 'Regular Price',
            'extended_price' => 'Extended Price',
            'is_supported' => 'Support',
            'support_instructions' => 'Support Instructions',
            'purchasing_status' => 'Purchasing Status',
            'is_free' => 'Free Product',
            'regular_price_label' => 'Regular Price Label',
            'extended_price_label' => 'Extended Price Label',
            'regular_extra_features' => 'Regular Extra Features',
            'extended_extra_features' => 'Extended Extra Features',
            'has_custom_services' => 'Custom Services Status',
            'custom_services' => 'Custom Services',
        ];

        foreach ($comparableProperties as $property => $label) {
            $newValue = $this->$property;
            $oldValue = $product->$property ?? null;

            // Skip if the update value is null (not submitted for update)
            if ($newValue === null) {
                continue;
            }

            // Handle enum values - convert to string for comparison and storage
            if ($newValue instanceof \BackedEnum) {
                $newValue = $newValue->value;
            }
            if ($oldValue instanceof \BackedEnum) {
                $oldValue = $oldValue->value;
            }

            // Compare values (handle arrays specially)
            $isDifferent = false;
            if (is_array($newValue) || is_array($oldValue)) {
                $isDifferent = json_encode($newValue) !== json_encode($oldValue);
            } else {
                $isDifferent = $newValue !== $oldValue;
            }

            if ($isDifferent) {
                $updated[$property] = [
                    'label' => $label,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $updated;
    }

    /**
     * Check if a specific property has been updated.
     *
     * @param string $property
     * @return bool
     */
    public function hasPropertyUpdated(string $property): bool
    {
        return array_key_exists($property, $this->getUpdatedProperties());
    }

    // =========================================================================
    // ATTRIBUTES
    // =========================================================================

    /**
     * Get price object with regular and extended prices.
     *
     * @return Attribute
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: function (): object {
                $data['regular'] = $this->getRegularPrice();
                $data['extended'] = $this->getExtendedPrice();

                if ($this->product->hasValidDiscount()) {
                    $discount = $this->product->discount;
                    $data['regular'] = $discount->price->regular;
                    if ($discount->hasExtended()) {
                        $data['extended'] = $discount->price->extended;
                    }
                }

                return (object) $data;
            }
        );
    }

    /**
     * Get thumbnail URL as attribute.
     *
     * @return Attribute
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                $previewImage = $this->preview_image ?? $this->product->preview_image;

                if ($this->isImagePreview()) {
                    return thumbnailGenerator()->getUrl($previewImage, 'small');
                }

                return storageUrl($previewImage);
            },
        );
    }

    /**
     * Get preview image URL
     */
    protected function previewImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->preview_image
                ? storageUrl($this->preview_image)
                : storageUrl($this->product->preview_image),
        );
    }

    /**
     * Get preview video URL
     */
    protected function previewVideoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->preview_video
                ? storageUrl($this->preview_video)
                : storageUrl($this->product->preview_video),
        );
    }

    /**
     * Get preview audio URL
     */
    protected function previewAudioUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->preview_audio
                ? storageUrl($this->preview_audio)
                : storageUrl($this->product->preview_audio),
        );
    }

    /**
     * Get image links from gallery
     */
    protected function galleryLinks(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->gallery
                ? array_map(fn($img) => storageUrl($img), $this->gallery)
                : array_map(fn($img) => storageUrl($img), $this->product->gallery),
        );
    }

    /**
     * * Get product demo/preview link
     */
    protected function viewDemo(): Attribute
    {
        return Attribute::make(
            get: fn() => route('products.preview', encrypt($this->id)),
        );
    }

    // =========================================================================
    // FILE OPERATIONS
    // =========================================================================

    /**
     * Download the main file.
     *
     * @return mixed
     */
    public function download(): mixed
    {
        $driver = storageDriver();
        $handler = new $driver->handler;

        $siteName = Str::slug(@settings('general')->site_name);
        $mainFilePath = $this->main_file['path'] ?? '';
        $filename = $siteName . '-updated-' . time() . '-' . Str::slug($this->name) . '.' . File::extension($mainFilePath);

        return $handler->download($mainFilePath, $filename);
    }

    /**
     * Delete all associated files from storage.
     *
     * @return void
     */
    public function deleteFiles(): void
    {
        $driver = storageDriver();
        $handler = new $driver->handler;

        if ($this->preview_image) {
            $handler->delete($this->preview_image);
            thumbnailGenerator()->delete($this->preview_image);
        }

        if ($this->preview_video) {
            $handler->delete($this->preview_video);
        }

        if ($this->preview_audio) {
            $handler->delete($this->preview_audio);
        }

        if (isset($this->main_file['path']) && !$this->isMainFileExternal()) {
            $handler->delete($this->main_file['path']);
        }

        if ($this->gallery) {
            foreach ($this->gallery as $screenshot) {
                $handler->delete($screenshot);
            }
        }
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the seller that owns the product update.
     *
     * @return BelongsTo<User, ProductUpdate>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the product being updated.
     *
     * @return BelongsTo<Product, ProductUpdate>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category of the product update.
     *
     * @return BelongsTo<ProductCategory, ProductUpdate>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the sub-category of the product update.
     *
     * @return BelongsTo<ProductSubCategory, ProductUpdate>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id');
    }
}
