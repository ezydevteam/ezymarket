<?php

namespace App\Console\Commands;

use App\Models\Product\ProductReportSetting;
use Illuminate\Console\Command;

/**
 * ProcessProductReportExpirations
 *
 * Console command to process expired product restrictions and reporter suspensions.
 * Should be run daily via scheduler.
 */
class ProcessProductReportExpirations extends Command
{
    protected $signature = 'reports:process-expirations
                            {--products : Only process expired product restrictions}
                            {--reporters : Only process expired reporter suspensions}';

    protected $description = 'Process expired product restrictions and reporter suspensions';

    public function handle(): int
    {
        $processProducts = $this->option('products');
        $processReporters = $this->option('reporters');

        // If no specific option, process both
        $processAll = !$processProducts && !$processReporters;

        $this->info('Processing product report expirations...');
        $this->newLine();

        $totalProcessed = 0;

        // Process expired product restrictions
        if ($processAll || $processProducts) {
            $this->info('🔓 Processing expired product restrictions...');

            $unrestrictedCount = ProductReportSetting::unrestrictExpiredProducts();

            if ($unrestrictedCount > 0) {
                $this->info("   ✓ Unrestricted {$unrestrictedCount} product(s)");
            } else {
                $this->comment('   No expired product restrictions found');
            }

            $totalProcessed += $unrestrictedCount;
            $this->newLine();
        }

        // Process expired reporter suspensions
        if ($processAll || $processReporters) {
            $this->info('👤 Processing expired reporter suspensions...');

            $reactivatedCount = ProductReportSetting::reactivateExpiredReporters();

            if ($reactivatedCount > 0) {
                $this->info("   ✓ Reactivated {$reactivatedCount} reporter(s)");
            } else {
                $this->comment('   No expired reporter suspensions found');
            }

            $totalProcessed += $reactivatedCount;
            $this->newLine();
        }

        // Summary
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Total processed: {$totalProcessed}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return self::SUCCESS;
    }
}
