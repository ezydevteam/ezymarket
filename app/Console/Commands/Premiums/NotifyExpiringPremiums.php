<?php

namespace App\Console\Commands\Premiums;

use App\Models\Premium\Premium;
use App\Facades\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * NotifyExpiringPremiums
 *
 * Console command to notify users about premium memberships that are about to expire.
 * Runs daily via scheduler to send reminders before expiration.
 */
class NotifyExpiringPremiums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'premiums:notify-expiring';

    protected $description = 'Notify users about expiring premium memberships';

    public function handle(): int
    {
        $premiums = Premium::with('user')
            ->aboutToExpire()
            ->where(
                fn($query) => $query
                    ->whereNull('last_notification_at')
                    ->orWhere('last_notification_at', '<', now()->subDays(Premium::RENEWING_DAYS))
            )
            ->get();

        if ($premiums->isEmpty()) {
            $this->info('No premium memberships require expiration notification');
            return self::SUCCESS;
        }

        $count = DB::transaction(function () use ($premiums) {
            $processed = 0;
            $now = now();

            foreach ($premiums as $premium) {
                Notification::sendPremiumNotification(
                    $premium,
                    'expiring_soon'
                );

                $premium->update(['last_notification_at' => $now]);
                $processed++;
            }

            return $processed;
        });

        $this->info("Successfully sent {$count} expiration notification(s)");

        return self::SUCCESS;
    }
}
