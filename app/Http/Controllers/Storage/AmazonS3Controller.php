<?php

namespace App\Http\Controllers\Storage;

use Illuminate\Support\Facades\Storage;

/**
 * Amazon S3 Storage Controller
 *
 * Handles file upload, download, and deletion operations for Amazon S3.
 * Inherits common functionality from StorageController.
 *
 * @package App\Http\Controllers\Storage
 */
class AmazonS3Controller extends StorageController
{
    /**
     * Initialize S3 storage disk.
     */
    public function __construct()
    {
        $this->disk = Storage::disk('amazon_s3');
    }

    /**
     * Get the environment variable prefix for Amazon S3.
     *
     * @return string
     */
    protected static function getEnvPrefix(): string
    {
        return 'AMAZON_S3';
    }
}

















