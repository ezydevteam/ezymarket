<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the widget record
        DB::table('widgets')
            ->where('slug', 'product-author-card')
            ->update([
                'slug' => 'product-seller-card',
                'title' => 'Product Seller Card',
                'class' => 'App\\Widgets\\Types\\ProductSellerCardWidget',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the widget record
        DB::table('widgets')
            ->where('slug', 'product-seller-card')
            ->update([
                'slug' => 'product-author-card',
                'title' => 'Product Author Card',
                'class' => 'App\\Widgets\\Types\\ProductAuthorCardWidget',
            ]);
    }
};
