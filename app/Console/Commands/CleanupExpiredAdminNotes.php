<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupExpiredAdminNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old notes from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notes cleanup process...');

        // Delete notes older than 1 year
        $deletedCount = DB::table('admin_notes')
            ->where('created_at', '<', now()->subDays(360))
            ->delete();

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} old note(s).");
        } else {
            $this->info('No notes to cleanup.');
        }

        $this->info('Notes cleanup completed successfully!');

        return Command::SUCCESS;
    }
}
