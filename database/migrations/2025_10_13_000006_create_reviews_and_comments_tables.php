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
        // Product Reviews Table
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('body');
            $table->decimal('stars', 2, 1);
            $table->tinyInteger('is_response')->default(0)->comment('0: No reply, 1: Has reply');
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->unique(['purchase_id'])->comment('One review per purchase');
            $table->index(['user_id', 'product_id', 'status']);
        });

        // Product Review Replies Table
        Schema::create('product_review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->index(['product_review_id', 'user_id', 'status']);
        });

        // Product Comments Table
        Schema::create('product_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->tinyInteger('is_response')->default(0)->comment('0: No replies, 1: Has replies');
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->index(['user_id', 'product_id', 'status']);
        });

        // Product Comment Replies Table
        Schema::create('product_comment_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->index(['product_comment_id', 'user_id', 'status']);
        });

        // Product Comment Reports Table
        Schema::create('product_comment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_comment_id')->constrained()->onDelete('cascade');
            $table->text('reason');
            $table->tinyInteger('status')->default(0)->comment('0: Under Review, 1: Reviewed');
            $table->timestamps();

            $table->unique(['user_id', 'product_comment_id'])->comment('One report per user per comment');
            $table->index(['product_comment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_comment_reports');
        Schema::dropIfExists('product_comment_replies');
        Schema::dropIfExists('product_comments');
        Schema::dropIfExists('product_review_replies');
        Schema::dropIfExists('product_reviews');
    }
};
