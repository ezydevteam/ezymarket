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
        // Products Table (formerly items)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('options')->nullable()->comment('JSON: Category custom fields');
            $table->string('tags')->nullable();
            $table->string('demo_link')->nullable();
            $table->string('regular_license_price');
            $table->string('extended_license_price')->nullable();
            $table->string('thumbnail');
            $table->string('main_file');
            $table->string('preview_image')->nullable();
            $table->string('preview_video')->nullable();
            $table->text('screenshots')->nullable()->comment('JSON: Array of image paths');
            $table->tinyInteger('free_product')->default(0)->comment('0: Paid, 1: Free');
            $table->tinyInteger('last_update_at')->nullable();
            $table->tinyInteger('purchasing_status')->default(1)->comment('0: Disabled, 1: Enabled');
            $table->tinyInteger('comments_status')->default(1)->comment('0: Disabled, 1: Enabled');
            $table->tinyInteger('reviews_status')->default(1)->comment('0: Disabled, 1: Enabled');
            $table->tinyInteger('is_on_discount')->default(0)->comment('0: No, 1: Yes');
            $table->integer('total_sales')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->decimal('avg_reviews', 2, 1)->default(0);
            $table->integer('total_comments')->default(0);
            $table->integer('total_views')->default(0);
            $table->integer('current_month_views')->default(0);
            $table->integer('current_month_sales')->default(0);
            $table->tinyInteger('is_trend')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('is_best_selling')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('is_featured')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('status')->default(0)->comment('0: Pending Review, 1: Approved, 2: Soft Rejected, 3: Hard Rejected, 4: Deleted, 5: Resubmitted');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['seller_id', 'category_id', 'sub_category_id', 'status']);
            $table->index(['slug', 'free_product', 'is_on_discount']);
            $table->index(['is_trend', 'is_best_selling', 'is_featured']);
            $table->index(['total_sales', 'total_views', 'avg_reviews']);
        });

        // Uploaded Files Table
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->string('size')->nullable();
            $table->string('extension')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'mime_type']);
        });

        // Product Updates Table
        Schema::create('product_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('version');
            $table->longText('changelog')->nullable();
            $table->string('main_file');
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Approved, 2: Rejected');
            $table->timestamps();

            $table->index(['seller_id', 'product_id', 'status']);
        });

        // Product Drafts Table
        Schema::create('product_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->longText('data')->comment('JSON: All product form data');
            $table->timestamps();

            $table->index('seller_id');
        });

        // Product Change Logs Table
        Schema::create('product_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('editor_id')->nullable()->constrained()->onDelete('set null');
            $table->text('body');
            $table->timestamp('created_at');

            $table->index(['product_id', 'editor_id']);
        });

        // Product Discounts Table
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('regular_percentage', 5, 2);
            $table->decimal('extended_percentage', 5, 2)->nullable();
            $table->dateTime('starting_at');
            $table->dateTime('ending_at');
            $table->timestamps();

            $table->index(['product_id', 'starting_at', 'ending_at']);
        });

        // Favorites Table
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at');

            $table->unique(['user_id', 'product_id']);
            $table->index('user_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('product_discounts');
        Schema::dropIfExists('product_change_logs');
        Schema::dropIfExists('product_drafts');
        Schema::dropIfExists('product_updates');
        Schema::dropIfExists('uploaded_files');
        Schema::dropIfExists('products');
    }
};
