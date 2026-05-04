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
        Schema::table('product_categories', function (Blueprint $table) {
            // Rename thumbnail_type to preview_type
            $table->renameColumn('thumbnail_type', 'preview_type');

            // Rename thumbnail_file_size to preview_file_size
            $table->renameColumn('thumbnail_file_size', 'preview_file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Revert preview_type back to thumbnail_type
            $table->renameColumn('preview_type', 'thumbnail_type');

            // Revert preview_file_size back to thumbnail_file_size
            $table->renameColumn('preview_file_size', 'thumbnail_file_size');
        });
    }
};
