<?php

namespace App\Console\Commands\Premiums;

use App\Models\Premium\Premium;
use App\Facades\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * NotifyExpiredPremiums
 *
 * Console command to notify users about premium memberships that have expired.
 * Runs daily via scheduler to send expiration notifications.
 */
class NotifyExpiredPremiums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'premiums:notify-expired';

    protected $description = 'Notify users about expired premium memberships';

    public function handle(): int
    {
        $premiums = Premium::with('user')
            ->expired()
            ->where(
                fn($query) => $query
                    ->whereNull('last_notification_at')
                    ->orWhere('last_notification_at', '<', now()->subDays(Premium::EXPIRING_DAYS))
            )
            ->get();

        if ($premiums->isEmpty()) {
            $this->info('No expired premium memberships require notification');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($premiums->count());
        $bar->start();

        $count = DB::transaction(function () use ($premiums, $bar) {
            $processed = 0;
            $now = now();

            foreach ($premiums as $premium) {
                Notification::sendPremiumNotification(
                    $premium,
                    'expired'
                );

                $premium->update(['last_notification_at' => $now]);
                $processed++;
                $bar->advance();
            }

            return $processed;
        });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully sent {$count} expired notification(s)");

        return self::SUCCESS;
    }
}
