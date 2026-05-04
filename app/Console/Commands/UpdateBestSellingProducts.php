<?php

namespace App\Console\Commands;

use App\Models\Product\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Artisan};

/**
 * UpdateBestSellingProducts
 *
 * Console command to update best-selling product rankings based on sales data.
 * Runs daily to refresh the top-selling products list.
 */
class UpdateBestSellingProducts extends Command
{
    protected $signature = 'products:update-best-selling';

    protected $description = 'Update best-selling product rankings based on sales data';

    public function handle(): int
    {
        $limit = (int) data_get(settings('product'), 'best_selling_number', 10);

        DB::transaction(function () use ($limit) {
            // Reset all current best selling products
            Product::approved()
                ->bestSelling()
                ->update(['is_best_selling' => false]);

            // Get IDs of new best selling products
            $bestSellingIds = Product::approved()
                ->where('total_sales', '>', 0)
                ->orderByDesc('total_sales')
                ->limit($limit)
                ->pluck('id');

            // Set new best selling products
            if ($bestSellingIds->isNotEmpty()) {
                Product::whereIn('id', $bestSellingIds)
                    ->update(['is_best_selling' => true]);
            }
        });

        Artisan::call('optimize:clear');

        $this->info('Best selling products refreshed successfully');

        return self::SUCCESS;
    }
}
