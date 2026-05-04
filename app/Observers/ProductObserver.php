<?php

namespace App\Observers;

use App\Enums\Product\{ProductStatus, ProductHistoryTitle};
use App\Models\Product\{Product, ProductHistory};
use Illuminate\Support\Facades\Cache;

/**
 * Product Model Observer
 *
 * Handles automatic operations during product lifecycle:
 * - File cleanup on deletion
 * - Status change tracking
 * - History logging (using ProductHistoryTitle enum)
 * - Cache invalidation
 * - Search index updates
 * - Statistics updates
 */
class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     * Triggered before a product is created
     */
    public function creating(Product $product): void
    {
        // Set default values if not set
        if (!isset($product->total_sales)) {
            $product->total_sales = 0;
        }

        if (!isset($product->total_views)) {
            $product->total_views = 0;
        }

        if (!isset($product->total_reviews)) {
            $product->total_reviews = 0;
        }
    }

    /**
     * Handle the Product "created" event.
     * Triggered after a product is created
     */
    public function created(Product $product): void
    {
        // Create initial history entry
        // TODO: Implement logic to determine if seller is trusted (check seller level, stats, etc.)
        // For now, all submissions are treated as regular submissions
        $this->createHistoryEntry(
            $product,
            ProductHistoryTitle::SUBMISSION,
            'Product submitted for review'
        );

        // Invalidate relevant caches
        $this->invalidateProductCaches($product);
    }

    /**
     * Handle the Product "updating" event.
     * Triggered before a product is updated
     */
    public function updating(Product $product): void
    {
        // Track status changes with proper history titles
        if ($product->isDirty('status')) {
            $oldStatus = $product->getOriginal('status');
            $newStatus = $product->status;

            // Store previous status if becoming restricted
            if ($newStatus === ProductStatus::RESTRICTED && !$product->previous_status) {
                $product->previous_status = $oldStatus;
            }

            // Determine appropriate history title based on status transition
            $historyTitle = $this->getHistoryTitleForStatusChange($oldStatus, $newStatus);
            $body = $this->getStatusChangeDescription($oldStatus, $newStatus, $product);

            if ($historyTitle) {
                $this->createHistoryEntry($product, $historyTitle, $body);
            }
        }

        // Track price changes (no specific enum for this, skip or use custom logging)
        if ($product->isDirty(['regular_price', 'extended_price'])) {
            $product->price_updated_at = now();
        }

        // Track featured status changes
        if ($product->isDirty('is_featured')) {
            if ($product->is_featured) {
                $product->featured_at = now();
            } else {
                $product->featured_at = null;
            }
        }
    }

    /**
     * Handle the Product "updated" event.
     * Triggered after a product is updated
     */
    public function updated(Product $product): void
    {
        // Invalidate caches
        $this->invalidateProductCaches($product);

        // Clear dashboard caches if meaningful changes occurred
        if ($this->shouldClearDashboardCache($product)) {
            $this->clearDashboardProductCaches();
        }

        // Update search index if needed (implement your search service)
        // SearchService::updateProduct($product);
    }

    /**
     * Handle the Product "deleting" event.
     * Triggered before a product is deleted (soft or hard)
     */
    public function deleting(Product $product): void
    {
        // If force deleting (hard delete), clean up files
        if ($product->isForceDeleting()) {
            $product->deleteFiles();

            // Delete related records
            if ($product->productUpdate) {
                $product->productUpdate->deleteFiles();
                $product->productUpdate->forceDelete();
            }

            // Clean up related data
            $product->discount()->forceDelete();
            $product->cartproducts()->forceDelete();
            $product->favorites()->forceDelete();
        }
    }

    /**
     * Handle the Product "deleted" event.
     * Triggered after a product is deleted (soft delete)
     */
    public function deleted(Product $product): void
    {
        // Invalidate caches
        $this->invalidateProductCaches($product);
    }

    /**
     * Handle the Product "restored" event.
     * Triggered after a soft-deleted product is restored
     */
    public function restored(Product $product): void
    {
        // Note: Product restoration is typically not tracked in ProductHistory enum

        // Invalidate caches
        $this->invalidateProductCaches($product);
    }

    /**
     * Handle the Product "forceDeleted" event.
     * Triggered after a product is permanently deleted
     */
    public function forceDeleted(Product $product): void
    {
        // Clean up all caches
        $this->invalidateProductCaches($product);

        // Remove from search index
        // SearchService::removeProduct($product);
    }

    /**
     * Create a history entry for the product using ProductHistoryTitle enum
     */
    protected function createHistoryEntry(
        Product $product,
        ProductHistoryTitle $title,
        ?string $body = null
    ): void {
        // Only create if product has an ID (skip during creation before save)
        if (!$product->exists) {
            return;
        }

        ProductHistory::create([
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'admin_id' => authAdmin()?->id,
            'title' => $title,
            'body' => $body,
        ]);
    }

    /**
     * Get appropriate history title for status change
     */
    protected function getHistoryTitleForStatusChange($oldStatus, $newStatus): ?ProductHistoryTitle
    {
        // Map status transitions to history titles
        return match (true) {
            // Approval transitions
            $newStatus === ProductStatus::APPROVED &&
                $oldStatus === ProductStatus::PENDING
            => ProductHistoryTitle::SUBMISSION_APPROVED,

            $newStatus === ProductStatus::APPROVED &&
                $oldStatus === ProductStatus::RESUBMITTED
            => ProductHistoryTitle::RESUBMISSION_APPROVED,

            // Rejection/Revision transitions
            $newStatus === ProductStatus::NEEDS_REVISION
            => ProductHistoryTitle::REVISION_REQUIRED,

            $newStatus === ProductStatus::REJECTED
            => ProductHistoryTitle::REJECTION,

            // Resubmission
            $newStatus === ProductStatus::RESUBMITTED
            => ProductHistoryTitle::RESUBMISSION,

            // Default: no history entry for other transitions
            default => null,
        };
    }

    /**
     * Get description for status change
     */
    protected function getStatusChangeDescription($oldStatus, $newStatus, Product $product): ?string
    {
        $description = "Status changed from {$oldStatus->label()} to {$newStatus->label()}";

        // Add rejection/revision reason if available
        if (
            $newStatus === ProductStatus::NEEDS_REVISION ||
            $newStatus === ProductStatus::REJECTED
        ) {
            if ($product->rejection_reason) {
                $description .= "\nReason: {$product->rejection_reason}";
            }
        }

        return $description;
    }

    /**
     * Invalidate product-related caches
     */
    protected function invalidateProductCaches(Product $product): void
    {
        $cacheTags = [
            "product.{$product->id}",
            "seller.{$product->seller_id}.products",
            "category.{$product->category_id}.products",
        ];

        if ($product->sub_category_id) {
            $cacheTags[] = "subcategory.{$product->sub_category_id}.products";
        }

        // Clear specific caches
        foreach ($cacheTags as $tag) {
            Cache::forget($tag);
        }

        // Clear listing caches
        Cache::forget('products.trending');
        Cache::forget('products.featured');
        Cache::forget('products.best_selling');
        Cache::forget('products.new');
    }

    /**
     * Determine if dashboard caches should be cleared
     * Only clear for meaningful changes that affect rankings/visibility
     */
    protected function shouldClearDashboardCache(Product $product): bool
    {
        return $product->wasChanged([
            'status',           // Approval/rejection affects visibility
            'total_sales',      // Affects top selling ranking
            'avg_rating',       // Affects top rated ranking
            'total_reviews',    // Affects rating calculation
            'is_featured',      // Featured products might show differently
            'deleted_at',       // Soft deletion affects visibility
        ]);
    }

    /**
     * Clear dashboard-specific product caches
     */
    protected function clearDashboardProductCaches(): void
    {
        Cache::forget('dashboard_top_selling_products');
        Cache::forget('dashboard_top_rated_products');
    }
}
