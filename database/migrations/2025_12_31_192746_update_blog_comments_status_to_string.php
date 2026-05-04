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
        // First change the column type to string
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        // Then update existing data
        DB::table('blog_comments')->where('status', '0')->update(['status' => 'pending']);
        DB::table('blog_comments')->where('status', '1')->update(['status' => 'published']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First change the column type back
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->integer('status')->default(0)->change();
        });

        // Then revert the data
        DB::table('blog_comments')->where('status', 'pending')->update(['status' => 0]);
        DB::table('blog_comments')->where('status', 'hold')->update(['status' => 0]);
        DB::table('blog_comments')->where('status', 'published')->update(['status' => 1]);
    }
};
