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
        // Ticket Categories Table
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Tickets Table
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->unique();
            $table->string('subject');
            $table->longText('message');
            $table->tinyInteger('priority')->default(1)->comment('1: Low, 2: Medium, 3: High');
            $table->tinyInteger('status')->default(0)->comment('0: Opened, 1: Answered, 2: Closed');
            $table->timestamps();

            $table->index(['user_id', 'ticket_category_id', 'status']);
            $table->index(['number', 'priority']);
        });

        // Ticket Replies Table
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('cascade');
            $table->longText('body');
            $table->timestamps();

            $table->index(['ticket_id', 'user_id', 'admin_id']);
        });

        // Ticket Reply Attachments Table
        Schema::create('ticket_reply_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_reply_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->timestamps();

            $table->index('ticket_reply_id');
        });

        // Support Periods Table
        Schema::create('support_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->string('amount');
            $table->dateTime('expiry_at');
            $table->timestamps();

            $table->index(['seller_id', 'purchase_id']);
        });

        // Support Earnings Table
        Schema::create('support_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->string('price');
            $table->string('seller_amount');
            $table->string('tax')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Paid, 2: Cancelled');
            $table->timestamps();

            $table->index(['seller_id', 'purchase_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_earnings');
        Schema::dropIfExists('support_periods');
        Schema::dropIfExists('ticket_reply_attachments');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_categories');
    }
};
