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
            'title' => 'Calendar',
            'slug' => 'calendar',
            'class' => 'App\\Widgets\\Types\\CalendarWidget',
            'description' => 'Displays a monthly calendar',
            'icon' => 'bi bi-calendar3',
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
        DB::table('widgets')->where('slug', 'calendar')->delete();
    }
};
