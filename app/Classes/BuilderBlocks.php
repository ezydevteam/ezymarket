<?php

namespace App\Classes;

/**
 * Builder Elements
 *
 * Provides element definitions for Home, Header, and Footer builders.
 *
 * @package App\Classes
 * @author EzyDev Team
 * @version 1.0.0
 */
class BuilderBlocks
{
    /**
     * Get all builder elements.
     *
     * @return array
     */
    public static function all(): array
    {
        $elements = [
            // ============================================================================
            // HOME BUILDER - GENERAL
            // ============================================================================
            ['id' => 'home_hero', 'title' => 'Hero Section', 'icon' => 'bi-window-fullscreen', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_categories', 'title' => 'Categories', 'icon' => 'bi-grid-3x3-gap', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_faqs', 'title' => "FAQ's", 'icon' => 'bi-question-circle', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_testimonials', 'title' => 'Testimonials', 'icon' => 'bi-chat-quote', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_newsletter', 'title' => 'Newsletter', 'icon' => 'bi-envelope-paper', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_featured_seller', 'title' => 'Featured Seller', 'icon' => 'bi-person-badge', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_offer_banner', 'title' => 'Offer Banner', 'icon' => 'bi-megaphone', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_premium_plans', 'title' => 'Premium Plans', 'icon' => 'bi-gem', 'group' => 'General', 'type' => 'home'],

            // ============================================================================
            // HOME BUILDER - PRODUCTS
            // ============================================================================
            ['id' => 'home_products', 'title' => 'Products', 'icon' => 'bi-bag', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_product_tabs', 'title' => 'Product Tabs', 'icon' => 'bi-window-dock', 'group' => 'General', 'type' => 'home'],

            // ============================================================================
            // HOME BUILDER - BLOG
            // ============================================================================
            ['id' => 'home_blog_articles', 'title' => 'Blog Posts', 'icon' => 'bi-newspaper', 'group' => 'General', 'type' => 'home'],
            ['id' => 'home_blog_categories', 'title' => 'Blog Categories', 'icon' => 'bi-folder', 'group' => 'General', 'type' => 'home'],

            // ============================================================================
            // HOME BUILDER - ELEMENTS
            // ============================================================================
            ['id' => 'home_button', 'title' => 'Button', 'icon' => 'bi-hand-index', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_divider', 'title' => 'Divider', 'icon' => 'bi-hr', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_image', 'title' => 'Image', 'icon' => 'bi-image', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_rich_text', 'title' => 'Rich Text', 'icon' => 'bi-text-paragraph', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_html', 'title' => 'HTML Code', 'icon' => 'bi-code-slash', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_social_icons', 'title' => 'Social Icons', 'icon' => 'bi-share', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_tabs', 'title' => 'General Tabs', 'icon' => 'bi-window-stack', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_slider', 'title' => 'Slider (Swiper)', 'icon' => 'bi-collection-play', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_widget', 'title' => 'Home Widget', 'icon' => 'bi-grid-1x2', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_advertisement', 'title' => 'Advertisement', 'icon' => 'bi-badge-ad', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_countdown', 'title' => 'Countdown', 'icon' => 'bi-clock-history', 'group' => 'Elements', 'type' => 'home'],
            ['id' => 'home_login_form', 'title' => 'Login Form', 'icon' => 'bi-box-arrow-in-right', 'group' => 'Elements', 'type' => 'home'],

            // ============================================================================
            // HEADER BUILDER
            // ============================================================================
            ['id' => 'header_logo', 'title' => 'Logo', 'icon' => 'bi-image', 'group' => 'Branding', 'type' => 'header'],
            ['id' => 'header_menu', 'title' => 'Menu', 'icon' => 'bi-list', 'group' => 'Navigation', 'type' => 'header'],
            ['id' => 'header_search', 'title' => 'Search', 'icon' => 'bi-search', 'group' => 'Navigation', 'type' => 'header'],
            ['id' => 'header_auth', 'title' => 'User Menu', 'icon' => 'bi-person-circle', 'group' => 'User', 'type' => 'header'],
            ['id' => 'header_cart', 'title' => 'Cart', 'icon' => 'bi-cart', 'group' => 'User', 'type' => 'header'],
            ['id' => 'header_favorites', 'title' => 'Favorites', 'icon' => 'bi-heart', 'group' => 'User', 'type' => 'header'],
            ['id' => 'header_notification', 'title' => 'Notification', 'icon' => 'bi-bell', 'group' => 'User', 'type' => 'header'],
            ['id' => 'header_message', 'title' => 'Message', 'icon' => 'bi-chat-dots', 'group' => 'User', 'type' => 'header'],
            ['id' => 'header_language_currency', 'title' => 'Language', 'icon' => 'bi-globe', 'group' => 'Utilities', 'type' => 'header'],
            ['id' => 'header_theme_toggle', 'title' => 'Theme Toggle', 'icon' => 'bi-moon-stars', 'group' => 'Utilities', 'type' => 'header'],
            ['id' => 'header_button', 'title' => 'Button', 'icon' => 'bi-hand-index', 'group' => 'Actions', 'type' => 'header'],
            ['id' => 'header_divider', 'title' => 'Divider', 'icon' => 'bi-vr', 'group' => 'Elements', 'type' => 'header'],
            ['id' => 'header_social', 'title' => 'Social', 'icon' => 'bi-share', 'group' => 'Elements', 'type' => 'header'],
            ['id' => 'header_offcanvas', 'title' => 'Offcanvas', 'icon' => 'bi-layout-sidebar', 'group' => 'Elements', 'type' => 'header'],
            ['id' => 'header_html', 'title' => 'HTML', 'icon' => 'bi-code-slash', 'group' => 'Elements', 'type' => 'header'],
            ['id' => 'header_countdown', 'title' => 'Countdown', 'icon' => 'bi-clock', 'group' => 'Elements', 'type' => 'header'],
            ['id' => 'header_icon', 'title' => 'Icon', 'icon' => 'bi-star', 'group' => 'Elements', 'type' => 'header'],
            // ============================================================================
            // FOOTER BUILDER
            // ============================================================================
            ['id' => 'footer_logo', 'title' => 'Logo', 'icon' => 'bi-image', 'group' => 'Branding', 'type' => 'footer'],
            ['id' => 'footer_about', 'title' => 'About Text', 'icon' => 'bi-info-circle', 'group' => 'Branding', 'type' => 'footer'],
            ['id' => 'footer_menu', 'title' => 'Footer Menu', 'icon' => 'bi-list-ul', 'group' => 'Navigation', 'type' => 'footer'],
            ['id' => 'footer_links', 'title' => 'Quick Links', 'icon' => 'bi-link-45deg', 'group' => 'Navigation', 'type' => 'footer'],
            ['id' => 'footer_social', 'title' => 'Social Icons', 'icon' => 'bi-share', 'group' => 'Social', 'type' => 'footer'],
            ['id' => 'footer_newsletter', 'title' => 'Newsletter', 'icon' => 'bi-envelope', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_contact', 'title' => 'Contact Info', 'icon' => 'bi-telephone', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_search', 'title' => 'Search Box', 'icon' => 'bi-search', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_copyright', 'title' => 'Copyright Text', 'icon' => 'bi-c-circle', 'group' => 'Legal', 'type' => 'footer'],
            ['id' => 'footer_payment_icons', 'title' => 'Payment Icons', 'icon' => 'bi-credit-card', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_widget_1', 'title' => 'Footer Widget 1', 'icon' => 'bi-grid-1x2', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_widget_2', 'title' => 'Footer Widget 2', 'icon' => 'bi-grid-1x2', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_widget_3', 'title' => 'Footer Widget 3', 'icon' => 'bi-grid-1x2', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_widget_4', 'title' => 'Footer Widget 4', 'icon' => 'bi-grid-1x2', 'group' => 'Widgets', 'type' => 'footer'],
            ['id' => 'footer_divider', 'title' => 'Divider', 'icon' => 'bi-hr', 'group' => 'Elements', 'type' => 'footer'],
            ['id' => 'footer_html', 'title' => 'HTML', 'icon' => 'bi-code-slash', 'group' => 'Elements', 'type' => 'footer'],
            ['id' => 'footer_button', 'title' => 'Button', 'icon' => 'bi-hand-index', 'group' => 'Elements', 'type' => 'footer'],
            ['id' => 'footer_language', 'title' => 'Language', 'icon' => 'bi-globe', 'group' => 'Elements', 'type' => 'footer'],
            ['id' => 'footer_countdown', 'title' => 'Countdown', 'icon' => 'bi-clock', 'group' => 'Elements', 'type' => 'footer'],
        ];

        // Conditionally add premium_upgrade header element
        if (function_exists('isPremiumAvailable') && isPremiumAvailable()) {
            // Insert after button in Actions group
            $insertIndex = array_search('header_button', array_column($elements, 'id'));
            if ($insertIndex !== false) {
                array_splice($elements, $insertIndex + 1, 0, [[
                    'id' => 'header_premium',
                    'title' => 'Premium',
                    'icon' => 'bi-gem',
                    'group' => 'Actions',
                    'type' => 'header'
                ]]);
            }
        }

        return $elements;
    }

    /**
     * Get the view name for a block ID (removes prefix if present/matched)
     *
     * @param string $blockId
     * @param string $prefix
     * @return string
     */
    public static function getViewName(string $blockId, string $prefix = ''): string
    {
        // If specific prefix provided, try to strip it
        if (!empty($prefix) && strpos($blockId, $prefix) === 0) {
            return substr($blockId, strlen($prefix));
        }

        // Fallback: automatic detection if no prefix passed
        if (empty($prefix)) {
            if (strpos($blockId, 'home_') === 0) {
                return substr($blockId, 5);
            }
            if (strpos($blockId, 'header_') === 0) {
                return substr($blockId, 7);
            }
            if (strpos($blockId, 'footer_') === 0) {
                return substr($blockId, 7);
            }
        }

        return $blockId;
    }
    /**
     * Get elements by type.
     *
     * @param string $type home|header|footer
     * @return array
     */
    public static function byType(string $type): array
    {
        return array_filter(self::all(), fn($el) => $el['type'] === $type);
    }

    /**
     * Get elements grouped by their group for a specific type.
     *
     * @param string $type home|header|footer
     * @return array
     */
    public static function groupedByType(string $type): array
    {
        $elements = self::byType($type);
        $groups = [];

        foreach ($elements as $element) {
            $group = $element['group'];
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][] = $element;
        }

        return $groups;
    }

    /**
     * Get a specific element by ID.
     *
     * @param string $id
     * @return array|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $element) {
            if ($element['id'] === $id) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Get a specific element by title.
     *
     * @param string $title
     * @return array|null
     */
    public static function findByTitle(string $title): ?array
    {
        foreach (self::all() as $element) {
            if (strcasecmp($element['title'], $title) === 0) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Get home builder elements.
     *
     * @return array
     */
    public static function home(): array
    {
        return self::groupedByType('home');
    }

    /**
     * Get header builder elements (grouped).
     *
     * @return array
     */
    public static function header(): array
    {
        return array_values(self::byType('header'));
    }

    /**
     * Get footer builder elements (grouped).
     *
     * @return array
     */
    public static function footer(): array
    {
        return array_values(self::byType('footer'));
    }
}
