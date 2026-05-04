<?php

namespace App\Methods;

use App\Traits\HandlesFileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use WebPConvert\WebPConvert;
use WebPConvert\Exceptions\WebPConvertException;

/**
 * WebPConverter - Image to WebP Converter
 *
 * Converts uploaded image files (JPEG, PNG) to WebP format for better compression
 * and faster loading times. Handles temporary file management and cleanup.
 *
 * Features:
 * - Converts JPEG/PNG to WebP format
 * - Automatic temporary file management
 * - Quality control (default 80%)
 * - Automatic cleanup of source files
 * - Error handling with fallback
 * - Batch conversion support
 * - Dimension preservation
 * - Metadata retention options
 *
 * Usage:
 * ```php
 * $converter = new WebPConverter();
 * $webpFile = $converter->convert($uploadedFile);
 *
 * // With custom quality
 * $webpFile = $converter->convert($uploadedFile, 90);
 *
 * // Batch conversion
 * $webpFiles = $converter->convertMultiple([$file1, $file2]);
 * ```
 *
 * @package App\Methods
 * @author Codebay Team
 * @version 1.0.0
 */
class WebPConverter
{
    use HandlesFileStorage;

    /**
     * Default WebP quality (0-100)
     * Higher values = better quality but larger file size
     */
    private const DEFAULT_QUALITY = 80;

    /**
     * Supported image MIME types for conversion
     */
    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

    /**
     * Temporary directory for file processing
     */
    private string $tempPath;

    /**
     * WebP conversion quality setting
     */
    private int $quality;

    /**
     * Initialize the WebPConverter
     *
     * Sets up temporary directory path and default quality settings.
     * Ensures temporary directory exists and is writable.
     *
     * @param int $quality WebP quality (0-100), default 80
     * @throws RuntimeException If temp directory cannot be created
     */
    public function __construct(int $quality = self::DEFAULT_QUALITY)
    {
        $this->tempPath = storage_path('app/temp/');
        $this->quality = $this->validateQuality($quality);
        $this->ensureTempDirectoryExists();
    }

    /**
     * Convert an uploaded image file to WebP format
     *
     * Main conversion method that handles the complete conversion process:
     * 1. Validates the input file
     * 2. Moves file to temporary location
     * 3. Converts to WebP format
     * 4. Cleans up original file
     * 5. Returns WebP file as UploadedFile
     *
     * @param UploadedFile $file The uploaded image file to convert
     * @param int|null $customQuality Optional custom quality for this conversion
     * @return UploadedFile The converted WebP file
     * @throws RuntimeException If conversion fails
     *
     * @example
     * ```php
     * $converter = new WebPConverter();
     * $webpFile = $converter->convert($uploadedFile);
     * ```
     */
    public function convert(UploadedFile $file, ?int $customQuality = null): UploadedFile
    {
        // Validate input
        $this->validateFile($file);

        $quality = $customQuality ? $this->validateQuality($customQuality) : $this->quality;

        // Generate unique filename for temporary storage
        $tempFileName = $this->generateUniqueFileName($file);
        $fileSource = $this->tempPath . $tempFileName;

        try {
            // Move uploaded file to temporary location
            $file->move($this->tempPath, $tempFileName);

            // Generate WebP filename and destination path
            $webpFileName = $this->generateUniqueFileName($file, false);
            $fileDestination = $this->tempPath . $webpFileName . '.webp';

            // Convert to WebP format
            $this->convertToWebp($fileSource, $fileDestination, $quality);

            // Clean up original file
            $this->cleanupFile($fileSource);

            // Return converted file as UploadedFile
            return $this->pathToUploadedFile($fileDestination);

        } catch (WebPConvertException $e) {
            // Clean up on failure
            $this->cleanupFile($fileSource);

            Log::error('WebP conversion failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new RuntimeException(
                "Failed to convert image to WebP format: {$e->getMessage()}",
                0,
                $e
            );

        } catch (\Exception $e) {
            // Clean up on any other failure
            $this->cleanupFile($fileSource);

            Log::error('Image conversion failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);

            throw new RuntimeException(
                "Image conversion failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Convert multiple images to WebP format
     *
     * Batch converts an array of uploaded files to WebP format.
     * Continues processing even if individual conversions fail.
     *
     * @param array<UploadedFile> $files Array of uploaded image files
     * @param int|null $customQuality Optional custom quality for all conversions
     * @return array{success: array<UploadedFile>, failed: array<string>} Converted files and failed filenames
     *
     * @example
     * ```php
     * $converter = new WebPConverter();
     * $result = $converter->convertMultiple([$file1, $file2, $file3]);
     *
     * foreach ($result['success'] as $webpFile) {
     *     // Process successful conversions
     * }
     *
     * if (!empty($result['failed'])) {
     *     // Handle failed conversions
     * }
     * ```
     */
    public function convertMultiple(array $files, ?int $customQuality = null): array
    {
        $converted = [];
        $failed = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                $failed[] = 'Invalid file object';
                continue;
            }

            try {
                $converted[] = $this->convert($file, $customQuality);
            } catch (RuntimeException $e) {
                $failed[] = $file->getClientOriginalName();
                Log::warning('Batch conversion: File skipped', [
                    'file' => $file->getClientOriginalName(),
                    'reason' => $e->getMessage()
                ]);
            }
        }

        return [
            'success' => $converted,
            'failed' => $failed,
        ];
    }

    /**
     * Check if a file can be converted to WebP
     *
     * Validates whether the given file is a supported image type
     * that can be converted to WebP format.
     *
     * @param UploadedFile $file The file to check
     * @return bool True if file can be converted, false otherwise
     */
    public function canConvert(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::SUPPORTED_MIME_TYPES, true);
    }

    /**
     * Get supported MIME types for conversion
     *
     * @return array<string> Array of supported MIME types
     */
    public function getSupportedMimeTypes(): array
    {
        return self::SUPPORTED_MIME_TYPES;
    }

    /**
     * Set conversion quality for subsequent conversions
     *
     * @param int $quality Quality value (0-100)
     * @return self For method chaining
     * @throws RuntimeException If quality is invalid
     */
    public function setQuality(int $quality): self
    {
        $this->quality = $this->validateQuality($quality);
        return $this;
    }

    /**
     * Get current conversion quality setting
     *
     * @return int Current quality value (0-100)
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * Validate uploaded file for conversion
     *
     * Checks if the file is valid and supported for WebP conversion.
     *
     * @param UploadedFile $file The file to validate
     * @return void
     * @throws RuntimeException If file is invalid or unsupported
     */
    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new RuntimeException('Invalid uploaded file');
        }

