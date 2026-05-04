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
        Schema::dropIfExists('home_blocks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating the table with base structure in case of rollback
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('alias')->unique();
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_id')->default(0);
            $table->timestamps();
        });
    }
};
