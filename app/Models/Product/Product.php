<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\{ProductStatus, ProductPreviewType, ProductReportStatus};
use App\Models\{CartProduct, Favorite, Purchase, User};
use App\Models\Financial\TransactionProduct;
use App\Models\Support\SupportPackage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\{
    Builder,
    Model,
    SoftDeletes,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Support\{Facades\File, Str};

/**
 * Product Model
 *
 * Represents digital products listed in the marketplace.
 *
 * @property int $id
 * @property int $seller_id
 * @property int $category_id
 * @property int|null $sub_category_id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property array|null $options
 * @property string|null $version
 * @property string|null $demo_link
 * @property string|null $tags
 * @property ProductPreviewType|null $preview_type
 * @property string|null $preview_image
 * @property string|null $preview_video
 * @property string|null $preview_audio
 * @property array|null $main_file ['type' => 'local'|'external', 'path' => string, 'name' => string|null, 'source' => string|null]
 * @property array|null $gallery
 * @property float $regular_price
 * @property float|null $extended_price
 * @property string|null $regular_price_label
 * @property string|null $extended_price_label
 * @property array|null $regular_extra_features
 * @property array|null $extended_extra_features
 * @property bool $has_custom_services
 * @property string|null $custom_services
 * @property bool $is_supported
 * @property string|null $support_instructions
 * @property ProductStatus $status
 * @property ProductStatus|null $previous_status
 * @property \Carbon\Carbon|null $restricted_at
 * @property string|null $restriction_reason
 * @property int $total_sales
 * @property float $total_sales_amount
 * @property float $total_earnings
 * @property int $total_reviews
 * @property float $avg_reviews
 * @property int $total_comments
 * @property int $total_views
 * @property int $total_reports
 * @property int $current_month_views
 * @property int $free_downloads
 * @property bool $purchasing_status
 * @property bool $is_premium
 * @property bool $is_free
 * @property bool $is_trending
 * @property bool $is_best_selling
 * @property bool $is_on_discount
 * @property bool $is_featured
 * @property \Carbon\Carbon|null $last_updated_at
 * @property \Carbon\Carbon|null $last_discount_at
 * @property \Carbon\Carbon|null $price_updated_at
 * @property \Carbon\Carbon|null $featured_at
 * @property \Carbon\Carbon|null $premium_at
 * @property int|null $deleted_by
 * @property string|null $deletion_reason
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read User $seller
 * @property-read ProductCategory $category
 * @property-read ProductSubCategory|null $subCategory
 * @property-read ProductUpdate|null $productUpdate
 * @property-read ProductDiscount|null $discount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductHistory> $histories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductChangeLog> $changelogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductReview> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductComment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductReport> $reports
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CartProduct> $cartProducts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TransactionProduct> $transactionProducts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Favorite> $favorites
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Purchase> $purchases
 *
 * @property-read string $status_name
 * @property-read string $status_icon
 * @property-read string $status_badge
 * @property-read string|null $preview_type_name
 * @property-read string|null $preview_type_icon
 * @property-read string|null $thumbnail_url
 * @property-read string|null $preview_image_url
 * @property-read string|null $preview_video_url
 * @property-read string|null $preview_audio_url
 * @property-read array|null $gallery_links
 * @property-read object $price
 * @property-read string $view_link
 * @property-read string $view_demo
 * @property-read bool $is_recently_updated
 * @property-read bool $is_purchasable
 *
 * @method static Builder active()
 * @method static Builder notDeleted()
 * @method static Builder pending()
 * @method static Builder softRejected()
 * @method static Builder resubmitted()
 * @method static Builder approved()
 * @method static Builder hardRejected()
 * @method static Builder restricted()
 * @method static Builder notRestricted()
 * @method static Builder onDiscount()
 * @method static Builder supported()
 * @method static Builder premium()
 * @method static Builder free()
 * @method static Builder notFree()
 * @method static Builder purchasingEnabled()
 * @method static Builder trending()
 * @method static Builder bestSelling()
 * @method static Builder featured()
 * @method static Builder whereEditorCategories($editor)
 *
 * @package App\Models\Product
 */
class Product extends Model
{
    use HasFactory, Sluggable, SoftDeletes;

    // =========================================================================
    // CONFIGURATION
    // =========================================================================

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'name',
        'slug',
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
        'regular_price_label',
        'extended_price_label',
        'regular_extra_features',
        'extended_extra_features',
        'has_custom_services',
        'custom_services',
        'is_supported',
        'support_instructions',
        'status',
        'previous_status',
        'restricted_at',
        'restriction_reason',
        'total_sales',
        'total_sales_amount',
        'total_earnings',
        'total_reviews',
        'avg_reviews',
        'total_comments',
        'total_views',
        'total_reports',
        'current_month_views',
        'free_downloads',
        'purchasing_status',
        'is_premium',
        'is_free',
        'support_package_id',
        'is_trending',
        'is_best_selling',
        'is_on_discount',
        'is_featured',
        'last_updated_at',
        'last_discount_at',
        'price_updated_at',
        'featured_at',
        'premium_at',
        'deleted_by',
        'deletion_reason',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'price',
        'view_link',
        'thumbnail_url',
        'preview_image_url',
        'preview_video_url',
        'preview_audio_url',
        'gallery_links'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'previous_status' => ProductStatus::class,
            'preview_type' => ProductPreviewType::class,
            'options' => 'array',
            'main_file' => 'array',
            'gallery' => 'array',
            'regular_extra_features' => 'array',
            'extended_extra_features' => 'array',
            'has_custom_services' => 'boolean',
            'is_supported' => 'boolean',
            'is_premium' => 'boolean',
            'is_free' => 'boolean',
            'is_trending' => 'boolean',
            'is_best_selling' => 'boolean',
            'purchasing_status' => 'boolean',
            'is_on_discount' => 'boolean',
            'is_featured' => 'boolean',
            'regular_price' => 'float',
            'extended_price' => 'float',
            'discount_percentage' => 'float',
            'restricted_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'last_discount_at' => 'datetime',
            'price_updated_at' => 'datetime',
            'discount_starting_date' => 'datetime',
            'discount_ending_date' => 'datetime',
            'featured_at' => 'datetime',
            'premium_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
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
     * The "booted" method of the model.
     *
     * Add global scope to hide products from soft-deleted sellers on frontend only.
     * Admin panel should see all products regardless of seller deletion status.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('activeSeller', function (Builder $query) {
            // Only apply filter if not in admin context
            if (!request()->is(adminPath() . '/*') && !app()->runningInConsole()) {
                $query->whereHas('seller', function ($q) {
                    $q->whereNull('deleted_at');
                });
            }
        });
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope for active products (not pending, hard rejected, draft, or deleted).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ProductStatus::PENDING->value,
            ProductStatus::REJECTED->value,
            ProductStatus::DRAFT->value,
        ]);
    }

    /**
     * Scope for draft products.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::DRAFT->value);
    }

    /**
     * Scope for not deleted products (excludes soft deleted).
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope for pending products.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::PENDING->value);
    }

    /**
     * Scope for products that need revision.
     */
    public function scopeNeedsRevision(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::NEEDS_REVISION->value);
    }

    /**
     * Scope for resubmitted products.
     */
    public function scopeResubmitted(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::RESUBMITTED->value);
    }

    /**
     * Scope for approved products.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::APPROVED->value);
    }

    /**
     * Scope for rejected products.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::REJECTED->value);
    }

    /**
     * Scope for restricted products.
     */
    public function scopeRestricted(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::RESTRICTED->value);
    }

    /**
     * Scope for not restricted products.
     */
    public function scopeNotRestricted(Builder $query): Builder
    {
        return $query->where('status', '!=', ProductStatus::RESTRICTED->value);
    }

    /**
     * Scope for products on discount.
     */
    public function scopeOnDiscount(Builder $query): Builder
    {
        return $query->where('is_on_discount', true);
    }

    /**
     * Scope for supported products.
     */
    public function scopeSupported(Builder $query): Builder
    {
        return $query->where('is_supported', true);
    }

    /**
     * Scope for premium products.
     */
    public function scopePremium(Builder $query): Builder
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for free products.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }

    /**
     * Scope for non-free products.
     */
    public function scopeNotFree(Builder $query): Builder
    {
        return $query->where('is_free', false);
    }

    /**
     * Scope for products with purchasing enabled.
     */
    public function scopePurchasingEnabled(Builder $query): Builder
    {
        return $query->free()->where('purchasing_status', true);
    }

    /**
     * Scope for trending products.
     */
    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    /**
     * Scope for best selling products.
     */
    public function scopeBestSelling(Builder $query): Builder
    {
        return $query->where('is_best_selling', true);
    }

    /**
     * Scope for featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for products in editor's assigned categories.
     */
    public function scopeWhereEditorCategories(Builder $query, $editor): Builder
    {
        $categoryIds = $editor->categories->pluck('id')->toArray();
        return $query->whereIn('category_id', $categoryIds);
    }

    // =========================================================================
    // BOOLEAN CHECKERS (is*, has*)
    // =========================================================================

    /**
     * Check if product is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === ProductStatus::DRAFT;
    }

    /**
     * Check if product is pending.
     */
    public function isPending(): bool
    {
        return $this->status === ProductStatus::PENDING;
    }

    /**
     * Check if product needs revision.
     */
    public function isNeedsRevision(): bool
    {
        return $this->status === ProductStatus::NEEDS_REVISION;
    }

    /**
     * Check if product is resubmitted.
     */
    public function isResubmitted(): bool
    {
        return $this->status === ProductStatus::RESUBMITTED;
    }

    /**
     * Check if product is pending review (pending or resubmitted).
     */
    public function isPendingReview(): bool
    {
        return $this->isPending() || $this->isResubmitted();
    }

    /**
     * Check if product is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === ProductStatus::APPROVED;
    }

    /**
     * Check if product is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === ProductStatus::REJECTED;
    }

    /**
     * Check if product is restricted.
     */
    public function isRestricted(): bool
    {
        return $this->status === ProductStatus::RESTRICTED;
    }

    /**
     * Check if product is soft deleted.
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * Check if preview type is image.
     */
    public function isImagePreview(): bool
    {
        return $this->preview_type === ProductPreviewType::IMAGE;
    }

    /**
     * Check if preview type is video.
     */
    public function isVideoPreview(): bool
    {
        return $this->preview_type === ProductPreviewType::VIDEO;
    }

    /**
     * Check if preview type is audio.
     */
    public function isAudioPreview(): bool
    {
        return $this->preview_type === ProductPreviewType::AUDIO;
    }

    /**
     * Check if product is on discount.
     */
    public function isOnDiscount(): bool
    {
        return $this->is_on_discount === true && $this->discount !== null;
    }

    /**
     * Check if product is supported.
     */
    public function isSupported(): bool
    {
        return $this->is_supported === true;
    }

    /**
     * Check if product is premium.
     */
    public function isPremium(): bool
    {
        return $this->is_premium === true;
    }

    /**
     * Check if product is free.
     */
    public function isFree(): bool
    {
        return $this->is_free === true;
    }

    /**
     * Check if purchasing is enabled for this product.
     */
    public function isPurchasingEnabled(): bool
    {
        if ($this->isFree()) {
            return $this->purchasing_status === true;
        }
        return true;
    }

    /**
     * Check if product is trending.
     */
    public function isTrending(): bool
    {
        return $this->is_trending === true;
    }

    /**
     * Check if product is best selling.
     */
    public function isBestSelling(): bool
    {
        return $this->is_best_selling === true;
    }

    /**
     * Check if main file is external (URL).
     */
    public function isMainFileExternal(): bool
    {
        return isset($this->main_file['type']) && $this->main_file['type'] === 'external';
    }

    /**
     * Check if product is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured === true;
    }

    /**
     * Check if product has a pending update.
     */
    public function hasUpdate(): bool
    {
        return (bool) $this->productUpdate;
    }

    /**
     * Check if product has extended price.
     */
    public function hasExtendedPrice(): bool
    {
        return !empty($this->extended_price);
    }

    /**
     * Check if product has sales.
     */
    public function hasSales(): bool
    {
        return $this->total_sales > 0;
    }

    /**
     * Check if product has a discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount !== null;
    }

    /**
     * Check if product has a valid discount.
     */
    public function hasValidDiscount(): bool
    {
        return (bool) $this->hasDiscount() && $this->discount->isActive();
    }

    /**
     * Check if extended price is on discount.
     */
    public function isExtendedOnDiscount(): bool
    {
        if (!$this->hasExtendedPrice()) {
            return false;
        }

        return $this->hasValidDiscount() && $this->discount->hasExtended();
    }

    /**
     * Check if product has changelogs.
     */
    public function hasChangelogs(): bool
    {
        return $this->changelogs->count() > 0;
    }

    /**
     * Check if product has reviews.
     */
    public function hasReviews(): bool
    {
        return $this->total_reviews > 0;
    }

    /**
     * Check if product has custom services enabled.
     */
    public function hasCustomServices(): bool
    {
        return $this->has_custom_services === true;
    }

    // =========================================================================
    // ATTRIBUTES (Laravel 11 Accessors)
    // =========================================================================

    /**
     * Get the status name.
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->status->label(),
        );
    }

    /**
     * Get the status icon.
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->status->icon(),
        );
    }

    /**
     * Get the status badge class.
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->status->badgeClass(),
        );
    }

    /**
     * Get the preview type name.
     */
    protected function previewTypeName(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->preview_type?->label(),
        );
    }

    /**
     * Get the preview type icon.
     */
    protected function previewTypeIcon(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->preview_type?->icon(),
        );
    }

    /**
     * Get default thumbnail URL.
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->getThumbnail(),
        );
    }

    /**
     * Get preview image URL.
     */
    protected function previewImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->preview_image ? storageUrl($this->preview_image) : null,
        );
    }

    /**
     * Get preview video URL.
     */
    protected function previewVideoUrl(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->preview_video ? storageUrl($this->preview_video) : null,
        );
    }

    /**
     * Get preview audio URL.
     */
    protected function previewAudioUrl(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->preview_audio ? storageUrl($this->preview_audio) : null,
        );
    }

    /**
     * Get image links from gallery.
     */
    protected function galleryLinks(): Attribute
    {
        return Attribute::make(
            get: fn(): ?array => $this->gallery
                ? array_map(fn($img) => storageUrl($img), $this->gallery)
                : null
        );
    }

    /**
     * Get calculated price object with regular and extended prices.
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: function() {
                $onDiscount = $this->isOnDiscount();
                $discountPrice = $onDiscount ? $this->discount->regular_price : null;

                return (object) [
                    'regular' => $this->regular_price + ($this->category?->regular_buyer_fee ?? 0),
                    'extended' => $this->extended_price
                        ? $this->extended_price + ($this->category?->extended_buyer_fee ?? 0)
                        : null,
                    'is_on_discount' => $onDiscount,
                    'discount_regular' => $discountPrice ? $discountPrice + ($this->category?->regular_buyer_fee ?? 0) : null
                ];
            }
        );
    }

    /**
     * Get user side product view link.
     */
    protected function viewLink(): Attribute
    {
        return Attribute::make(
            get: fn(): string => route('products.show', [$this->slug, $this->id]),
        );
    }

    /**
     * Get product demo/preview link.
     */
    protected function viewDemo(): Attribute
    {
        return Attribute::make(
            get: fn(): string => route('products.preview', encrypt($this->id)),
        );
    }

    /**
     * Check if product is recently updated (within last 30 days).
     */
    protected function isRecentlyUpdated(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->last_updated_at && $this->last_updated_at->gte(now()->subDays(30)),
        );
    }

    /**
     * Check if product is purchasable (approved, not restricted, not deleted).
     */
    protected function isPurchasable(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->isApproved() && !$this->isRestricted() && !$this->trashed(),
        );
    }

    // =========================================================================
    // GETTER METHODS
    // =========================================================================

    /**
     * Get all status options for dropdowns.
     *
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        return ProductStatus::options();
    }

    /**
     * Get thumbnail URL with custom size.
     *
     * @param string $size Size variant: 'small', 'medium', 'large'
     */
    public function getThumbnail(string $size = 'small'): ?string
    {
        if (!$this->preview_image) {
            return null;
        }

        if ($this->isImagePreview()) {
            return thumbnailGenerator()->getUrl($this->preview_image, $size);
        }

        return storageUrl($this->preview_image);
    }

    /**
     * Get the image link (preview image or thumbnail).
     */
    public function getImageLink(): ?string
    {
        if ($this->preview_image) {
            return $this->preview_image_url;
        }
        return $this->thumbnail_url;
    }

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
     * Get filtered regular extra features array.
     *
     * @return array<int, string>
     */
    public function getRegularExtraFeatures(): array
    {
        $features = $this->regular_extra_features;
        return is_array($features) ? array_filter($features) : [];
    }

    /**
     * Get filtered extended extra features array.
     *
     * @return array<int, string>
     */
    public function getExtendedExtraFeatures(): array
    {
        $features = $this->extended_extra_features;
        return is_array($features) ? array_filter($features) : [];
    }

    /**
     * Get regular extra features as comma-separated string.
     */
    public function getRegularExtraFeaturesString(): string
    {
        return $this->regular_extra_features && is_array($this->regular_extra_features)
            ? implode(', ', $this->regular_extra_features)
            : '';
    }

    /**
     * Get extended extra features as comma-separated string.
     */
    public function getExtendedExtraFeaturesString(): string
    {
        return $this->extended_extra_features && is_array($this->extended_extra_features)
            ? implode(', ', $this->extended_extra_features)
            : '';
    }

    /**
     * Get tags as array.
     */
    public function getTags(): array
    {
        $tags = explode(',', $this->tags ?? '');
        return array_map('trim', array_filter($tags));
    }

    /**
     * Get changelogs link.
     */
    public function getChangeLogsLink(): string
    {
        return route('products.show', [$this->slug, $this->id]);
    }

    /**
     * Get reviews link.
     */
    public function getReviewsLink(): string
    {
        return route('products.reviews.store', [$this->slug, $this->id]);
    }

    /**
     * Get comments link.
     */
    public function getCommentsLink(): string
    {
        return route('products.show', [$this->slug, $this->id]);
    }

    /**
     * Get support link.
     */
    public function getSupportLink(): string
    {
        return route('products.show', [$this->slug, $this->id]);
    }

    /**
     * Get report statistics for this product.
     *
     * @return array<string, int>
     */
    public function getReportStats(): array
    {
        return [
            'total' => $this->reports()->count(),
            'pending' => $this->reports()->pending()->count(),
            'reviewed' => $this->reports()->reviewed()->count(),
            'resolved' => $this->reports()->resolved()->count(),
            'cancelled' => $this->reports()->cancelled()->count(),
        ];
    }

    // =========================================================================
    // ACTION METHODS
    // =========================================================================

    /**
     * Download the main file.
     *
     * @return mixed
     */
    public function download(): mixed
    {
        $storageDriver = storageDriver();
        $handler = new $storageDriver->handler;

        $siteName = Str::slug(getSiteName());
        $mainFilePath = $this->main_file['path'] ?? '';
        $filename = $siteName . '-' . time() . '-' . Str::slug($this->name) . '.' . File::extension($mainFilePath);

        return $handler->download($mainFilePath, $filename);
    }

    /**
     * Update and return the report count.
     */
    public function reportCounter(): int
    {
        $actualCount = $this->reports()->count();
        $this->update(['total_reports' => $actualCount]);
        return $actualCount;
    }

    /**
     * Soft delete the product (reversible, files kept).
     */
    public function softDelete(?string $reason = null, ?int $deletedBy = null): void
    {
        $this->deletion_reason = $reason;
        $this->deleted_by = $deletedBy;
        $this->save();
        $this->delete();
    }

    /**
     * Hard delete the product (permanent, files removed).
     */
    public function hardDelete(): void
    {
        $this->deleteFiles();
        if ($this->productUpdate) {
            $this->productUpdate->deleteFiles();
            $this->productUpdate->forceDelete();
        }
        $this->discount()->forceDelete();
        $this->cartProducts()->forceDelete();
        $this->favorites()->forceDelete();

        $this->forceDelete();
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restoreDeleted(): void
    {
        $this->restore();
        $this->deleted_by = null;
        $this->deletion_reason = null;
        $this->save();
    }

    /**
     * Restrict the product with an optional reason.
     */
    public function restrict(?string $reason = null): void
    {
        $this->previous_status = $this->status;
        $this->status = ProductStatus::RESTRICTED;
        $this->restricted_at = now();
        $this->restriction_reason = $reason;
        $this->save();
    }

    /**
     * Unrestrict the product and optionally resolve pending reports.
     */
    public function unrestrict(bool $resolveReports = true): void
    {
        $this->status = $this->previous_status ?? ProductStatus::APPROVED;
        $this->restricted_at = null;
        $this->restriction_reason = null;
        $this->previous_status = null;
        $this->save();

        if ($resolveReports) {
            $this->resolvePendingReports();
        }
    }

    /**
     * Resolve all pending reports for this product.
     */
    public function resolvePendingReports(?string $adminNotes = null): int
    {
        $defaultNotes = $adminNotes ?? translate('Automatically resolved after product unrestriction');

        return $this->reports()->where('status', ProductReportStatus::PENDING)->update([
            'status' => ProductReportStatus::RESOLVED,
            'reviewed_by_id' => authAdmin()->id,
            'reviewed_at' => now(),
            'admin_notes' => $defaultNotes,
        ]);
    }

    // =========================================================================
    // FILE MANAGEMENT
    // =========================================================================

    /**
     * Delete the preview image and its thumbnails.
     */
    public function deletePreviewImage(): void
    {
        if ($this->preview_image) {
            // Delete all thumbnails first
            thumbnailGenerator()->delete($this->preview_image);

            // Then delete original
            $storageDriver = storageDriver();
            $handler = new $storageDriver->handler;
            $handler->delete($this->preview_image);
        }
    }

    /**
     * Delete the preview video.
     */
    public function deletePreviewVideo(): void
    {
        if ($this->preview_video) {
            $storageDriver = storageDriver();
            $handler = new $storageDriver->handler;
            $handler->delete($this->preview_video);
        }
    }

    /**
     * Delete the preview audio.
     */
    public function deletePreviewAudio(): void
    {
        if ($this->preview_audio) {
            $storageDriver = storageDriver();
            $handler = new $storageDriver->handler;
            $handler->delete($this->preview_audio);
        }
    }

    /**
     * Delete the main file (if local).
     */
    public function deleteMainFile(): void
    {
        if (!$this->isMainFileExternal() && isset($this->main_file['path'])) {
            $storageDriver = storageDriver();
            $handler = new $storageDriver->handler;
            $handler->delete($this->main_file['path']);
        }
    }

    /**
     * Delete all gallery images.
     */
    public function deleteGallery(): void
    {
        if ($this->gallery) {
            $storageDriver = storageDriver();
            $handler = new $storageDriver->handler;
            foreach ($this->gallery as $screenshot) {
                $handler->delete($screenshot);
            }
        }
    }

    /**
     * Delete all product files.
     */
    public function deleteFiles(): void
    {
        $this->deletePreviewImage();
        $this->deletePreviewVideo();
        $this->deletePreviewAudio();
        $this->deleteMainFile();
        $this->deleteGallery();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the seller that owns the product.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }

    /**
     * Get the category of the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the sub-category of the product.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id');
    }

    /**
     * Get the pending update for the product.
     */
    public function productUpdate(): HasOne
    {
        return $this->hasOne(ProductUpdate::class);
    }

    /**
     * Get the discount for the product.
     */
    public function discount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class);
    }

    /**
     * Get the history entries for the product.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ProductHistory::class);
    }

    /**
     * Get the changelogs for the product.
     */
    public function changelogs(): HasMany
    {
        return $this->hasMany(ProductChangeLog::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the comments for the product.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }

    /**
     * Get the reports for the product.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ProductReport::class);
    }

    /**
     * Get the cart products for the product.
     */
    public function cartProducts(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    /**
     * Get the transaction products for the product.
     */
    public function transactionProducts(): HasMany
    {
        return $this->hasMany(TransactionProduct::class);
    }

    /**
     * Get the favorites for the product.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the purchases for the product.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the support package associated with the product.
     */
    public function supportPackage(): BelongsTo
    {
        return $this->belongsTo(SupportPackage::class, 'support_package_id');
    }
}
