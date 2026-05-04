<?php

namespace App\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;
use stdClass;

/**
 * Handles File Storage Trait
 *
 * Provides utility methods for file handling, mime type detection,
 * and response formatting for file operations.
 *
 * Methods:
 * - generateUniqueFileName() - Create unique filenames with timestamp
 * - pathToUploadedFile() - Convert file path to UploadedFile instance
 * - fileExtension() - Get file extension from mime type
 * - fileMimeType() - Get mime type from file extension
 * - isPublicFile() - Check if mime type is publicly accessible
 * - success() - Format successful response
 * - error() - Format error response
 *
 * @package App\Traits
 */
trait HandlesFileStorage
{
    /**
     * Generate a unique filename with optional extension.
     *
     * Creates a filename using random string + timestamp for uniqueness.
     *
     * @param UploadedFile $file The uploaded file
     * @param bool $withExtension Whether to include file extension
     * @return string The generated unique filename
     */
    public function generateUniqueFileName(UploadedFile $file, bool $withExtension = true): string
    {
        $fileExtension = $file->getClientOriginalExtension();
        $filename = Str::random(15) . '_' . time();

        if ($withExtension) {
            $filename = $filename . '.' . strtolower($fileExtension);
        }

        return $filename;
    }

    /**
     * Convert a file path to an UploadedFile instance.
     *
     * Useful for testing or processing files from storage as uploaded files.
     *
     * @param string $path The file path
     * @param bool $test Whether this is for testing (affects validation)
     * @return UploadedFile The created UploadedFile instance
     */
    public function pathToUploadedFile(string $path, bool $test = true): UploadedFile
    {
        $filesystem = new Filesystem();

        $name = $filesystem->name($path);
        $extension = $filesystem->extension($path);
        $originalName = $name . '.' . $extension;
        $mimeType = $filesystem->mimeType($path);
        $error = null;

        return new UploadedFile($path, $originalName, $mimeType, $error, $test);
    }

    /**
     * Get file extension from mime type.
     *
     * @param string $mimeType The mime type (e.g., 'image/jpeg')
     * @return string|null The file extension (e.g., 'jpg') or null if not found
     */
    public function fileExtension(string $mimeType): ?string
    {
        $extensionMap = array_flip(GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS);
        return $extensionMap[$mimeType] ?? null;
    }

    /**
     * Get mime type from file extension.
     *
     * @param string $extension The file extension (e.g., 'jpg')
     * @return string|null The mime type (e.g., 'image/jpeg') or null if not found
     */
    public function fileMimeType(string $extension): ?string
    {
        return GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS[$extension] ?? null;
    }

    /**
     * Check if a mime type is for a publicly accessible file.
     *
     * Public files are those that can be directly displayed in browsers
     * (images, videos, audio) without download prompts.
     *
     * Storage behavior:
     * - Local: Public files → storage/app/public/, Private files → storage/app/private/
     * - Cloud: Public files → bucket root, Private files → private/ folder
     *
     * URL access:
     * - Public files: Direct URLs via storageUrl() (e.g., /storage/image.jpg or https://cdn.com/image.jpg)
     * - Private files: Authenticated download routes only (storageUrl() returns null)
     *
     * @param string $mimeType The mime type to check
     * @return bool True if the file is publicly accessible (image/video/audio)
     */
    public function isPublicFile(string $mimeType): bool
    {
        $publicMimeTypes = [
            // Images - publicly viewable
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'image/bmp',

            // Videos - publicly streamable
            'video/mp4',
            'video/mpeg',
            'video/webm',
            'video/ogg',
            'video/quicktime',  // .mov
            'video/x-msvideo',  // .avi
            'video/x-flv',      // .flv
            'video/3gpp',       // .3gp
            'video/x-matroska', // .mkv

            // Audio - publicly playable
            'audio/mpeg',       // .mp3
            'audio/mp3',
            'audio/wav',
            'audio/wave',
            'audio/x-wav',
            'audio/ogg',
            'audio/aac',
            'audio/mp4',        // .m4a
            'audio/x-m4a',
            'audio/webm',
            'audio/flac',
            'audio/x-flac',
        ];

        return in_array($mimeType, $publicMimeTypes, true);
    }

    /**
     * Create a successful response object.
     *
     * @param array<string, mixed> $data Additional data to include in response
     * @return stdClass Response object with 'type' => 'success' and merged data
     */
    public function success(array $data): stdClass
    {
        $response = ['type' => 'success'] + $data;
        return $this->response($response);
    }

    /**
     * Create an error response object.
     *
     * @param string $message The error message
     * @return stdClass Response object with 'type' => 'error' and message
     */
    public function error(string $message): stdClass
    {
        $response = [
            'type' => 'error',
            'message' => $message,
        ];

        return $this->response($response);
    }

    /**
     * Convert array to object response.
     *
     * Uses json encoding/decoding to create a stdClass object.
     * This ensures consistent response structure.
     *
     * @param array<string, mixed> $data The response data
     * @return stdClass The formatted response object
     */
    protected function response(array $data): stdClass
    {
        return json_decode(json_encode($data));
    }
}

















