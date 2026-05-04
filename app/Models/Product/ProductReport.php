<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\{ProductReportStatus, ProductReportReason};
use App\Models\{Admin, User};
use Illuminate\Database\Eloquent\{
    Builder,
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo
};

/**
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property ProductReportReason $reason
 * @property string|null $description
 * @property array|null $screenshots
 * @property ProductReportStatus $status
 * @property string|null $admin_notes
 * @property int|null $reviewed_by_id
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Product $product
 * @property-read User $user
 * @property-read Admin|null $reviewedBy
 */
class ProductReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'reason',
        'description',
        'screenshots',
        'status',
        'admin_notes',
        'reviewed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ProductReportReason::class,
            'status' => ProductReportStatus::class,
            'screenshots' => 'array',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ==================== Attributes ====================

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badge()
        );
    }

    protected function reasonBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reason->badge()
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label()
        );
    }

    protected function reasonLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reason->label()
        );
    }

    protected function screenshotLinks(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->screenshots) || !is_array($this->screenshots)) {
                    return [];
                }

                return array_filter(
                    array_map(fn($path) => storageUrl($path), $this->screenshots)
                );
            }
        );
    }

    // ==================== Scopes ====================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ProductReportStatus::PENDING);
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('status', ProductReportStatus::REVIEWED);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', ProductReportStatus::RESOLVED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', ProductReportStatus::CANCELLED);
    }

    public function scopeByReason(Builder $query, ProductReportReason|string $reason): Builder
    {
        $reasonValue = $reason instanceof ProductReportReason ? $reason->value : $reason;
        return $query->where('reason', $reasonValue);
    }

    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }

    // ==================== Helper Methods ====================

    public function isPending(): bool
    {
        return $this->status === ProductReportStatus::PENDING;
    }

    public function isReviewed(): bool
    {
        return $this->status === ProductReportStatus::REVIEWED;
    }

    public function isResolved(): bool
    {
        return $this->status === ProductReportStatus::RESOLVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ProductReportStatus::CANCELLED;
    }

    public function hasScreenshots(): bool
    {
        return !empty($this->screenshots) && is_array($this->screenshots);
    }

    public function canBeActioned(): bool
    {
        return $this->isPending() || $this->isReviewed();
    }

    public function hasBeenReviewed(): bool
    {
        return !is_null($this->reviewed_at);
    }

    public static function getStatusOptions(): array
    {
        return ProductReportStatus::labels();
    }

    public static function getReasonOptions(): array
    {
        return ProductReportReason::labels();
    }

    // ==================== Status Update Methods ====================

    public function markAsReviewed(int $adminId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => ProductReportStatus::REVIEWED,
            'reviewed_by_id' => $adminId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function markAsResolved(int $adminId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => ProductReportStatus::RESOLVED,
            'reviewed_by_id' => $adminId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function markAsCancelled(int $adminId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => ProductReportStatus::CANCELLED,
            'reviewed_by_id' => $adminId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    // ==================== Relationships ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_id');
    }
}
