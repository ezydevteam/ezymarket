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
        // Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1000);
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country', 3)->nullable();
            $table->string('password');
            $table->text('about_me')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->tinyInteger('google2fa_status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('google2fa_secret')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('id_verification_status')->default(0)->comment('0: Unverified, 1: Pending, 2: Verified, 3: Rejected');
            $table->tinyInteger('status')->default(1)->comment('0: Banned, 1: Active');
            $table->timestamp('last_seen')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['username', 'email', 'status']);
        });

        // Password Resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // User Login Logs
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip', 64);
            $table->string('country', 3)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('os', 100)->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
        });

        // OAuth Providers
        Schema::create('oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_providers');
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};
