<?php

namespace App\Http\Controllers\Storage;

use Illuminate\Support\Facades\Storage;

/**
 * DigitalOcean Spaces Storage Controller
 *
 * Handles file upload, download, and deletion operations for DigitalOcean Spaces.
 * Inherits common functionality from StorageController.
 *
 * @package App\Http\Controllers\Storage
 */
class DigitaloceanController extends StorageController
{
    /**
     * Initialize DigitalOcean Spaces storage disk.
     */
    public function __construct()
    {
        $this->disk = Storage::disk('digitalocean');
    }

    /**
     * Get the environment variable prefix for DigitalOcean Spaces.
     *
     * @return string
     */
    protected static function getEnvPrefix(): string
    {
        return 'DIGITALOCEAN';
    }
}

















