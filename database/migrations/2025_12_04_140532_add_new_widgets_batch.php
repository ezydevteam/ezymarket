<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $widgets = [
            [
                'title' => 'Product Categories',
                'slug' => 'product-categories',
                'class' => 'App\\Widgets\\Types\\ProductCategoriesWidget',
                'description' => 'Display product categories list',
                'icon' => 'bi bi-grid',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Blog Categories',
                'slug' => 'blog-categories',
                'class' => 'App\\Widgets\\Types\\BlogCategoriesWidget',
                'description' => 'Display blog categories list',
                'icon' => 'bi bi-bookmark',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Recent Blog Posts',
                'slug' => 'recent-blog-posts',
                'class' => 'App\\Widgets\\Types\\RecentBlogPostsWidget',
                'description' => 'Display recent blog articles',
                'icon' => 'bi bi-newspaper',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Popular Blog Posts',
                'slug' => 'popular-blog-posts',
                'class' => 'App\\Widgets\\Types\\PopularBlogPostsWidget',
                'description' => 'Display popular blog articles by views',
                'icon' => 'bi bi-graph-up',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Social Followers',
                'slug' => 'social-followers',
                'class' => 'App\\Widgets\\Types\\SocialFollowersWidget',
                'description' => 'Display social media links with follower counts',
                'icon' => 'bi bi-people',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('widgets')->insert($widgets);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('widgets')->whereIn('slug', [
            'product-categories',
            'blog-categories',
            'recent-blog-posts',
            'popular-blog-posts',
            'social-followers',
        ])->delete();
    }
};
