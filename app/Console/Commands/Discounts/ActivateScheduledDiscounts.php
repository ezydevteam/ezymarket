<?php

namespace App\Console\Commands\Discounts;

use App\Enums\BadgeAlias;
use App\Models\Badge;
use App\Models\Product\ProductDiscount;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ActivateScheduledDiscounts
 *
 * Console command to activate product discounts that have reached their start time.
 * Runs every minute via scheduler to check for discounts ready to begin.
 */
class ActivateScheduledDiscounts extends Command
{
    protected $signature = 'discounts:activate-scheduled';

    protected $description = 'Activate product discounts that have reached their start time';

    public function handle(): int
    {
        $discounts = ProductDiscount::with(['product.seller'])
            ->started()
            ->inactive()
            ->get();

        if ($discounts->isEmpty()) {
            $this->info('No discounts to start');
            return self::SUCCESS;
        }

        $discountBadge = Badge::where('alias', BadgeAlias::DISCOUNT_MASTER)->first();
        $count = 0;

        DB::transaction(function () use ($discounts, $discountBadge, &$count) {
            foreach ($discounts as $discount) {
                $discount->product->update([
                    'is_on_discount' => true,
                    'last_discount_at' => Carbon::parse($discount->ending_at),
                ]);

                $discount->update(['is_active' => true]);

                if ($discountBadge) {
                    $discount->product->seller->addBadge($discountBadge);
                }

                $count++;
            }
        });

        $this->info("Successfully started {$count} discount(s)");

        return self::SUCCESS;
    }
}
