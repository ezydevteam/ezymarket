<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Storage\LocalController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class DebugUpload extends Command
{
    protected $signature = 'debug:upload';
    protected $description = 'Debug file upload logic end-to-end';

    public function handle()
    {
        $dummyContent = 'test image content';
        $tempPath = storage_path('app/temp/test_image5.webp');

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        file_put_contents($tempPath, $dummyContent);

        // This is exactly what pathToUploadedFile does inside WebPConverter
        $file = new UploadedFile(
            $tempPath,
            'test_image5.webp',
            'image/webp',
            null,
            true
        );

        $path = "files/products/2volejrejnmg/";
        $mimeType = 'image/webp';

        $controller = new LocalController();
        $response = $controller->upload($file, $path, $mimeType);

        $this->info("Filename: " . $response->filename);
        $this->info("Path: " . $response->path);
    }
}
