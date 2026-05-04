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
        Schema::table('admin_notifications', function (Blueprint $table) {
            // Rename status to is_unread
            $table->renameColumn('status', 'is_unread');
        });

        // Invert the boolean logic: old 0 (unread) becomes 1 (is_unread=true), old 1 (read) becomes 0 (is_unread=false)
        DB::statement('UPDATE admin_notifications SET is_unread = CASE WHEN is_unread = 0 THEN 1 ELSE 0 END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Invert back the boolean logic
        DB::statement('UPDATE admin_notifications SET is_unread = CASE WHEN is_unread = 0 THEN 1 ELSE 0 END');

        Schema::table('admin_notifications', function (Blueprint $table) {
            // Rename back to status
            $table->renameColumn('is_unread', 'status');
        });
    }
};
