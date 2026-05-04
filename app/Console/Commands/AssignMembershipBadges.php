<?php

namespace App\Console\Commands;

use App\Models\{User, Badge};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * AssignMembershipBadges
 *
 * Console command to assign membership anniversary badges to eligible users.
 * Awards badges based on account age (years of membership).
 */
class AssignMembershipBadges extends Command
{
    protected $signature = 'badges:assign-membership';

    protected $description = 'Assign membership anniversary badges to eligible users';

    public function handle(): int
    {
        $membershipBadges = Badge::membershipYearsBadge()
            ->orderBy('membership_years', 'desc')
            ->get();

        if ($membershipBadges->isEmpty()) {
            $this->info('No membership year badges configured');
            return self::SUCCESS;
        }

        $this->info("Processing {$membershipBadges->count()} membership badge(s)...");

        $totalAssigned = 0;

        DB::transaction(function () use ($membershipBadges, &$totalAssigned) {
            foreach ($membershipBadges as $badge) {
                $cutoffDate = now()->subYears($badge->membership_years);

                $eligibleUsers = User::active()
                    ->where('created_at', '<=', $cutoffDate)
                    ->whereDoesntHave('badges', fn($query) =>
                        $query->where('badge_id', $badge->id)
                    )
                    ->get(['id']);

                if ($eligibleUsers->isEmpty()) {
                    continue;
                }

                foreach ($eligibleUsers as $user) {
                    $user->addBadge($badge);
                    $totalAssigned++;
                }

                $this->line("Assigned '{$badge->name}' badge to {$eligibleUsers->count()} user(s)");
            }
        });

        if ($totalAssigned === 0) {
            $this->info('No users eligible for membership badges');
        } else {
            $this->info("✓ Successfully assigned {$totalAssigned} badge(s)");
        }

        return self::SUCCESS;
    }
}
