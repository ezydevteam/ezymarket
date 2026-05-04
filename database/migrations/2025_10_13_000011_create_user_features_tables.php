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
        // Badges Table
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('image');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // User Badges Table
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at');

            $table->unique(['user_id', 'badge_id']);
            $table->index('user_id');
        });

        // Levels Table
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 10);
            $table->integer('minimum_sales')->default(0);
            $table->integer('maximum_sales')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['status', 'minimum_sales', 'maximum_sales']);
        });

        // ID Verifications Table (formerly KYC)
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('documents')->comment('JSON: Document file paths');
            $table->text('admin_reply')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Verified, 2: Rejected');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Feedback Table
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->tinyInteger('status')->default(0)->comment('0: Unread, 1: Read');
            $table->timestamps();

            $table->index(['email', 'status']);
        });

        // User Notifications Table
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('link');
            $table->tinyInteger('status')->default(0)->comment('0: Unread, 1: Read');
            $table->timestamp('created_at');

            $table->index(['user_id', 'status', 'created_at']);
        });

        // Notification Preferences Table
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->tinyInteger('new_sale')->default(1);
            $table->tinyInteger('new_comment')->default(1);
            $table->tinyInteger('new_review')->default(1);
            $table->tinyInteger('product_update')->default(1);
            $table->tinyInteger('product_comment')->default(1);
            $table->tinyInteger('product_review')->default(1);
            $table->tinyInteger('product_discount')->default(1);
            $table->tinyInteger('referral_earning')->default(1);
            $table->tinyInteger('withdrawal_status')->default(1);
            $table->tinyInteger('subscription_expiry')->default(1);
            $table->tinyInteger('support_expiry')->default(1);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('id_verifications');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
