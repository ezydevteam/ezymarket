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
            'key' => 'watermark',
            'value' => json_encode([
                'status' => 0,
                'image' => null,
                'position' => 'center',
                'width' => 150,
                'height' => 150,
                'opacity' => 50,
                'rotate' => 0,
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'watermark')->delete();
    }
};
