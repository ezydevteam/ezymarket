<?php

namespace App\Console\Commands\Premiums;

use App\Models\Premium\Premium;
use Illuminate\Console\Command;

/**
 * ResetPremiumDownloads
 *
 * Console command to reset total downloads counter for all premiums.
 * Runs daily via scheduler to refresh download limits.
 */
class ResetPremiumDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'premiums:reset-downloads';

    protected $description = 'Reset total downloads counter for all premium memberships';

    public function handle(): int
    {
        $count = Premium::query()->update([
            'total_downloads' => 0,
            'last_reset_at' => now(),
        ]);

        if ($count === 0) {
            $this->info('No premium memberships to reset');
            return self::SUCCESS;
        }

        $this->info("Successfully reset total downloads for {$count} premium membership(s)");

        return self::SUCCESS;
    }
}
