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
        // Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->index('key');
        });

        // Extensions Table
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('alias')->unique();
            $table->string('logo');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('credentials')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Themes Table
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('alias')->unique();
            $table->string('thumbnail');
            $table->string('version');
            $table->tinyInteger('status')->default(0)->comment('0: Not Active, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Addons Table
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('alias')->unique();
            $table->string('version');
            $table->string('thumbnail');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Payment Gateways Table
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('logo');
            $table->tinyInteger('mode')->default(1)->comment('0: Test, 1: Live');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('fees')->nullable();
            $table->text('credentials')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Storage Providers Table
        Schema::create('storage_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('logo');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('credentials')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Captcha Providers Table
        Schema::create('captcha_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('logo');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->text('credentials')->nullable();
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Mail Templates Table
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('alias')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->text('shortcodes')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['alias', 'status']);
        });

        // Translates Table
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['code', 'key']);
        });

        // Currencies Table
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('symbol');
            $table->decimal('rate', 12, 8)->default(1);
            $table->tinyInteger('position')->default(0)->comment('0: Left, 1: Right');
            $table->tinyInteger('status')->default(0)->comment('0: Disabled, 1: Active');
            $table->timestamps();

            $table->index(['code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('translates');
        Schema::dropIfExists('mail_templates');
        Schema::dropIfExists('captcha_providers');
        Schema::dropIfExists('storage_providers');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('extensions');
        Schema::dropIfExists('settings');
    }
};
