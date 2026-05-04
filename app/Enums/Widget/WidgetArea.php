<?php

namespace App\Enums\Widget;

/**
 * WidgetArea Enum
 *
 * Pre-defined widget areas in the system.
 */
enum WidgetArea: string
{
    case HOME_SIDEBAR = 'home-sidebar';
    case PRODUCT_CATEGORY_SIDEBAR = 'product-category-sidebar';
    case PRODUCT_PAGE_SIDEBAR = 'product-page-sidebar';
    case SINGLE_PRODUCT_SIDEBAR = 'single-product-sidebar';
    case PAGE_SIDEBAR = 'page-sidebar';
    case BLOG_SIDEBAR = 'blog-sidebar';
    case FOOTER_1 = 'footer-1';
    case FOOTER_2 = 'footer-2';
    case FOOTER_3 = 'footer-3';
    case FOOTER_4 = 'footer-4';

    /**
     * Get the display label for the area.
     */
    public function label(): string
    {
        return match ($this) {
            self::HOME_SIDEBAR => translate('Home Sidebar'),
            self::PRODUCT_CATEGORY_SIDEBAR => translate('Product Category Sidebar'),
            self::PRODUCT_PAGE_SIDEBAR => translate('Product Page Sidebar'),
            self::SINGLE_PRODUCT_SIDEBAR => translate('Single Product Sidebar'),
            self::PAGE_SIDEBAR => translate('Page Sidebar'),
            self::BLOG_SIDEBAR => translate('Blog Sidebar'),
            self::FOOTER_1 => translate('Footer Column 1'),
            self::FOOTER_2 => translate('Footer Column 2'),
            self::FOOTER_3 => translate('Footer Column 3'),
            self::FOOTER_4 => translate('Footer Column 4'),
        };
    }

    /**
     * Get the description for the area.
     */
    public function description(): string
    {
        return match ($this) {
            self::HOME_SIDEBAR => translate('Sidebar on home page'),
            self::PRODUCT_CATEGORY_SIDEBAR => translate('Sidebar on product category & sub-category pages'),
            self::PRODUCT_PAGE_SIDEBAR => translate('Sidebar on product search and index pages'),
            self::SINGLE_PRODUCT_SIDEBAR => translate('Sidebar on single product pages'),
            self::PAGE_SIDEBAR => translate('Sidebar on static pages'),
            self::BLOG_SIDEBAR => translate('Sidebar on blog pages'),
            self::FOOTER_1 => translate('First footer column'),
            self::FOOTER_2 => translate('Second footer column'),
            self::FOOTER_3 => translate('Third footer column'),
            self::FOOTER_4 => translate('Fourth footer column'),
        };
    }

    /**
     * Get the icon for the area.
     */
    public function icon(): string
    {
        return match ($this) {
            self::HOME_SIDEBAR => 'bi bi-house',
            self::PRODUCT_CATEGORY_SIDEBAR => 'bi bi-folder',
            self::PRODUCT_PAGE_SIDEBAR => 'bi bi-layout-sidebar',
            self::SINGLE_PRODUCT_SIDEBAR => 'bi bi-layout-sidebar-reverse',
            self::PAGE_SIDEBAR => 'bi bi-file-text',
            self::BLOG_SIDEBAR => 'bi bi-journal-text',
            self::FOOTER_1, self::FOOTER_2, self::FOOTER_3, self::FOOTER_4 => 'bi bi-columns-gap',
        };
    }

    /**
     * Get all area values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all areas as options array for select.
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
     * Get area from value.
     */
    public static function fromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }
}
