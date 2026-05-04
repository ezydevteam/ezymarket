<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name,
            'options' => $this->options,
            'version' => $this->version,
            'demo_link' => $this->demo_link,
            'tags' => $this->tags,
            'media' => $this->getMediaData(),
            'price' => $this->getPriceData(),
            'currency' => defaultCurrency()->code,
            'published_at' => $this->created_at,
        ];
    }

    private function getMediaData(): array
    {
        return array_filter([
            'thumbnail' => $this->thumbnail_url,
            'preview_image' => !$this->isAudioPreview()
                ? $this->preview_image_url
                : null,
            'preview_video' => $this->isVideoPreview()
                ? $this->preview_video_url
                : null,
            'preview_audio' => $this->isAudioPreview()
                ? $this->preview_audio_url
                : null,
            'gallery' => $this->isImagePreview()
                ? $this->gallery_links
                : null,
        ], fn($value) => $value !== null);
    }

    private function getPriceData(): array
    {
        return [
            'regular' => $this->price->regular,
            'extended' => $this->price->extended,
        ];
    }
}
