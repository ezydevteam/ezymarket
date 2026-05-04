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
                'title' => 'Slider (Swiper)',
                'alias' => 'slider',
                'description' => 'Image carousel with captions',
                'is_active' => true,
                'options' => json_encode([
                    'height' => 400,
                    'autoplay' => true,
                    'content' => [], // Repeater data
                ]),
            ],
            [
                'title' => 'Blog Categories',
                'alias' => 'blog_categories',
                'description' => 'Grid of blog categories',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Login Form',
                'alias' => 'login_form',
                'description' => 'User login form (visible to guests)',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Premium Plans',
                'alias' => 'premium_plans',
                'description' => 'Premium membership plans pricing table',
                'is_active' => true,
                'options' => json_encode([]),
            ],
        ];

        foreach ($blocks as $block) {
            // Check if exists to avoid duplication
            if (!DB::table('home_blocks')->where('alias', $block['alias'])->exists()) {
                DB::table('home_blocks')->insert($block);
            } else {
                // Determine if we should update.
                // For Slider, maybe it was inserted before but "empty".
                // Let's just update the title/desc to be sure.
                DB::table('home_blocks')->where('alias', $block['alias'])->update([
                    'title' => $block['title'],
                    'description' => $block['description'],
                    'options' => $block['options'] // Reset options ensuring defaults are there
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('home_blocks')->whereIn('alias', ['slider', 'blog_categories', 'login_form', 'premium_plans'])->delete();
    }
};
