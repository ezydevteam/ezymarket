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
        // 1. Add options column to product_categories
        Schema::table('product_categories', function (Blueprint $table) {
            $table->json('options')->nullable()->after('description');
        });

        // 2. Migrate data from product_category_options to product_categories
        $categories = DB::table('product_categories')->get();
        foreach ($categories as $category) {
            $options = DB::table('product_category_options')
                ->where('category_id', $category->id)
                ->get()
                ->map(function ($option) {
                    return [
                        'id' => (string) $option->id,
                        'type' => (int) $option->type,
                        'name' => $option->name,
                        'options' => json_decode($option->options, true),
                        'is_required' => (bool) $option->is_required,
                    ];
                })
                ->toArray();

            if (!empty($options)) {
                DB::table('product_categories')
                    ->where('id', $category->id)
                    ->update(['options' => json_encode($options)]);
            }
        }

        // 3. Drop product_category_options table
        Schema::dropIfExists('product_category_options');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_category_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->integer('type');
            $table->string('name');
            $table->json('options');
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        $categories = DB::table('product_categories')->get();
        foreach ($categories as $category) {
            $optionsData = $category->options ? json_decode($category->options, true) : [];
            foreach ($optionsData as $option) {
                DB::table('product_category_options')->insert([
                    'category_id' => $category->id,
                    'type' => $option['type'],
                    'name' => $option['name'],
                    'options' => json_encode($option['options']),
                    'is_required' => $option['is_required'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
