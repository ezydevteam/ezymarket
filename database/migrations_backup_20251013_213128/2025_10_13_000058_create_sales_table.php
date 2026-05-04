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
        Schema::create('sales', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->bigInteger('seller_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->tinyInteger('license_type')->comment('1:Regular 2:Extended');
            $table->double('price');
            $table->double('buyer_fee')->default(0);
            $table->text('buyer_tax')->nullable();
            $table->double('seller_fee')->default(0);
            $table->text('seller_tax')->nullable();
            $table->double('seller_earning')->default(0);
            $table->string('country', 10)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1:Active 2:Refunded 3:Cancelled');
            $table->foreign("seller_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("user_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("product_id")->references("id")->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};



















