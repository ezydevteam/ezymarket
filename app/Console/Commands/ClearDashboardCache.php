<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cache\CacheManager;

class ClearDashboardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear dashboard caches to refresh analytics and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing dashboard caches...');

        // Create cache manager instance for dashboard
        $cache = CacheManager::scope('dashboard_');

        // List of cache keys to clear
        $cacheKeys = [
            'stats_today',
            'stats_last_7_days',
            'stats_last_28_days',
            'stats_this_month',
            'stats_this_year',
            'stats_lifetime',
            'best_seller_month',
            'top_selling_products',
            'top_rated_products',
            'upcoming_birthdays',
            'admin_logins',
        ];

        // Clear each cache key
        foreach ($cacheKeys as $key) {
            $cache->forget($key);
            $this->line("✓ Cleared: {$key}");
        }

        // Clear user analytics caches
        $this->clearAnalyticsCaches('user_analytics', ['week', 'month', 'year']);

        // Clear sales analytics caches
        $this->clearAnalyticsCaches('sales_analytics', ['week', 'month', 'year']);

        $this->newLine();
        $this->info('Dashboard caches cleared successfully!');
        $this->comment('Next dashboard load will regenerate fresh data.');

        return 0;
    }

    /**
     * Clear analytics caches for different types and offsets
     */
    private function clearAnalyticsCaches(string $prefix, array $types): void
    {
        $cache = CacheManager::scope('dashboard_');
        $offsets = range(-5, 5);

        foreach ($types as $type) {
            foreach ($offsets as $offset) {
                $cache->forget("{$prefix}_{$type}_{$offset}");
            }
            $this->line("✓ Cleared: {$prefix} ({$type})");
        }
    }
}
