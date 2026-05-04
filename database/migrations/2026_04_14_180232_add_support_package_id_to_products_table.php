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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('support_package_id')->nullable()->after('is_supported');
        });

        Schema::table('product_updates', function (Blueprint $table) {
            $table->unsignedBigInteger('support_package_id')->nullable()->after('is_supported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('support_package_id');
        });

        Schema::table('product_updates', function (Blueprint $table) {
            $table->dropColumn('support_package_id');
        });
    }
};