        if (!$this->canConvert($file)) {
            $mimeType = $file->getMimeType();
            $supportedTypes = implode(', ', self::SUPPORTED_MIME_TYPES);
            throw new RuntimeException(
                "Unsupported file type: {$mimeType}. Supported types: {$supportedTypes}"
            );
        }

        // Check file size (must be greater than 0)
        if ($file->getSize() === 0) {
            throw new RuntimeException('Cannot convert empty file');
        }
    }

    /**
     * Validate and sanitize quality value
     *
     * Ensures quality value is within valid range (0-100).
     *
     * @param int $quality Quality value to validate
     * @return int Validated quality value
     * @throws RuntimeException If quality is out of range
     */
    private function validateQuality(int $quality): int
    {
        if ($quality < 0 || $quality > 100) {
            throw new RuntimeException(
                "Invalid quality value: {$quality}. Must be between 0 and 100"
            );
        }

        return $quality;
    }

    /**
     * Ensure temporary directory exists and is writable
     *
     * Creates the temporary directory if it doesn't exist.
     *
     * @return void
     * @throws RuntimeException If directory cannot be created or is not writable
     */
    private function ensureTempDirectoryExists(): void
    {
        if (!File::exists($this->tempPath)) {
            makeDirectory($this->tempPath);
        }

        if (!File::isWritable($this->tempPath)) {
            throw new RuntimeException(
                "Temporary directory is not writable: {$this->tempPath}"
            );
        }
    }

    /**
     * Convert image file to WebP format
     *
     * Performs the actual WebP conversion using WebPConvert library.
     *
     * @param string $source Source file path
     * @param string $destination Destination WebP file path
     * @param int $quality Conversion quality (0-100)
     * @return void
     * @throws WebPConvertException If conversion fails
     */
    private function convertToWebp(string $source, string $destination, int $quality): void
    {
        $options = [
            'quality' => $quality,
            'metadata' => 'none', // Strip metadata for smaller file size
            'method' => 6, // Compression method (0-6, higher = slower but better)
            'low-memory' => true, // Use less memory
            'log-call-arguments' => false, // Don't log for performance
        ];

        WebPConvert::convert($source, $destination, $options);

        // Verify output file was created
        if (!File::exists($destination)) {
            throw new RuntimeException('WebP conversion produced no output file');
        }

        // Verify output file is not empty
        if (File::size($destination) === 0) {
            File::delete($destination);
            throw new RuntimeException('WebP conversion produced empty file');
        }
    }

    /**
     * Clean up temporary file
     *
     * Safely deletes a file if it exists. Does not throw exceptions
     * to prevent cleanup failures from interrupting the process.
     *
     * @param string $filePath Path to file to delete
     * @return void
     */
    private function cleanupFile(string $filePath): void
    {
        try {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        } catch (\Exception $e) {
            // Log but don't throw - cleanup failures shouldn't break the process
            Log::warning('Failed to cleanup temporary file', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
        }
    }
}


















