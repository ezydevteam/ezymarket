<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $blocks = [
            [
                'title' => 'Button',
                'alias' => 'button',
                'description' => 'Add a clickable button',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Divider',
                'alias' => 'divider',
                'description' => 'Add a spacer or separator line',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Image',
                'alias' => 'image',
                'description' => 'Display a single image',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Rich Text',
                'alias' => 'rich_text',
                'description' => 'Add custom HTML or formatted text',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Social Icons',
                'alias' => 'social_icons',
                'description' => 'Display links to your social media',
                'is_active' => true,
                'options' => json_encode([]),
            ],
            [
                'title' => 'Countdown',
                'alias' => 'countdown',
                'description' => 'A countdown timer to a specific date',
                'is_active' => true,
                'options' => json_encode([]),
            ],
        ];

        foreach ($blocks as $block) {
            \DB::table('home_blocks')->insertOrIgnore($block);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $aliases = ['button', 'divider', 'image', 'rich_text', 'social_icons', 'countdown'];
        \DB::table('home_blocks')->whereIn('alias', $aliases)->delete();
    }
};
