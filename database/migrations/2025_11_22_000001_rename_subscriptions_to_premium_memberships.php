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
        // Rename subscriptions table to premium_memberships
        Schema::rename('subscriptions', 'premium_memberships');

        // Rename subscription_revenues table to premium_revenues
        if (Schema::hasTable('subscription_revenues')) {
            Schema::rename('subscription_revenues', 'premium_revenues');
        }

        // Rename was_subscribed column in users table
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('was_subscribed', 'was_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert premium_memberships back to subscriptions
        Schema::rename('premium_memberships', 'subscriptions');

        // Revert premium_revenues back to subscription_revenues
        if (Schema::hasTable('premium_revenues')) {
            Schema::rename('premium_revenues', 'subscription_revenues');
        }

        // Revert was_premium column in users table
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('was_premium', 'was_subscribed');
        });
    }
};
