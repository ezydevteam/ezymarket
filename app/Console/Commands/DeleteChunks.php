<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteChunks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:delete-chunks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete chunk parts older than 5 hours from storage';

    private const HOURS_THRESHOLD = 5;

    public function handle(): int
    {
        $chunkDirectory = storage_path('app/chunks');

        if (!File::exists($chunkDirectory)) {
            $this->info('Chunks directory does not exist');
            return self::SUCCESS;
        }

        $files = File::files($chunkDirectory);

        if (empty($files)) {
            $this->info('No chunks to delete');
            return self::SUCCESS;
        }

        $threshold = now()->subHours(self::HOURS_THRESHOLD)->timestamp;
        $deletedCount = 0;

        foreach ($files as $file) {
            if (File::lastModified($file) <= $threshold) {
                File::delete($file);
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            $this->info('No old chunks found to delete');
        } else {
            $this->info("Successfully deleted {$deletedCount} chunk file(s)");
        }

        return self::SUCCESS;
    }
}
