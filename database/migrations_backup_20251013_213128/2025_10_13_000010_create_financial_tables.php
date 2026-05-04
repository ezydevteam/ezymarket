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
        // Seller Taxes Table
        Schema::create('seller_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country', 3);
            $table->decimal('rate', 5, 2)->comment('Percentage');
            $table->tinyInteger('status')->default(1)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['country', 'status']);
        });

        // Buyer Taxes Table
        Schema::create('buyer_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country', 3);
            $table->decimal('rate', 5, 2)->comment('Percentage');
            $table->tinyInteger('status')->default(1)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['country', 'status']);
        });

        // Withdrawal Methods Table
        Schema::create('withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('minimum');
            $table->string('maximum');
            $table->decimal('fees', 5, 2)->default(0)->comment('Percentage');
            $table->tinyInteger('auto_payout')->default(0)->comment('0: Manual, 1: Automatic');
            $table->text('instructions')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['status', 'auto_payout']);
        });

        // Withdrawals Table
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('withdrawal_method_id')->constrained()->onDelete('cascade');
            $table->string('amount');
            $table->decimal('fees', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('account')->comment('JSON: User account details');
            $table->text('admin_note')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Approved, 2: Returned, 3: Cancelled');
            $table->timestamps();

            $table->index(['user_id', 'withdrawal_method_id', 'status']);
        });

        // Statements Table
        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('amount');
            $table->string('total');
            $table->tinyInteger('type')->comment('1: Credit, 2: Debit');
            $table->timestamp('created_at');

            $table->index(['user_id', 'type', 'created_at']);
        });

        // Referrals Table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Completed');
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
            $table->index(['referrer_id', 'status']);
        });

        // Referral Earnings Table
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->string('amount');
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Paid, 2: Cancelled');
            $table->timestamps();

            $table->index(['referrer_id', 'sale_id', 'status']);
        });

        // Followers Table
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at');

            $table->unique(['follower_id', 'following_id']);
            $table->index('follower_id');
            $table->index('following_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followers');
        Schema::dropIfExists('referral_earnings');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('statements');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('withdrawal_methods');
        Schema::dropIfExists('buyer_taxes');
        Schema::dropIfExists('seller_taxes');
    }
};
