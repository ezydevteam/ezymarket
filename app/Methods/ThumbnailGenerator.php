<?php

namespace App\Methods;

use Intervention\Image\ImageManager;

/**
 * Simple Thumbnail Generator
 * Generate thumbnails on upload
 */
class ThumbnailGenerator
{
    protected array $sizes = [
        'small' => [96, 96],
        //'medium' => [144, 144],
        //'large' => [300, 300],
    ];

    /**
     * Generate thumbnails for an image
     */
    public function generate(string $imagePath): void
    {
        // Temporarily increase memory limit to handle large images
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        try {
            $manager = ImageManager::gd();

            // Read original image from storage
            $fileContents = readFromStorage($imagePath);
            $originalImage = $manager->read($fileContents);
            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

            foreach ($this->sizes as $size => $dimensions) {
                // Clone the original image for each size
                $image = clone $originalImage;
                $image->cover($dimensions[0], $dimensions[1]);

                $thumbnailPath = $this->getThumbnailPath($imagePath, $size);

                // Encode based on file extension
                if ($extension === 'png') {
                    $encoded = $image->toPng();
                } elseif ($extension === 'gif') {
                    $encoded = $image->toGif();
                } elseif ($extension === 'webp') {
                    $encoded = $image->toWebp();
                } else {
                    $encoded = $image->toJpeg();
                }

                // Save thumbnail to storage
                writeToStorage($thumbnailPath, $encoded);
            }
        } catch (\Throwable $e) {
            // Silently fail - product upload continues even if thumbnails fail
        } finally {
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }

    /**
     * Delete thumbnails
     */
    public function delete(string $imagePath): void
    {
        try {
            foreach (array_keys($this->sizes) as $size) {
                $thumbnailPath = $this->getThumbnailPath($imagePath, $size);
                deleteFromStorage($thumbnailPath);
            }
        } catch (\Exception $e) {
            // Silently fail - deletion continues even if thumbnails don't exist
        }
    }

    /**
     * Get thumbnail URL
     */
    public function getUrl(?string $imagePath, string $size = 'medium'): ?string
    {
        if (!$imagePath) {
            return null;
        }

        return storageUrl($this->getThumbnailPath($imagePath, $size));
    }

    /**
     * Generate thumbnail path
     */
    protected function getThumbnailPath(string $imagePath, string $size): string
    {
        $info = pathinfo($imagePath);
        return $info['dirname'] . '/' . $info['filename'] . '_' . $size . '_thumb.' . $info['extension'];
    }
}
