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
        // Admins Table
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1000);
            $table->string('firstname');
            $table->string('lastname');
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('role', 20)->default('manager')->comment('super_admin or manager');
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->tinyInteger('google2fa_status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('google2fa_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['username', 'email', 'role']);
        });

        // Admin Password Resets
        Schema::create('admin_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Admin Notifications
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('link');
            $table->tinyInteger('status')->default(0)->comment('0: Unread, 1: Read');
            $table->timestamp('created_at');

            $table->index(['status', 'created_at']);
        });

        // Editors Table
        Schema::create('editors', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1000);
            $table->string('firstname');
            $table->string('lastname');
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->tinyInteger('google2fa_status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('google2fa_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['username', 'email']);
        });

        // Editor Password Resets
        Schema::create('editor_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Editor Images (for content editor)
        Schema::create('editor_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('editor_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->timestamps();

            $table->index('editor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editor_images');
        Schema::dropIfExists('editor_password_resets');
        Schema::dropIfExists('editors');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('admin_password_resets');
        Schema::dropIfExists('admins');
    }
};
