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
        // Plans Table
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('short_description');
            $table->longText('advertisement')->nullable();
            $table->string('price');
            $table->integer('product_limit')->comment('-1: Unlimited');
            $table->tinyInteger('featured_duration')->default(0)->comment('Days, 0: Disabled');
            $table->tinyInteger('best_selling_duration')->default(0)->comment('Days, 0: Disabled');
            $table->tinyInteger('trend_duration')->default(0)->comment('Days, 0: Disabled');
            $table->tinyInteger('discount')->default(0)->comment('0: Disabled, 1: Enabled');
            $table->tinyInteger('product_updates')->default(0)->comment('0: Disabled, 1: Enabled');
            $table->tinyInteger('storage_space')->nullable()->comment('GB, null: Unlimited');
            $table->tinyInteger('support_duration')->nullable()->comment('Months, null: Unlimited');
            $table->tinyInteger('is_free')->default(0)->comment('0: Paid, 1: Free');
            $table->tinyInteger('is_featured')->default(0)->comment('0: Normal, 1: Featured');
            $table->integer('sort_id')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0: Hidden, 1: Active');
            $table->timestamps();

            $table->index(['is_free', 'is_featured', 'status', 'sort_id']);
        });

        // Subscriptions Table
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('status')->default(1)->comment('0: Expired, 1: Active, 2: About to Expire');
            $table->dateTime('expiry_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_id', 'transaction_id']);
            $table->index(['user_id', 'status']);
        });

        // Premium Earnings Table
        Schema::create('premium_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('price');
            $table->string('seller_amount');
            $table->string('tax')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Paid, 2: Cancelled');
            $table->timestamps();

            $table->index(['seller_id', 'transaction_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_earnings');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
