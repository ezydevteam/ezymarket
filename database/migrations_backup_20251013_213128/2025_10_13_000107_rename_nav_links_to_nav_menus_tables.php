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
        // Rename top_nav_links to top_nav_menus
        Schema::rename('top_nav_links', 'top_nav_menus');

        // Rename bottom_nav_links to bottom_nav_menus
        Schema::rename('bottom_nav_links', 'bottom_nav_menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Rename top_nav_menus back to top_nav_links
        Schema::rename('top_nav_menus', 'top_nav_links');

        // Reverse: Rename bottom_nav_menus back to bottom_nav_links
        Schema::rename('bottom_nav_menus', 'bottom_nav_links');
    }
};
