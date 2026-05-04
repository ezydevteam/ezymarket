<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Traits\HandlesFileStorage;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\{UploadedFile, RedirectResponse};
use stdClass;

/**
 * Abstract Storage Controller
 *
 * Base class for all cloud storage drivers (S3, DigitalOcean, Cloudflare R2, etc.).
 * Provides common upload, download, and delete functionality.
 *
 * Child classes only need to:
 * 1. Set the disk name in constructor
 * 2. Provide the environment variable prefix via getEnvPrefix()
 *
 * @package App\Http\Controllers\Storage
 */
abstract class StorageController extends Controller
{
    use HandlesFileStorage;

    /**
     * Laravel Storage disk instance.
     *
     * @var Filesystem
     */
    public Filesystem $disk;

    /**
     * Get the environment variable prefix for this storage driver.
     * e.g., 'AMAZON_S3', 'DIGITALOCEAN', 'CLOUDFLARE_R2'
     *
     * @return string
     */
    abstract protected static function getEnvPrefix(): string;

    /**
     * Set storage credentials in environment file.
     *
     * @param object $credentials Object containing storage credentials
     * @return void
     */
    public static function setCredentials(object $credentials): void
    {
        $prefix = static::getEnvPrefix();

        setEnv($prefix . '_ACCESS_KEY_ID', $credentials->access_key_id);
        setEnv($prefix . '_SECRET_ACCESS_KEY', $credentials->secret_access_key);
        setEnv($prefix . '_DEFAULT_REGION', $credentials->default_region);
        setEnv($prefix . '_BUCKET', $credentials->bucket);
        setEnv($prefix . '_URL', $credentials->url);
        setEnv($prefix . '_ENDPOINT', $credentials->endpoint);
    }

    /**
     * Upload a file to cloud storage.
     *
     * Public files (images, videos, audio) are stored at root level.
     * Private files (documents, archives) are stored in 'private/' folder.
     *
     * @param UploadedFile $file The file to upload
     * @param string $path The directory path in storage
     * @param string $mimeType The file's MIME type
     * @return stdClass Response object with success/error status
     */
    public function upload(UploadedFile $file, string $path, string $mimeType): stdClass
    {
        try {
            $filename = $this->generateUniqueFileName($file);

            // Add 'private/' prefix for non-public files (documents, archives, etc.)
            if (!$this->isPublicFile($mimeType)) {
                $path = 'private/' . $path;
            }

            $fullPath = $path . $filename;

            $upload = $this->disk->put($fullPath, fopen($file, 'r+'));

            if (!$upload) {
                return $this->error(translate('Failed to upload due to an error in the storage driver'));
            }

            return $this->success([
                'filename' => $filename,
                'path' => $fullPath,
            ]);
        } catch (Exception $e) {
            return $this->error(translate('Failed to upload due to an error in the storage driver'));
        }
    }

    /**
     * Generate a temporary download link for a file.
     *
     * Creates a signed URL valid for 1 hour with forced download disposition.
     * Supports both public and private files (checks 'private/' prefix).
     *
     * @param string $path The file path in storage
     * @param string $filename The filename for download
     * @return RedirectResponse|stdClass Redirect to download URL or error response
     */
    public function download(string $path, string $filename): RedirectResponse|stdClass
    {
        try {
            // Check if file exists (may be in root or private/ folder)
            if (!$this->disk->exists($path)) {
                return $this->error(translate('The requested file does not exist'));
            }

            /** @var \Illuminate\Filesystem\AwsS3V3Adapter $adapter
             * @phpstan-ignore-next-line
            */
            $downloadLink = $this->disk->temporaryUrl(
                $path,
                Carbon::now()->addHour(),
                ['ResponseContentDisposition' => 'attachment; filename="' . $filename . '"']
            );

            return redirect($downloadLink);
        } catch (Exception $e) {
            return $this->error(translate('Failed to download due to an error in the storage driver'));
        }
    }

    /**
     * Delete a file from cloud storage.
     *
     * @param string $path The file path to delete
     * @return bool Always returns true (no exception thrown if file doesn't exist)
     */
    public function delete(string $path): bool
    {
        if ($this->disk->exists($path)) {
            $this->disk->delete($path);
        }

        return true;
    }
}
