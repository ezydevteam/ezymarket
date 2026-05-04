<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('home_blocks')->insert([
            'title' => 'Hero Section',
            'alias' => 'hero',
            'description' => 'Modern full-width hero with video/image background and search',
            'is_active' => true,
            'options' => json_encode([
                'type' => 'video',
                'video_url' => 'videos/home-section-video.mp4',
                'image' => '',
                'overlay_color' => '#000000',
                'overlay_opacity' => '0.5',
                'title' => 'Largest Digital Products Marketplace',
                'subtitle' => 'Buy & Sell Code, Themes, Plugins & More',
                'search_enable' => '1',
                'btn1_text' => 'Start Selling',
                'btn1_url' => '/become-a-seller',
                'btn1_class' => 'btn-outline-light',
                'btn2_text' => 'Explore products',
                'btn2_url' => '/products',
                'btn2_class' => 'btn-primary'
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('home_blocks')->where('alias', 'hero')->delete();
    }
};
