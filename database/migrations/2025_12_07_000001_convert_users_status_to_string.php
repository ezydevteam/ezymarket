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
        // First, add a temporary column to hold the new string values
        Schema::table('users', function (Blueprint $table) {
            $table->string('status_new')->default('active')->after('status');
        });

        // Convert existing integer values to string values
        DB::table('users')->where('status', 0)->update(['status_new' => 'suspended']);
        DB::table('users')->where('status', 1)->update(['status_new' => 'active']);

        // Drop the old column and rename the new one
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add temporary column to hold integer values
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('status_new')->default(1)->after('status');
        });

        // Convert string values back to integers
        DB::table('users')->where('status', 'suspended')->update(['status_new' => 0]);
        DB::table('users')->where('status', 'active')->update(['status_new' => 1]);

        // Drop the old column and rename the new one
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }
};
