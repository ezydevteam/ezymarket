<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // email, push, in_app
            $table->string('event'); // order_placed, payment_received, etc.
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'type', 'event']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_preferences');
    }
};



















