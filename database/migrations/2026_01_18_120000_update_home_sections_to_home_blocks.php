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
        Schema::rename('home_sections', 'home_blocks');

        Schema::table('home_blocks', function (Blueprint $table) {
            $table->renameColumn('name', 'title');
            $table->json('options')->nullable()->after('description');

            // Removing columns that are now part of the options array
            if (Schema::hasColumn('home_blocks', 'products_number')) {
                $table->dropColumn('products_number');
            }
            if (Schema::hasColumn('home_blocks', 'items_number')) {
                $table->dropColumn('items_number');
            }
            if (Schema::hasColumn('home_blocks', 'cache_expiry_time')) {
                $table->dropColumn('cache_expiry_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
            $table->string('products_number')->nullable();
            $table->string('cache_expiry_time')->nullable();
            $table->dropColumn('options');
        });

        Schema::rename('home_blocks', 'home_sections');
    }
};
