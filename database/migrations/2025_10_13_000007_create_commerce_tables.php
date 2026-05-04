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
        // Transactions Table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('amount');
            $table->string('fees')->nullable();
            $table->string('tax')->nullable();
            $table->string('total');
            $table->string('gateway');
            $table->string('gateway_id')->nullable();
            $table->tinyInteger('type')->comment('1: Deposit, 2: Purchase, 3: Sale, 4: Refund, 5: Withdrawal, 6: Subscription');
            $table->tinyInteger('status')->default(0)->comment('0: Unpaid, 1: Paid, 2: Cancelled');
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['gateway', 'gateway_id']);
        });

        // Transaction Items Table
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->tinyInteger('license_type')->comment('1: Regular, 2: Extended');
            $table->string('amount');
            $table->string('seller_amount')->nullable();
            $table->string('seller_tax')->nullable();
            $table->string('buyer_tax')->nullable();
            $table->string('total');
            $table->timestamps();

            $table->index(['transaction_id', 'product_id']);
        });

        // Purchases Table
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->string('license_type')->comment('Regular License, Extended License');
            $table->string('code')->unique();
            $table->string('download_link');
            $table->integer('download_count')->default(0);
            $table->boolean('is_downloaded')->default(false);
            $table->tinyInteger('support_status')->default(0)->comment('0: No support, 1: Active support, 2: Expired');
            $table->dateTime('support_expiry_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'sale_id']);
            $table->index(['user_id', 'product_id', 'code']);
            $table->index('support_status');
        });

        // Sales Table
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->string('price');
            $table->string('tax')->nullable();
            $table->string('seller_amount')->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('0: Unviewed, 1: Viewed');
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Paid, 2: Cancelled, 3: Refunded');
            $table->timestamps();

            $table->index(['seller_id', 'product_id', 'status']);
            $table->index(['purchase_id', 'is_viewed']);
        });

        // Cart Items Table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('license_type')->comment('1: Regular, 2: Extended');
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index('user_id');
        });

        // Refunds Table
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('reason');
            $table->text('admin_reply')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Accepted, 2: Declined');
            $table->timestamps();

            $table->unique(['purchase_id'])->comment('One refund per purchase');
            $table->index(['user_id', 'sale_id', 'status']);
        });

        // Refund Replies Table
        Schema::create('refund_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('body');
            $table->timestamps();

            $table->index(['refund_id', 'user_id', 'admin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_replies');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
