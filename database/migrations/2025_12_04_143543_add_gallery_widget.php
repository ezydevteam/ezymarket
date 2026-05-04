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
        DB::table('widgets')->insert([
            'title' => 'Gallery',
            'slug' => 'gallery',
            'class' => 'App\\Widgets\\Types\\GalleryWidget',
            'description' => 'Display a gallery of images',
            'icon' => 'bi bi-images',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('widgets')->where('slug', 'gallery')->delete();
    }
};
