<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel
 *
 * Manages console commands for the EasyMarket application.
 * Scheduled tasks are defined in routes/console.php.
 *
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * Register console commands for the application
     *
     * Loads all custom Artisan commands from the Commands directory
     * and registers console routes.
     *
     * @return void
     */
    protected function commands(): void
    {
        // Load all commands from app/Console/Commands directory
        $this->load(__DIR__ . '/Commands');

        // Load console routes from routes/console.php
        require base_path('routes/console.php');
    }
}
