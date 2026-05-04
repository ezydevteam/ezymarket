<?php

declare(strict_types=1);

namespace App\Enums\Product;

enum ProductCategoryPreviewType: int
{
    case IMAGE = 1;
    case VIDEO = 2;
    case AUDIO = 3;

    /**
     * Get the label for the preview type
     */
    public function label(): string
    {
        return match($this) {
            self::IMAGE => translate('Image'),
            self::VIDEO => translate('Video'),
            self::AUDIO => translate('Audio'),
        };
    }

    /**
     * Get all options as array [value => label]
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Check if the type is image
     */
    public function isImage(): bool
    {
        return $this === self::IMAGE;
    }

    /**
     * Check if the type is video
     */
    public function isVideo(): bool
    {
        return $this === self::VIDEO;
    }

    /**
     * Check if the type is audio
     */
    public function isAudio(): bool
    {
        return $this === self::AUDIO;
    }
}
