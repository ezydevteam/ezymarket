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
        // Convert existing integer values to string values
        DB::table('id_verifications')->where('status', 1)->update(['status' => 0]); // temp
        DB::table('id_verifications')->where('status', 2)->update(['status' => 10]); // temp
        DB::table('id_verifications')->where('status', 3)->update(['status' => 20]); // temp

        // Change column type to string
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });

        // Update to new string values
        DB::table('id_verifications')->where('status', '0')->update(['status' => 'pending']);
        DB::table('id_verifications')->where('status', '10')->update(['status' => 'approved']);
        DB::table('id_verifications')->where('status', '20')->update(['status' => 'rejected']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert string values back to integers
        DB::table('id_verifications')->where('status', 'pending')->update(['status' => '1']);
        DB::table('id_verifications')->where('status', 'approved')->update(['status' => '2']);
        DB::table('id_verifications')->where('status', 'rejected')->update(['status' => '3']);

        // Change column type back to tinyInteger
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->change();
        });

        // Clean up to proper integer values
        DB::table('id_verifications')->where('status', '1')->update(['status' => 1]);
        DB::table('id_verifications')->where('status', '2')->update(['status' => 2]);
        DB::table('id_verifications')->where('status', '3')->update(['status' => 3]);
    }
};
