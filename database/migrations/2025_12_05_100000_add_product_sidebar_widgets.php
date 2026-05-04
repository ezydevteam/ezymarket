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
        // Add product sidebar widgets
        $widgets = [
            [
                'title' => 'Product Price Card',
                'slug' => 'product-price-card',
                'class' => 'App\\Widgets\\Types\\ProductPriceCardWidget',
                'description' => 'Display product pricing with purchase options',
                'icon' => 'bi bi-credit-card',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product Premium Card',
                'slug' => 'product-premium-card',
                'class' => 'App\\Widgets\\Types\\ProductPremiumCardWidget',
                'description' => 'Display premium subscription download section',
                'icon' => 'bi bi-gem',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product Free Card',
                'slug' => 'product-free-card',
                'class' => 'App\\Widgets\\Types\\ProductFreeCardWidget',
                'description' => 'Display free product download section',
                'icon' => 'bi bi-gift',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product Author Card',
                'slug' => 'product-author-card',
                'class' => 'App\\Widgets\\Types\\ProductAuthorCardWidget',
                'description' => 'Display product author/seller information',
                'icon' => 'bi bi-person-badge',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product Details Card',
                'slug' => 'product-details-card',
                'class' => 'App\\Widgets\\Types\\ProductDetailsCardWidget',
                'description' => 'Display product details like version, category, last update',
                'icon' => 'bi bi-info-circle',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product Meta Card',
                'slug' => 'product-meta-card',
                'class' => 'App\\Widgets\\Types\\ProductMetaCardWidget',
                'description' => 'Display product meta information like tags, compatible browsers',
                'icon' => 'bi bi-tags',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('widgets')->insert($widgets);

        // Get the widget IDs we just inserted
        $widgetIds = DB::table('widgets')
            ->whereIn('slug', [
                'product-price-card',
                'product-premium-card',
                'product-free-card',
                'product-author-card',
                'product-details-card',
                'product-meta-card',
            ])
            ->pluck('id', 'slug');

        // Create default widget instances for single-product-sidebar
        $instances = [
            [
                'widget_id' => $widgetIds['product-price-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode(['show_support_policy_link' => true]),
                'order_id' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => $widgetIds['product-premium-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode([]),
                'order_id' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => $widgetIds['product-free-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode([]),
                'order_id' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => $widgetIds['product-author-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode([
                    'show_avatar' => true,
                    'show_stats' => true,
                    'show_contact_button' => true,
                    'show_follow_button' => true,
                ]),
                'order_id' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => $widgetIds['product-details-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode([
                    'show_last_updated' => true,
                    'show_published_date' => true,
                    'show_version' => true,
                    'show_category' => true,
                    'show_options' => true,
                    'show_tags' => true,
                    'collapsed_by_default' => false,
                ]),
                'order_id' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => $widgetIds['product-meta-card'],
                'area' => 'single-product-sidebar',
                'title' => null,
                'settings' => json_encode([
                    'show_title' => false,
                    'show_file_size' => true,
                    'show_sales' => true,
                    'show_downloads' => true,
                    'show_rating' => true,
                    'show_demo_link' => true,
                    'show_compatible_with' => true,
                    'show_browsers' => true,
                    'show_files_included' => true,
                ]),
                'order_id' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('widget_instances')->insert($instances);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete widget instances first
        DB::table('widget_instances')
            ->whereIn('widget_id', function ($query) {
                $query->select('id')
                    ->from('widgets')
                    ->whereIn('slug', [
                        'product-price-card',
                        'product-premium-card',
                        'product-free-card',
                        'product-author-card',
                        'product-details-card',
                        'product-meta-card',
                    ]);
            })
            ->delete();

        // Delete widgets
        DB::table('widgets')->whereIn('slug', [
            'product-price-card',
            'product-premium-card',
            'product-free-card',
            'product-author-card',
            'product-details-card',
            'product-meta-card',
        ])->delete();
    }
};
