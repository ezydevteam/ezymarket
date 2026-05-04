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
            'title' => 'Popular Products',
            'slug' => 'popular-products',
            'class' => 'App\\Widgets\\Types\\PopularProductsWidget',
            'description' => 'Display popular/best-selling products',
            'icon' => 'bi bi-fire',
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
        DB::table('widgets')->where('slug', 'popular-products')->delete();
    }
};
