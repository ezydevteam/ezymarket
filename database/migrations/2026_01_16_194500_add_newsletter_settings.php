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
        DB::table('settings')->insert([
            'key' => 'newsletter',
            'value' => json_encode([
                'status' => 0,
                'popup_status' => 0,
                'footer_status' => 0,
                'register_new_users' => 0,
                'popup_image' => null,
                'popup_reminder_time' => 24,
                'api_key' => null,
                'audience_id' => null,
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'newsletter')->delete();
    }
};
