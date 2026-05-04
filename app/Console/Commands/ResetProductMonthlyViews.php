<?php

namespace App\Console\Commands;

use App\Models\Product\Product;
use Illuminate\Console\Command;

class ResetProductMonthlyViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:reset-monthly-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset product monthly views to zero';

    public function handle(): int
    {
        $count = Product::approved()
            ->where('current_month_views', '>', 0)
            ->update(['current_month_views' => 0]);

        if ($count === 0) {
            $this->info('No products require monthly views reset');
        } else {
            $this->info("Successfully reset monthly views for {$count} product(s)");
        }

        return self::SUCCESS;
    }
}
