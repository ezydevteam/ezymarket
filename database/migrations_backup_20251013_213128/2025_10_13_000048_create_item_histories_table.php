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
        Schema::create('item_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('seller_id')->unsigned()->nullable();
            $table->bigInteger('editor_id')->unsigned()->nullable();
            $table->bigInteger('admin_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->foreign("seller_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("editor_id")->references("id")->on('editors')->onDelete('cascade');
            $table->foreign("admin_id")->references("id")->on('admins')->onDelete('cascade');
            $table->foreign("product_id")->references("id")->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_histories');
    }
};



















