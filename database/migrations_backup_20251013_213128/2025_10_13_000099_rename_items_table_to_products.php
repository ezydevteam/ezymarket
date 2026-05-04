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
        // Table is already created as 'products'
        // Schema::rename('items', 'products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Table is created as 'products' in up migration
        // Schema::rename('products', 'items');
    }
};
