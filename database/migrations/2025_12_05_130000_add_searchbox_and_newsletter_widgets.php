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
        // Add SearchBox Widget
        DB::table('widgets')->insertOrIgnore([
            'title' => 'Search Box',
            'slug' => 'search-box',
            'class' => 'App\\Widgets\\Types\\SearchBoxWidget',
            'description' => 'Display a search form',
            'icon' => 'bi bi-search',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add Newsletter Widget
        DB::table('widgets')->insertOrIgnore([
            'title' => 'Newsletter',
            'slug' => 'newsletter',
            'class' => 'App\\Widgets\\Types\\NewsletterWidget',
            'description' => 'Display a newsletter subscription form',
            'icon' => 'bi bi-envelope',
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
        DB::table('widgets')->whereIn('slug', ['search-box', 'newsletter'])->delete();
    }
};
