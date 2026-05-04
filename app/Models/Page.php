<?php

namespace App\Models;

use App\Enums\Page\{PageLayout, PageHeaderStyle};
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  Page extends Model
{
    use HasFactory, Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'description',
        'preview_image',
        'header',
        'layout',
        'total_views',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'layout' => PageLayout::class,
            'header' => 'array',
            'total_views' => 'integer',
        ];
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    /**
     * Get the page's public URL.
     */
    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn() => route('page', $this->slug)
        );
    }

    /**
     * Check if page has content.
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Get excerpt from content.
     */
    public function getExcerpt(int $length = 150): string
    {
        return str($this->description ?? $this->content)->limit($length);
    }

    /**
     * Get the layout enum instance.
     */
    public function getLayout(): PageLayout
    {
        return $this->layout ?? PageLayout::FULL;
    }

    /**
     * Check if page uses full width layout.
     */
    public function isFullLayout(): bool
    {
        return $this->getLayout() === PageLayout::FULL;
    }

    /**
     * Check if page uses boxed layout.
     */
    public function isBoxedLayout(): bool
    {
        return $this->getLayout() === PageLayout::BOXED;
    }

    /**
     * Check if page uses sidebar layout.
     */
    public function isSidebarLayout(): bool
    {
        return $this->getLayout() === PageLayout::SIDEBAR;
    }

    /**
     * Get preview image URL.
     */
    public function getPreviewImage(): ?string
    {
        return $this->preview_image ? asset($this->preview_image) : null;
    }

    /**
     * Check if page has preview image.
     */
    public function hasPreviewImage(): bool
    {
        return !empty($this->preview_image);
    }

    /**
     * Check if page has header.
     */
    public function hasHeader(): bool
    {
        $header = $this->getHeader();
        return ($header['style'] ?? '') !== PageHeaderStyle::NO_HEADER->value;
    }

    /**
     * Get header configuration.
     */
    public function getHeader(): array
    {
        return $this->header ?? [
            'style' => PageHeaderStyle::STYLE_1->value,
            'breadcrumb' => true,
            'description' => false,
        ];
    }

    /**
     * Get header style.
     */
    public function getHeaderStyle(): PageHeaderStyle
    {
        $header = $this->getHeader();
        $styleValue = $header['style'] ?? PageHeaderStyle::STYLE_1->value;

        return PageHeaderStyle::tryFrom($styleValue) ?? PageHeaderStyle::STYLE_1;
    }

    /**
     * Check if header should show breadcrumb.
     */
    public function showBreadcrumb(): bool
    {
        $header = $this->getHeader();
        return $header['breadcrumb'] ?? true;
    }

    /**
     * Check if header should show description.
     */
    public function showDescription(): bool
    {
        $header = $this->getHeader();
        return $header['description'] ?? false;
    }
}


















