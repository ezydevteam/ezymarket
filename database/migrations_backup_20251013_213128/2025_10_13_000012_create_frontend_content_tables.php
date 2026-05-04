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
        // Nav Menus Table (Top Navigation & Footer)
        Schema::create('nav_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('link');
            $table->tinyInteger('target')->default(0)->comment('0: Same Tab, 1: New Tab');
            $table->tinyInteger('type')->default(1)->comment('1: Top Nav, 2: Footer Nav');
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['type', 'status', 'sort_id']);
        });

        // Home Sections Table
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('description')->nullable();
            $table->integer('items_number')->default(12);
            $table->integer('cache_expiry_time')->default(3600)->comment('Seconds');
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status', 'sort_id']);
        });

        // Home Categories Table
        Schema::create('home_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('sort_id')->default(0);
            $table->timestamps();

            $table->unique('category_id');
            $table->index('sort_id');
        });

        // Ads Table
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('alias')->unique();
            $table->string('size');
            $table->longText('code')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Testimonials Table
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('image');
            $table->text('body');
            $table->decimal('stars', 2, 1)->default(5.0);
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['status', 'sort_id']);
        });

        // FAQs Table
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['status', 'sort_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('ads');
        Schema::dropIfExists('home_categories');
        Schema::dropIfExists('home_sections');
        Schema::dropIfExists('nav_menus');
    }
};
