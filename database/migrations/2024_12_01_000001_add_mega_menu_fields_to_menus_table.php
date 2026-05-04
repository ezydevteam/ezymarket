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
        Schema::table('menus', function (Blueprint $table) {
            // Menu style for mega menu columns (single, 2col, 3col, 4col)
            $table->string('menu_style')->nullable()->after('menu_type');

            // Icon settings (Bootstrap icon class like 'bi-house')
            $table->string('icon')->nullable()->after('custom_html');

            // Icon color (hex color or Bootstrap color class)
            $table->string('icon_color')->nullable()->after('icon');

            // Hide label - useful for headings in dropdowns to show only icon
            $table->boolean('hide_label')->default(false)->after('icon_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['menu_style', 'icon', 'icon_color', 'hide_label']);
        });
    }
};
