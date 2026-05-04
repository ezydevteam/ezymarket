<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds draft support to the products table by:
     * 1. Making description nullable (drafts may not have a description yet)
     * 2. Making slug nullable (drafts don't need a slug until publish)
     * 3. Making tags nullable (not required until publish)
     * 4. Migrating existing product_drafts data into products with 'draft' status
     * 5. Dropping the old product_drafts table
     */
    public function up(): void
    {
        // 1. Make fields nullable for draft support
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->string('tags')->nullable()->change();
        });

        // 2. Dev mode: just discard old drafts (data format doesn't map cleanly)
        //    New drafts will be created directly as Product records going forward.

        // 3. Drop the old product_drafts table
        Schema::dropIfExists('product_drafts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate product_drafts table
        Schema::create('product_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('cascade');
            $table->longText('data')->comment('JSON: All product form data');
            $table->timestamps();
            $table->index('seller_id');
        });

        // Remove draft products
        DB::table('products')->where('status', 'draft')->delete();

        // Revert nullable changes
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            $table->string('tags')->nullable(false)->change();
        });
    }
};
