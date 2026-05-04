<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\{Product\ProductReportStatus, User\UserStatus};
use App\Models\User;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

/**
 * @property int $id
 * @property bool $auto_restrict
 * @property int $restrict_threshold
 * @property int $restrict_days
 * @property bool $auto_delete
 * @property int $delete_threshold
 * @property bool $restrict_reporter
 * @property int $reporter_days
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ProductReportSetting extends Model
{
    use HasFactory;

    protected $table = 'product_report_settings';

    protected $fillable = [
        'auto_restrict',
        'restrict_threshold',
        'restrict_days',
        'auto_delete',
        'delete_threshold',
        'restrict_reporter',
        'reporter_threshold',
        'reporter_days',
    ];

    protected function casts(): array
    {
        return [
            'auto_restrict' => 'boolean',
            'auto_delete' => 'boolean',
            'restrict_reporter' => 'boolean',
            'restrict_threshold' => 'integer',
            'restrict_days' => 'integer',
            'delete_threshold' => 'integer',
            'reporter_threshold' => 'integer',
            'reporter_days' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope for enabled settings
     */
    public function scopeEnabled($query)
    {
        return $query->where(function ($q) {
            $q->where('auto_restrict', true)
                ->orWhere('auto_delete', true)
                ->orWhere('restrict_reporter', true);
        });
    }

    /**
     * Check if product restriction is enabled
     */
    public function isProductRestrictionEnabled(): bool
    {
        return $this->auto_restrict;
    }

    /**
     * Check if product deletion is enabled
     */
    public function isProductDeletionEnabled(): bool
    {
        return $this->auto_delete;
    }

    /**
     * Check if reporter restriction is enabled
     */
    public function isReporterRestrictionEnabled(): bool
    {
        return $this->restrict_reporter;
    }

    /**
     * Check if a product should be restricted based on report count
     */
    public function shouldRestrictProduct(int $reportCount): bool
    {
        return $this->auto_restrict && $reportCount >= $this->restrict_threshold;
    }

    /**
     * Check if a product should be deleted based on report count
     */
    public function shouldDeleteProduct(int $reportCount): bool
    {
        return $this->auto_delete && $reportCount >= $this->delete_threshold;
    }

    /**
     * Check if a product's restriction period has expired
     */
    public function isProductRestrictionExpired(Product $product): bool
    {
        if (!$product->isRestricted() || !$product->restricted_at) {
            return false;
        }

        return $product->restricted_at->addDays($this->restrict_days)->isPast();
    }


    /**
     * Apply automatic actions to a product based on its report count
     * Called when a new report is submitted or report status changes
     */
    public function applyProductActions(Product $product): array
    {
        $actions = [];
        $reportCount = $product->reportCounter();

        // Check for deletion first (higher threshold)
        if ($this->shouldDeleteProduct($reportCount)) {
            $product->softDelete();
            $actions[] = 'deleted';
        }
        // If not deleted, check for restriction
        elseif ($this->shouldRestrictProduct($reportCount)) {
            if (!$product->isRestricted()) {
                $reason = "Automatically restricted due to {$reportCount} reports (threshold: {$this->restrict_threshold})";
                $product->restrict($reason);
                $actions[] = 'restricted';
            }
        }

        return $actions;
    }

    /**
     * Unrestrict products whose restriction period has expired
     * Should be called via scheduled task/cron job
     */
    public static function unrestrictExpiredProducts(): int
    {
        $settings = static::getInstance();
        $expiredProducts = Product::restricted()
            ->whereNotNull('restricted_at')
            ->where('restricted_at', '<=', now()->subDays($settings->restrict_days))
            ->get();

        $unrestrictedCount = 0;
        foreach ($expiredProducts as $product) {
            if ($settings->isProductRestrictionExpired($product)) {
                $product->unrestrict();
                $unrestrictedCount++;
            }
        }

        return $unrestrictedCount;
    }

    /**
     * Suspend a reporter who has submitted too many false reports
     * Called when a report is marked as cancelled/false
     */
    public function suspendFalseReporter(User $reporter): bool
    {
        if (!$this->restrict_reporter) {
            return false;
        }

        // Skip if user is already suspended
        if ($reporter->isSuspended()) {
            return false;
        }

        // Count cancelled/false reports within the monitoring period
        $falseReports = $reporter->reports()
            ->where('status', ProductReportStatus::CANCELLED)
            ->where('created_at', '>=', now()->subDays($this->reporter_days))
            ->count();

        // Check if threshold is reached - suspend the user
        if ($falseReports >= $this->reporter_threshold) {
            $reporter->update(['status' => UserStatus::SUSPENDED]);
            return true;
        }

        return false;
    }

    /**
     * Reactivate suspended reporters whose restriction period has expired
     * Should be called via scheduled task/cron job
     */
    public static function reactivateExpiredReporters(): int
    {
        $settings = static::getInstance();

        if (!$settings->restrict_reporter) {
            return 0;
        }

        // Find suspended users who were suspended due to false reports
        // and whose suspension period has expired
        $suspendedReporters = User::where('status', UserStatus::SUSPENDED)
            ->whereHas('reports', function ($query) use ($settings) {
                $query->where('status', ProductReportStatus::CANCELLED);
            })
            ->where('updated_at', '<=', now()->subDays($settings->reporter_days))
            ->get();

        $reactivatedCount = 0;
        foreach ($suspendedReporters as $reporter) {
            // Double-check: count recent false reports to ensure they're still below threshold
            $recentFalseReports = $reporter->reports()
                ->where('status', ProductReportStatus::CANCELLED)
                ->where('created_at', '>=', now()->subDays($settings->reporter_days))
                ->count();

            // Only reactivate if they no longer exceed the threshold
            if ($recentFalseReports < $settings->reporter_threshold) {
                $reporter->update(['status' => UserStatus::ACTIVE]);
                $reactivatedCount++;
            }
        }

        return $reactivatedCount;
    }

    /**
     * Get the singleton settings instance (cached for 24 hours)
     */
    public static function getInstance(): self
    {
        return cache()->remember('product_report_settings', 86400, fn() => static::first() ?? static::create([]));
    }

    /**
     * Update settings and clear cache
     */
    public function updateSettings(array $data): bool
    {
        $updated = $this->update($data);
        cache()->forget('product_report_settings');
        return $updated;
    }

    /**
     * Get all thresholds as an array
     */
    public function getThresholds(): array
    {
        return [
            'product_restriction' => [
                'threshold' => $this->restrict_threshold,
                'enabled' => $this->auto_restrict,
                'period_days' => $this->restrict_days,
            ],
            'product_deletion' => [
                'threshold' => $this->delete_threshold,
                'enabled' => $this->auto_delete,
            ],
            'reporter_restriction' => [
                'enabled' => $this->restrict_reporter,
                'period_days' => $this->reporter_days,
            ],
        ];
    }
}
