<?php

namespace App\Console\Commands;

use App\Models\{User, SellerLevel};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * AssignSellerLevels
 *
 * Console command to assign seller levels based on total sales earnings.
 * Also awards associated level badges to sellers.
 */
class AssignSellerLevels extends Command
{
    protected $signature = 'sellers:assign-levels';

    protected $description = 'Assign seller levels based on total sales earnings';

    public function handle(): int
    {
        $sellers = User::seller()->get(['id', 'total_sales_amount', 'level_id']);

        if ($sellers->isEmpty()) {
            $this->info('No sellers to update');
            return self::SUCCESS;
        }

        $levels = SellerLevel::with('badge')
            ->orderByDesc('min_earnings')
            ->get();

        if ($levels->isEmpty()) {
            $this->warn('No levels configured in the system');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($sellers->count());
        $bar->start();

        $updatedCount = 0;

        DB::transaction(function () use ($sellers, $levels, $bar, &$updatedCount) {
            foreach ($sellers as $seller) {
                $newLevel = $this->determineLevel($seller->total_sales_amount, $levels);

                if ($newLevel && $seller->level_id !== $newLevel->id) {
                    $seller->update(['level_id' => $newLevel->id]);
                    $updatedCount++;

                    if ($newLevel->badge) {
                        $seller->addBadge($newLevel->badge);
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        if ($updatedCount === 0) {
            $this->info('All seller levels are up to date');
        } else {
            $this->info("✓ Successfully updated {$updatedCount} seller level(s)");
        }

        return self::SUCCESS;
    }

    private function determineLevel(float $totalSalesEarnings, $levels): ?SellerLevel
    {
        return $levels->first(fn($level) => $level->min_earnings <= $totalSalesEarnings);
    }
}
