<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $blocks = [
            [
                'title' => 'Newsletter',
                'alias' => 'newsletter',
                'description' => 'Newsletter subscription form',
                'is_active' => true,
                'options' => json_encode([
                    'title' => 'Subscribe to Our Newsletter',
                    'subtitle' => 'Stay tuned for the latest updates',
                    'bg_color' => '#f8f9fa',
                ]),
            ],
            [
                'title' => 'Offer Banner',
                'alias' => 'offer_banner',
                'description' => 'Banner with text, image and CTA',
                'is_active' => true,
                'options' => json_encode([
                    'title' => 'Special Offer',
                    'description' => 'Get 50% off on all items!',
                    'btn_text' => 'Shop Now',
                    'btn_url' => '#',
                    'image' => '',
                    'align' => 'left', // left, center, right, reverse
                ]),
            ],
            [
                'title' => 'General Tabs',
                'alias' => 'tabs',
                'description' => 'Content tabs',
                'is_active' => true,
                'options' => json_encode([
                    'content' => [
                        ['title' => 'Tab 1', 'html' => '<p>Content 1</p>'],
                        ['title' => 'Tab 2', 'html' => '<p>Content 2</p>']
                    ]
                ]),
            ],
            [
                'title' => 'Product Tabs',
                'alias' => 'product_tabs',
                'description' => 'Products displayed in tabs (Trending, Featured, etc)',
                'is_active' => true,
                'options' => json_encode([
                    'show_trending' => 1,
                    'show_featured' => 1,
                    'show_best_selling' => 1,
                    'show_new' => 1,
                    'limit' => 8,
                ]),
            ],
            [
                'title' => 'HTML Code',
                'alias' => 'html',
                'description' => 'Raw HTML code block',
                'is_active' => true,
                'options' => json_encode([
                    'html' => '<!-- Custom HTML -->',
                ]),
            ],
        ];

        foreach ($blocks as $block) {
            DB::table('home_blocks')->insert($block);
        }

        // Update Slider description/options if needed, or assume it exists.
        // If slider alias exists, maybe update it? Let's assume it exists and we just edit view.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('home_blocks')->whereIn('alias', ['newsletter', 'offer_banner', 'tabs', 'product_tabs', 'html'])->delete();
    }
};
