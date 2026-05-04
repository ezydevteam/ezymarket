<?php

declare(strict_types=1);

namespace App\Enums\Product;

/**
 * Product Preview Type Enum
 *
 * Defines the types of preview files that can be attached to a product.
 *
 * @package App\Enums\Product
 */
enum ProductPreviewType: string
{
    /**
     * Image preview (jpg, png, gif, etc.)
     */
    case IMAGE = 'image';

    /**
     * Video preview (mp4, webm, etc.)
     */
    case VIDEO = 'video';

    /**
     * Audio preview (mp3, wav, etc.)
     */
    case AUDIO = 'audio';

    /**
     * Get all available preview type options
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::IMAGE->value => translate('Image'),
            self::VIDEO->value => translate('Video'),
            self::AUDIO->value => translate('Audio'),
        ];
    }

    /**
     * Get the translated label for the preview type
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::IMAGE => translate('Image'),
            self::VIDEO => translate('Video'),
            self::AUDIO => translate('Audio'),
        };
    }

    /**
     * Get the icon for the preview type
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::IMAGE => 'fa-image',
            self::VIDEO => 'fa-video',
            self::AUDIO => 'fa-music',
        };
    }

    /**
     * Check if preview type is image
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this === self::IMAGE;
    }

    /**
     * Check if preview type is video
     *
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this === self::VIDEO;
    }

    /**
     * Check if preview type is audio
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return $this === self::AUDIO;
    }
}
