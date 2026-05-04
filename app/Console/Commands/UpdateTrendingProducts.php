<?php

namespace App\Console\Commands;

use App\Enums\BadgeAlias;
use App\Models\{User, Badge};
use App\Models\Product\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Artisan};

/**
 * UpdateTrendingProducts
 *
 * Console command to update trending products based on views.
 * Awards Trend Master badges to eligible sellers.
 */
class UpdateTrendingProducts extends Command
{
    protected $signature = 'products:update-trending';

    protected $description = 'Update trending products based on monthly or total views';

    public function handle(): int
    {
        $limit = (int) data_get(settings('product'), 'trending_number', 10);
        $trendBadge = Badge::where('alias', BadgeAlias::TREND_MASTER)->first();

        DB::transaction(function () use ($limit, $trendBadge) {
            // Reset all current trending products
            Product::approved()
                ->trending()
                ->update(['is_trending' => false]);

            // Try to get trending products by current month views first
            $trendingIds = Product::approved()
                ->where('current_month_views', '>', 0)
                ->orderByDesc('current_month_views')
                ->limit($limit)
                ->pluck('id');

            // Fallback to total views if not enough products
            if ($trendingIds->count() < $limit) {
                $trendingIds = Product::approved()
                    ->where('total_views', '>', 0)
                    ->orderByDesc('total_views')
                    ->limit($limit)
                    ->pluck('id');
            }

            // Set new trending products
            if ($trendingIds->isNotEmpty()) {
                Product::whereIn('id', $trendingIds)
                    ->update(['is_trending' => true]);

                // Add badge to sellers
                if ($trendBadge) {
                    $sellerIds = Product::with('seller')
                        ->whereIn('id', $trendingIds)
                        ->get()
                        ->pluck('seller.id')
                        ->unique()
                        ->filter();

                    foreach ($sellerIds as $sellerId) {
                        $seller = User::find($sellerId);
                        $seller?->addBadge($trendBadge);
                    }
                }
            }
        });

        Artisan::call('optimize:clear');

        $this->info('Trending products refreshed successfully');

        return self::SUCCESS;
    }
}
