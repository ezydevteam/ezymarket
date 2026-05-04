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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1000);
            $table->bigInteger('seller_id')->unsigned();
            $table->string('name', 100)->unique();
            $table->string('slug')->unique();
            $table->longText('description');
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('sub_category_id')->unsigned()->nullable();
            $table->longText('options')->nullable();
            $table->string('version')->nullable();
            $table->text('demo_link')->nullable();
            $table->longText('tags');
            $table->string('thumbnail')->nullable();
            $table->string('preview_type')->default('image');
            $table->string('preview_image')->nullable();
            $table->string('preview_video')->nullable();
            $table->string('preview_audio')->nullable();
            $table->string('main_file');
            $table->boolean('is_main_file_external')->default(false);
            $table->longText('screenshots')->nullable();
            $table->double('regular_price');
            $table->double('extended_price')->nullable();
            $table->string('regular_price_label')->nullable();
            $table->string('extended_price_label')->nullable();
            $table->boolean('is_supported')->default(0);
            $table->text('support_instructions')->nullable();
            $table->json('regular_extra_features')->nullable();
            $table->json('extended_extra_features')->nullable();
            $table->boolean('has_custom_services')->default(false);
            $table->text('custom_services')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1:Pending 2:Soft Rejected 3:Resubmitted 4:Approved 5:Hard Rejected 6:Restricted 7:Deleted');
            $table->integer('previous_status')->nullable();
            $table->timestamp('restricted_at')->nullable();
            $table->text('restriction_reason')->nullable();
            $table->bigInteger('total_sales')->default(0)->unsigned();
            $table->double('total_sales_amount')->default(0);
            $table->double('total_earnings')->default(0);
            $table->bigInteger('total_reviews')->default(0)->unsigned();
            $table->bigInteger('avg_reviews')->default(0)->unsigned();
            $table->bigInteger('total_comments')->default(0)->unsigned();
            $table->bigInteger('total_views')->default(0)->unsigned();
            $table->integer('total_reports')->default(0)->unsigned();
            $table->bigInteger('current_month_views')->default(0)->unsigned();
            $table->bigInteger('free_downloads')->default(0)->unsigned();
            $table->boolean('purchasing_status')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_best_selling')->default(false);
            $table->boolean('is_on_discount')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('was_featured')->default(false);
            $table->dateTime('last_update_at')->nullable();
            $table->dateTime('last_discount_at')->nullable();
            $table->dateTime('price_updated_at')->nullable();
            $table->foreign("seller_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("category_id")->references("id")->on('categories')->onDelete('cascade');
            $table->foreign("sub_category_id")->references("id")->on('sub_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
