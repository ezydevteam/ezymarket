<?php

namespace App\Classes;

class BootstrapIcons
{
    /**
     * Get all available Bootstrap Icons
     *
     * @param bool $sortAlphabetically Sort by label alphabetically
     * @return array Icon class => Label
     */
    public static function all(bool $sortAlphabetically = false): array
    {
        $icons = [
            // Navigation & Home
            'bi-house' => 'Home',
            'bi-house-door' => 'Home Door',
            'bi-house-fill' => 'Home Filled',
            'bi-speedometer' => 'Dashboard',
            'bi-speedometer2' => 'Dashboard Alt',
            'bi-grid' => 'Grid',
            'bi-grid-3x3' => 'Grid 3x3',
            'bi-grid-3x3-gap' => 'Grid Gap',
            'bi-list' => 'List',
            'bi-menu-button' => 'Menu Button',
            'bi-compass' => 'Explore',

            // Shopping & Products
            'bi-cart' => 'Cart',
            'bi-cart2' => 'Cart 2',
            'bi-cart3' => 'Cart 3',
            'bi-cart4' => 'Cart 4',
            'bi-cart-check' => 'Cart Check',
            'bi-cart-fill' => 'Cart Filled',
            'bi-cart-plus' => 'Add to Cart',
            'bi-bag' => 'Shopping Bag',
            'bi-bag-check' => 'Bag Check',
            'bi-bag-fill' => 'Bag Filled',
            'bi-bag-plus' => 'Add to Bag',
            'bi-shop' => 'Shop',
            'bi-shop-window' => 'Shop Window',
            'bi-basket' => 'Basket',
            'bi-basket2' => 'Basket 2',
            'bi-basket3' => 'Basket 3',
            'bi-handbag' => 'Handbag',

            // Digital Products & Files
            'bi-file-earmark' => 'File',
            'bi-file-earmark-code' => 'Code File',
            'bi-file-earmark-zip' => 'Zip File',
            'bi-file-earmark-text' => 'Text File',
            'bi-file-earmark-pdf' => 'PDF File',
            'bi-file-earmark-image' => 'Image File',
            'bi-file-earmark-music' => 'Audio File',
            'bi-file-earmark-play' => 'Video File',
            'bi-filetype-js' => 'JavaScript',
            'bi-filetype-php' => 'PHP',
            'bi-filetype-css' => 'CSS',
            'bi-filetype-html' => 'HTML',
            'bi-filetype-psd' => 'PSD',
            'bi-filetype-ai' => 'AI',
            'bi-filetype-svg' => 'SVG',
            'bi-code-square' => 'Code',
            'bi-code-slash' => 'Code Slash',
            'bi-braces' => 'Braces',
            'bi-terminal' => 'Terminal',

            // Categories
            'bi-folder' => 'Folder',
            'bi-folder2' => 'Folder 2',
            'bi-folder-fill' => 'Folder Filled',
            'bi-folder-plus' => 'Folders',
            'bi-archive' => 'Archive',
            'bi-box-seam' => 'Package',
            'bi-boxes' => 'Boxes',
            'bi-collection' => 'Collection',
            'bi-layers' => 'Layers',
            'bi-stack' => 'Stack',

            // Media & Content
            'bi-image' => 'Image',
            'bi-images' => 'Images',
            'bi-camera' => 'Camera',
            'bi-film' => 'Video',
            'bi-music-note' => 'Music',
            'bi-music-note-beamed' => 'Music Notes',
            'bi-mic' => 'Microphone',
            'bi-headphones' => 'Audio',
            'bi-palette' => 'Design',
            'bi-brush' => 'Brush',
            'bi-paint-bucket' => 'Paint',

            // Templates & Themes
            'bi-layout-text-window' => 'Layout',
            'bi-layout-text-sidebar' => 'Template',
            'bi-layout-three-columns' => 'Columns',
            'bi-layout-split' => 'Split Layout',
            'bi-window' => 'Window',
            'bi-easel' => 'Design',
            'bi-palette2' => 'Theme',
            'bi-brush-fill' => 'Customize',

            // WordPress & CMS
            'bi-wordpress' => 'WordPress',
            'bi-plugin' => 'Plugin',
            'bi-puzzle' => 'Extension',
            'bi-nut' => 'Component',

            // Books & Learning
            'bi-book' => 'Book',
            'bi-book-half' => 'eBook',
            'bi-journal' => 'Journal',
            'bi-journal-text' => 'Article',
            'bi-newspaper' => 'News',
            'bi-mortarboard' => 'Course',
            'bi-bookmark' => 'Bookmark',
            'bi-bookmark-star' => 'Featured',
            'bi-trophy' => 'Achievement',

            // Graphics & Icons
            'bi-gem' => 'Premium',
            'bi-award' => 'Award',
            'bi-badge-3d' => '3D Badge',
            'bi-vector-pen' => 'Vector',
            'bi-bezier' => 'Bezier',
            'bi-bezier2' => 'Bezier 2',
            'bi-pentagon' => 'Shape',
            'bi-star' => 'Star',
            'bi-star-fill' => 'Star Filled',
            'bi-heart' => 'Heart',
            'bi-heart-fill' => 'Heart Filled',

            // Tags & Labels
            'bi-tag' => 'Tag',
            'bi-tags' => 'Tags',
            'bi-tag-fill' => 'Tag Filled',
            'bi-tags-fill' => 'Tags Filled',
            'bi-bookmark-check' => 'Tagged',

            // User & Account
            'bi-person' => 'User',
            'bi-person-circle' => 'Profile',
            'bi-person-fill' => 'User Filled',
            'bi-people' => 'Users',
            'bi-person-badge' => 'Author',
            'bi-person-workspace' => 'Developer',
            'bi-person-video' => 'Creator',
            'bi-person-gear' => 'Account Settings',

            // Shopping Features
            'bi-gift' => 'Gift',
            'bi-gift-fill' => 'Gift Filled',
            'bi-currency-dollar' => 'Price',
            'bi-cash' => 'Cash',
            'bi-credit-card' => 'Payment',
            'bi-wallet' => 'Wallet',
            'bi-wallet2' => 'Wallet 2',
            'bi-receipt' => 'Receipt',
            'bi-coin' => 'Coin',
            'bi-piggy-bank' => 'Savings',

            // Downloads & Upload
            'bi-download' => 'Download',
            'bi-cloud-download' => 'Cloud Download',
            'bi-cloud-arrow-down' => 'Download Alt',
            'bi-upload' => 'Upload',
            'bi-cloud-upload' => 'Cloud Upload',
            'bi-cloud-arrow-up' => 'Upload Alt',
            'bi-box-arrow-down' => 'Export',
            'bi-box-arrow-up' => 'Import',

            // Communication
            'bi-envelope' => 'Email',
            'bi-envelope-fill' => 'Email Filled',
            'bi-chat' => 'Chat',
            'bi-chat-dots' => 'Chat Dots',
            'bi-chat-left' => 'Chat Left',
            'bi-chat-left-dots' => 'Chat Left Dots',
            'bi-chat-right' => 'Chat Right',
            'bi-chat-text' => 'Chat Text',
            'bi-chat-text-fill' => 'Chat Text Filled',
            'bi-chat-quote' => 'Chat Quote',
            'bi-chat-square' => 'Chat Square',
            'bi-chat-square-dots' => 'Chat Square Dots',
            'bi-chat-square-text' => 'Chat Square Text',
            'bi-chat-square-text-fill' => 'Chat Square Text Filled',
            'bi-telephone' => 'Phone',
            'bi-headset' => 'Support',
            'bi-question-circle' => 'Help',
            'bi-info-circle' => 'Info',

            // Actions & Tools
            'bi-search' => 'Search',
            'bi-filter' => 'Filter',
            'bi-funnel' => 'Funnel',
            'bi-sort-down' => 'Sort',
            'bi-sliders' => 'Settings',
            'bi-gear' => 'Settings',
            'bi-tools' => 'Tools',
            'bi-wrench' => 'Configure',
            'bi-toggles' => 'Toggle',

            // Notifications & Status
            'bi-bell' => 'Notifications',
            'bi-bell-fill' => 'Notifications Filled',
            'bi-bell-slash' => 'Notifications Off',
            'bi-bell-slash-fill' => 'Notifications Off Filled',
            'bi-megaphone' => 'Announcements',
            'bi-lightning' => 'Flash Sale',
            'bi-fire' => 'Hot',
            'bi-rocket' => 'New',
            'bi-transparency' => 'Featured',
            'bi-app-indicator' => 'App Indicator',

            // Social & Sharing
            'bi-share' => 'Share',
            'bi-share-fill' => 'Share Filled',
            'bi-link-45deg' => 'Link',
            'bi-globe' => 'Website',
            'bi-facebook' => 'Facebook',
            'bi-twitter' => 'Twitter',
            'bi-instagram' => 'Instagram',
            'bi-youtube' => 'YouTube',
            'bi-linkedin' => 'LinkedIn',
            'bi-github' => 'GitHub',

            // Security & License
            'bi-shield-check' => 'Verified',
            'bi-shield-lock' => 'Secure',
            'bi-key' => 'License',
            'bi-lock' => 'Private',
            'bi-unlock' => 'Public',

            // Ratings & Reviews
            'bi-star-half' => 'Rating',
            'bi-hand-thumbs-up' => 'Like',
            'bi-hand-thumbs-down' => 'Dislike',
            'bi-emoji-smile' => 'Review',

            // Analytics & Stats
            'bi-graph-up' => 'Trending',
            'bi-graph-up-arrow' => 'Growth',
            'bi-bar-chart' => 'Stats',
            'bi-pie-chart' => 'Analytics',
            'bi-eye' => 'Views',

            // Time & Updates
            'bi-clock' => 'Time',
            'bi-clock-history' => 'History',
            'bi-calendar' => 'Calendar',
            'bi-calendar-event' => 'Event',
            'bi-arrow-repeat' => 'Update',
            'bi-arrow-clockwise' => 'Refresh',

            // Delivery & Shipping
            'bi-truck' => 'Delivery',
            'bi-box-seam' => 'Shipping',
            'bi-mailbox' => 'Mailbox',
            'bi-send' => 'Send',

            // Arrows & Navigation
            'bi-arrow-right' => 'Arrow Right',
            'bi-arrow-left' => 'Arrow Left',
            'bi-arrow-up' => 'Arrow Up',
            'bi-arrow-down' => 'Arrow Down',
            'bi-chevron-right' => 'Chevron Right',
            'bi-chevron-left' => 'Chevron Left',
            'bi-chevron-up' => 'Chevron Up',
            'bi-chevron-down' => 'Chevron Down',
            'bi-caret-right' => 'Caret Right',
            'bi-caret-down' => 'Caret Down',

            // Special Categories
            'bi-lightning-charge' => 'Best Sellers',
            'bi-bullseye' => 'Featured',
            'bi-bookmark-heart' => 'Favorites',
            'bi-percent' => 'Discount',
            'bi-exclamation-circle' => 'Limited',
            'bi-infinity' => 'Unlimited',

            // Miscellaneous
            'bi-three-dots' => 'Three Dots',
            'bi-three-dots-vertical' => 'Three Dots Vertical',
            'bi-plus' => 'Plus',
            'bi-plus-circle' => 'Add',
            'bi-dash-circle' => 'Remove',
            'bi-dash-lg' => 'Minus',
            'bi-dash' => 'Dash',
            'bi-x' => 'Close',
            'bi-x-circle' => 'Close Circle',
            'bi-check' => 'Check',
            'bi-check2' => 'Check 2',
            'bi-check-circle' => 'Verified',
            'bi-check2-circle' => 'Verified 2',
            'bi-shield-exclamation' => 'Alert',
            'bi-emoji-frown' => 'Sad',
        ];

        if ($sortAlphabetically) {
            asort($icons);
        }

        return $icons;
    }

    /**
     * Get icon by category
     *
     * @param string $category
     * @return array
     */
    public static function getByCategory(string $category): array
    {
        $categories = [
            'navigation' => ['bi-house', 'bi-speedometer2', 'bi-grid', 'bi-list', 'bi-compass'],
            'shopping' => ['bi-cart', 'bi-bag', 'bi-shop', 'bi-basket'],
            'digital' => ['bi-file-earmark-code', 'bi-code-square', 'bi-terminal', 'bi-braces'],
            'media' => ['bi-image', 'bi-film', 'bi-music-note', 'bi-headphones'],
            'design' => ['bi-palette', 'bi-brush', 'bi-easel', 'bi-vector-pen'],
            'user' => ['bi-person', 'bi-people', 'bi-person-circle', 'bi-person-badge'],
        ];

        return array_intersect_key(
            self::all(),
            array_flip($categories[$category] ?? [])
        );
    }

    /**
     * Search icons by keyword
     *
     * @param string $keyword
     * @return array
     */
    public static function search(string $keyword): array
    {
        $keyword = strtolower($keyword);
        return array_filter(
            self::all(),
            fn($label, $class) =>
            str_contains(strtolower($label), $keyword) ||
                str_contains(strtolower($class), $keyword),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
