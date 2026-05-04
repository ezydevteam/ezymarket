<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ClearUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clear-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove stale unverified user accounts from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Check if email verification is enabled globally
        if (!@settings('actions')->email_verification) {
            $this->info('Email verification is currently disabled in settings. Skipping cleanup.');
            return Command::SUCCESS;
        }

        $this->info('Starting unverified accounts cleanup process...');

        // 2. Identify stale unverified users
        // - email_verified_at is null
        // - email_otp is not null (meaning they were triggered to verify)
        // - created_at is older than 48 hours
        $staleUsers = User::whereNull('email_verified_at')
            ->whereNotNull('email_otp')
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        $count = $staleUsers->count();

        if ($count > 0) {
            foreach ($staleUsers as $user) {
                $this->comment("Deleting unverified user: {$user->email} (ID: {$user->id})");
                // Use forceDelete to ensure UserObserver::deleting is triggered
                // and deleteResources() is called.
                $user->forceDelete();
            }

            $this->info("Successfully deleted {$count} stale unverified account(s).");
        } else {
            $this->info('No stale unverified accounts found.');
        }

        $this->info('Unverified accounts cleanup completed successfully!');

        return Command::SUCCESS;
    }
}
