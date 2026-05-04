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
        // Update products table
        DB::table('products')
            ->where('status', 'soft_rejected')
            ->update(['status' => 'needs_revision']);

        DB::table('products')
            ->where('status', 'hard_rejected')
            ->update(['status' => 'rejected']);

        DB::table('products')
            ->where('previous_status', 'soft_rejected')
            ->update(['previous_status' => 'needs_revision']);

        DB::table('products')
            ->where('previous_status', 'hard_rejected')
            ->update(['previous_status' => 'rejected']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')
            ->where('status', 'needs_revision')
            ->update(['status' => 'soft_rejected']);

        DB::table('products')
            ->where('status', 'rejected')
            ->update(['status' => 'hard_rejected']);

        DB::table('products')
            ->where('previous_status', 'needs_revision')
            ->update(['previous_status' => 'soft_rejected']);

        DB::table('products')
            ->where('previous_status', 'rejected')
            ->update(['previous_status' => 'hard_rejected']);
    }
};
