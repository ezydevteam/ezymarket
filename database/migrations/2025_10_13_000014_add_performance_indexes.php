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
        // Additional indexes for performance optimization

        // Users - Add index for frequently filtered columns
        Schema::table('users', function (Blueprint $table) {
            $table->index('last_seen');
            $table->index('id_verification_status');
            $table->index('created_at');
        });

        // Products - Add compound indexes for common queries
        Schema::table('products', function (Blueprint $table) {
            $table->index(['created_at', 'status']);
            $table->index(['total_sales', 'status']);
            $table->index(['avg_reviews', 'status']);
            $table->index(['free_product', 'status']);
        });

        // Transactions - Add index for date range queries
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });

        // Sales - Add index for date range and earnings reports
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['seller_id', 'created_at', 'status']);
        });

        // Product Reviews - Add index for rating queries
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'stars', 'status']);
            $table->index('created_at');
        });

        // Product Comments - Add index for sorting
        Schema::table('product_comments', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Tickets - Add index for date range queries
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Subscriptions - Add index for expiry checks
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['expiry_at', 'status']);
        });

        // Purchases - Add index for support expiry checks
        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['support_expiry_at', 'support_status']);
        });

        // Blog Articles - Add index for date sorting
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->index(['created_at', 'status']);
        });

        // Withdrawals - Add index for date range queries
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop additional indexes
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['support_expiry_at', 'support_status']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['expiry_at', 'status']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('product_comments', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['product_id', 'stars', 'status']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['seller_id', 'created_at', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['free_product', 'status']);
            $table->dropIndex(['avg_reviews', 'status']);
            $table->dropIndex(['total_sales', 'status']);
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['id_verification_status']);
            $table->dropIndex(['last_seen']);
        });
    }
};
