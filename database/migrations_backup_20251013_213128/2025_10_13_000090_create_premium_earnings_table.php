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
        Schema::create('premium_earnings', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->bigInteger('seller_id')->unsigned();
            $table->bigInteger('premium_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('percentage');
            $table->double('price');
            $table->double('seller_earning');
            $table->foreign("seller_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("premium_id")->references("id")->on('subscriptions')->onDelete('set null');
            $table->foreign("product_id")->references("id")->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_earnings');
    }
};
