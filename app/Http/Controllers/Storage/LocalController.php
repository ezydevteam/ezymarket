<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Traits\HandlesFileStorage;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Local Storage Controller
 *
 * Handles file upload, download, and deletion operations for local storage.
 * Uses Laravel's storage disks:
 * - Public files (images, videos, audio) → storage/app/public (via 'public' disk)
 * - Private files (documents, archives) → storage/app/private (via 'local' disk)
 *
 * @package App\Http\Controllers\Storage
 */
class LocalController extends Controller
{
    use HandlesFileStorage;

    /**
     * Upload a file to local storage.
     *
     * Public files (images, videos, audio) go to storage/app/public.
     * Private files (documents, archives) go to storage/app/private.
     *
     * @param UploadedFile $file The file to upload
     * @param string $path The directory path
     * @param string $mimeType The file's MIME type
     * @return stdClass Response object with success/error status
     */
    public function upload(UploadedFile $file, string $path, string $mimeType): stdClass
    {
        try {
            $filename = $this->generateUniqueFileName($file);
            $filePath = $path . $filename;

            // Determine disk based on file type
            $disk = $this->isPublicFile($mimeType) ? 'public' : 'local';

            $upload = Storage::disk($disk)->putFileAs(
                rtrim($path, '/'),
                $file,
                $filename
            );

            if (!$upload) {
                return $this->error(translate('Failed to upload due to an error in the storage driver'));
            }

            return $this->success([
                'filename' => $filename,
                'path' => $filePath,
            ]);
        } catch (Exception $e) {
            return $this->error(translate('Failed to upload due to an error in the storage driver'));
        }
    }

    /**
     * Download a file from local storage.
     *
     * Creates a streamed response for efficient large file downloads.
     * Checks both public and local (private) disks.
     *
     * @param string $path The file path in storage
     * @param string $filename The filename for download
     * @return StreamedResponse|stdClass Streamed download or error response
     */
    public function download(string $path, string $filename): StreamedResponse|stdClass
    {
        try {
            // Check public disk first, then local (private) disk
            $disk = Storage::disk('public')->exists($path)
                ? Storage::disk('public')
                : Storage::disk('local');

            if (!$disk->exists($path)) {
                return $this->error(translate('The requested file does not exist'));
            }

            $headers = [
                'Content-Type' => $disk->mimeType($path),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => $disk->size($path),
            ];

            return new StreamedResponse(function () use ($path, $disk) {
                $stream = $disk->readStream($path);
                while (!feof($stream) && connection_status() === 0) {
                    echo fread($stream, 1024 * 8);
                    flush();
                }
                fclose($stream);
            }, 200, $headers);
        } catch (Exception $e) {
            return $this->error(translate('Failed to download due to an error in the storage driver'));
        }
    }

    /**
     * Delete a file from local storage.
     *
     * Checks both public and local (private) disks.
     *
     * @param string $path The file path to delete
     * @return bool Always returns true (no exception thrown if file doesn't exist)
     */
    public function delete(string $path): bool
    {
        $publicDisk = Storage::disk('public');
        $localDisk = Storage::disk('local');

        if ($publicDisk->exists($path)) {
            $publicDisk->delete($path);
        } elseif ($localDisk->exists($path)) {
            $localDisk->delete($path);
        }

        return true;
    }
}
















