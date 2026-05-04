<?php

namespace App\Http\Controllers\Storage;

use Illuminate\Support\Facades\Storage;

/**
 * Cloudflare R2 Storage Controller
 *
 * Handles file upload, download, and deletion operations for Cloudflare R2.
 * Inherits common functionality from StorageController.
 *
 * @package App\Http\Controllers\Storage
 */
class CloudflareR2Controller extends StorageController
{
    /**
     * Initialize Cloudflare R2 storage disk.
     */
    public function __construct()
    {
        $this->disk = Storage::disk('cloudflare_r2');
    }

    /**
     * Get the environment variable prefix for Cloudflare R2.
     *
     * @return string
     */
    protected static function getEnvPrefix(): string
    {
        return 'CLOUDFLARE_R2';
    }
}

















