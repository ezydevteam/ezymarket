<?php

namespace App\Console\Commands\Discounts;

use App\Models\Product\{Product, ProductDiscount};
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * DeactivateExpiredDiscounts
 *
 * Console command to deactivate and remove expired product discounts.
 * Runs every minute via scheduler to check for discounts that have ended.
 */
class DeactivateExpiredDiscounts extends Command
{
    protected $signature = 'discounts:deactivate-expired';

    protected $description = 'Deactivate and remove expired product discounts';

    public function handle(): int
    {
        $discounts = ProductDiscount::with('product')
            ->ended()
            ->active()
            ->get();

        if ($discounts->isEmpty()) {
            $this->info('No expired discounts to end');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($discounts) {
            // Bulk update products
            $productUpdates = $discounts->mapWithKeys(fn($discount) => [
                $discount->product_id => [
                    'is_on_discount' => false,
                    'last_discount_at' => Carbon::parse($discount->ending_at),
                    'updated_at' => now(),
                ]
            ]);

            foreach ($productUpdates as $productId => $data) {
                Product::where('id', $productId)->update($data);
            }

            // Bulk delete discounts
            ProductDiscount::whereIn('id', $discounts->pluck('id'))->delete();
        });

        $count = $discounts->count();
        $this->info("Successfully ended {$count} expired discount(s)");

        return self::SUCCESS;
    }
}
