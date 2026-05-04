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
        // Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('views')->default(0);
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['slug', 'status', 'sort_id']);
        });

        // Sub Categories Table
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('views')->default(0);
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['category_id', 'slug', 'status']);
        });

        // Category Options Table
        Schema::create('category_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->tinyInteger('required')->default(0)->comment('0: Optional, 1: Required');
            $table->tinyInteger('is_select')->default(0)->comment('0: Input, 1: Select');
            $table->text('options')->nullable()->comment('Comma-separated values for select');
            $table->timestamps();

            $table->index('category_id');
        });

        // Pages Table
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('views')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->index(['slug', 'status']);
        });

        // Blog Categories Table
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['slug', 'status']);
        });

        // Blog Articles Table
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('editor_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description');
            $table->longText('body');
            $table->string('image');
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('views')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Draft, 1: Published');
            $table->timestamps();

            $table->index(['blog_category_id', 'slug', 'status', 'views']);
        });

        // Blog Comments Table
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('blog_article_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Published');
            $table->timestamps();

            $table->index(['user_id', 'blog_article_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_articles');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('category_options');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('categories');
    }
};
