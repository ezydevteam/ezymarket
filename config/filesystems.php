<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application.
    |
    | Controlled by: FILESYSTEM_DRIVER in .env file
    | Default: 'local'
    |
    | Available options: local, amazon_s3, cloudflare_r2, digitalocean
    |
     */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
     */

    'disks' => [

        /*
         * Direct Disk
         * Points directly to the public directory root.
         * Use for files that need to be directly accessible via web URLs.
         */
        'direct' => [
            'driver' => 'local',
            'root' => public_path('/'),
        ],

        /*
         * Local Disk
         * Private storage in storage/app directory.
         * Files are not publicly accessible by default.
         */
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        /*
         * Public Disk
         * Storage in storage/app/public directory.
         * Files are accessible via symbolic link at public/storage.
         * Run: php artisan storage:link to create the symbolic link.
         */
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        /*
         * Amazon S3 Cloud Storage
         * Configure AWS S3 bucket for cloud file storage.
         * Requires AWS credentials and bucket configuration in .env
         */
        'amazon_s3' => [
            'driver' => 's3',
            'key' => env('AMAZON_S3_ACCESS_KEY_ID'),
            'secret' => env('AMAZON_S3_SECRET_ACCESS_KEY'),
            'region' => env('AMAZON_S3_DEFAULT_REGION'),
            'bucket' => env('AMAZON_S3_BUCKET'),
            'url' => env('AMAZON_S3_URL'),
            'endpoint' => env('AMAZON_S3_ENDPOINT'),
            'use_path_style_endpoint' => false,
        ],

        /*
         * DigitalOcean Spaces
         * S3-compatible object storage from DigitalOcean.
         * Requires DigitalOcean Spaces credentials in .env
         */
        'digitalocean' => [
            'driver' => 's3',
            'key' => env('DIGITALOCEAN_ACCESS_KEY_ID'),
            'secret' => env('DIGITALOCEAN_SECRET_ACCESS_KEY'),
            'region' => env('DIGITALOCEAN_DEFAULT_REGION'),
            'bucket' => env('DIGITALOCEAN_BUCKET'),
            'url' => env('DIGITALOCEAN_URL'),
            'endpoint' => env('DIGITALOCEAN_ENDPOINT'),
            'use_path_style_endpoint' => false,
        ],

        /*
         * Cloudflare R2 Storage
         * S3-compatible object storage from Cloudflare.
         * Requires Cloudflare R2 credentials in .env
         * Note: Region should typically be 'auto' for R2
         */
        'cloudflare_r2' => [
            'driver' => 's3',
            'key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
            'secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
            'region' => env('CLOUDFLARE_R2_DEFAULT_REGION'),
            'bucket' => env('CLOUDFLARE_R2_BUCKET'),
            'url' => env('CLOUDFLARE_R2_URL'),
            'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
            'use_path_style_endpoint' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    | This allows publicly accessible files in storage/app/public to be
    | accessed via public/storage URL.
    |
    | Command: php artisan storage:link
    |
     */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];



















