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
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('guard')->default('web'); // 'web', 'admin', etc.
            $table->string('identifier')->nullable(); // username or email attempted
            $table->string('ip_address', 45);
            $table->string('country', 2)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('location')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('os')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_brand')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at');

            $table->index(['guard', 'attempted_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};
