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
        $oldSettings = DB::table('chatbox_settings')->first();
        if ($oldSettings) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'chatbox'],
                [
                    'value' => json_encode([
                        'status' => (bool) $oldSettings->chatbox_system_enabled,
                        'banned_keywords' => json_decode($oldSettings->banned_keywords) ?? [],
                    ])
                ]
            );
        } else {
             DB::table('settings')->updateOrInsert(
                ['key' => 'chatbox'],
                [
                    'value' => json_encode([
                        'status' => true,
                        'banned_keywords' => [],
                    ])
                ]
            );
        }

        Schema::dropIfExists('chatbox_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('chatbox_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('chatbox_system_enabled')->default(true);
            $table->longText('banned_keywords')->nullable();
            $table->timestamps();
        });

        $setting = DB::table('settings')->where('key', 'chatbox')->first();
        if ($setting) {
            $value = json_decode($setting->value, true);
            DB::table('chatbox_settings')->insert([
                'chatbox_system_enabled' => $value['status'] ?? true,
                'banned_keywords' => isset($value['banned_keywords']) ? json_encode($value['banned_keywords']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('settings')->where('key', 'chatbox')->delete();
    }
};
