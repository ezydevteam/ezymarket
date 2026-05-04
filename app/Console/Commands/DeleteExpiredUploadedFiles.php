<?php

namespace App\Console\Commands;

use App\Models\UploadedFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteExpiredUploadedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired uploaded files from storage and database';

    public function handle(): int
    {
        $uploadedFiles = UploadedFile::expired()->get();

        if ($uploadedFiles->isEmpty()) {
            $this->info('No expired uploaded files to delete');
            return self::SUCCESS;
        }

        $count = DB::transaction(function () use ($uploadedFiles) {
            $deleted = 0;

            foreach ($uploadedFiles as $uploadedFile) {
                $uploadedFile->deleteFile();
                $uploadedFile->delete();
                $deleted++;
            }

            return $deleted;
        });

        $this->info("Successfully deleted {$count} expired uploaded file(s)");

        return self::SUCCESS;
    }
}
