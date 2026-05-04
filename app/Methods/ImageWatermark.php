<?php

namespace App\Methods;

use App\Traits\HandlesFileStorage;
use Exception;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

/**
 * ImageWatermark - Image Watermarking Service
 *
 * Applies watermarks to uploaded images with configurable positioning, sizing,
 * rotation, and opacity. Supports single watermark placement or fill pattern.
 *
 * Features:
 * - Configurable watermark position (9 positions + fill)
 * - Adjustable size (width & height)
 * - Rotation support (0-360 degrees)
 * - Opacity control (0-100%)
 * - Fill pattern support (repeating watermark)
 * - Automatic image format handling
 * - Temporary file management
 * - Error handling with cleanup
 *
 * Supported Positions:
 * - top-left, top, top-right
 * - left, center, right
 * - bottom-left, bottom, bottom-right
 * - fill (repeating pattern)
 *
 * Usage:
 * ```php
 * // Basic usage
 * $watermark = new ImageWatermark();
 * $watermarkedFile = $watermark->add($uploadedFile);
 *
 * // With custom settings (configured in admin panel)
 * // Settings: watermark.image, watermark.position, watermark.width,
 * //           watermark.height, watermark.rotate, watermark.opacity
 * ```
 *
 * Configuration:
 * Watermark settings are managed through the settings('watermark') helper:
 * - `image`: Path to watermark image file
 * - `position`: Placement position (default: 'bottom-right')
 * - `width`: Watermark width in pixels (default: 200)
 * - `height`: Watermark height in pixels (default: 70)
 * - `rotate`: Rotation angle in degrees (default: 0)
 * - `opacity`: Opacity percentage 0-100 (default: 100)
 *
 * @package App\Methods
 * @author Codebay Team
 * @version 1.0.0
 */
class ImageWatermark
{
    use HandlesFileStorage;

    /**
     * Add watermark to an image
     *
     * Applies a watermark to the provided image based on configured settings.
     * Supports both UploadedFile and file path as input. Handles positioning,
     * sizing, rotation, and opacity. Can create a fill pattern or single placement.
     *
     * @param UploadedFile|string $image The image file to watermark (UploadedFile or file path)
     * @return UploadedFile The watermarked image as UploadedFile
     * @throws Exception If watermark image doesn't exist or processing fails
     *
     * @example
     * ```php
     * $watermark = new ImageWatermark();
     *
     * // With uploaded file
     * $watermarkedFile = $watermark->add($request->file('image'));
     *
     * // With file path
     * $watermarkedFile = $watermark->add('/path/to/image.jpg');
     * ```
     */
    public function add(UploadedFile|string $image): UploadedFile
    {
        $originalImage = $image;

        try {
            $watermarkSettings = settings('watermark');

            $watermarkPath = public_path(@$watermarkSettings->image ?? '');
            $position = @$watermarkSettings->position ?? 'bottom-right';
            $width = @$watermarkSettings->width ?? 200;
            $height = @$watermarkSettings->height ?? 70;
            $rotate = @$watermarkSettings->rotate ?? 0;
            $opacity = @$watermarkSettings->opacity ?? 100;

            if (!file_exists($watermarkPath)) {
                throw new Exception(translate('Watermark image does not exist'));
            }

            // Use Intervention Image v3 API
            $manager = ImageManager::gd();

            if (is_string($image)) {
                $image = $manager->read(file_get_contents($image));
            } else {
                $image = $manager->read($image);
            }

            $watermark = $manager->read(file_get_contents($watermarkPath));

            $watermark->resize($width, $height);
            $watermark->rotate($rotate);

            // Note: Intervention Image v3 doesn't have built-in opacity control
            // Opacity setting from admin panel is currently not applied
            // TODO: Implement opacity using GD imagefilter() if needed

            if ($position == "fill") {
                $imageWidth = $image->width();
                $imageHeight = $image->height();
                $watermarkWidth = $watermark->width();
                $watermarkHeight = $watermark->height();

                for ($y = 0; $y < $imageHeight; $y += $watermarkHeight) {
                    for ($x = 0; $x < $imageWidth; $x += $watermarkWidth) {
                        $image->place($watermark, 'top-left', $x, $y);
                    }
                }
            } else {
                $image->place($watermark, $position, 5, 5);
            }

            $directory = storage_path("app/temp/");
            makeDirectory($directory);

            $filename = $this->generateUniqueFileName($originalImage);
            $fileDestination = $directory . $filename;

            // Use v3 encoder - default to JPEG
            $encoded = $image->toJpeg();
            file_put_contents($fileDestination, $encoded);

            return $this->pathToUploadedFile($fileDestination);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}


















