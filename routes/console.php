<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file defines Closure-based Artisan commands and scheduled tasks
| for the EasyMarket application.
|
| For complex commands, create dedicated classes in app/Console/Commands/
|
| Cron Setup:
| Add to crontab: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
|
| Documentation: https://laravel.com/docs/11.x/artisan#closure-commands
|
*/

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Tasks are organized by frequency and purpose:
| - Premium membership management
| - Product analytics (views, trending, best-sellers)
| - Transaction cleanup (unpaid, expired)
| - Discount lifecycle (start/end times)
| - User badges and seller levels
| - File cleanup (chunks, expired uploads)
| - SEO (sitemap generation)
| - Queue processing
|
*/

// Only run scheduled tasks if installation is complete
if (config('system.install.complete')) {
    // Premium Membership Management
    if (get_license_type(2) && @settings('premium')->status) {
        Schedule::command('premiums:notify-expired')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        Schedule::command('premiums:notify-expiring')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        Schedule::command('premiums:reset-downloads')
            ->daily()
            ->at('00:00');
    }
}

// Queue Processing - Process jobs from queue
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();

// Product Analytics - Monthly view counter reset
Schedule::command('products:reset-monthly-views')
    ->monthlyOn(1, '00:00');

// Product Rankings - Refresh trending and best-selling lists
Schedule::command('products:update-trending')
    ->daily()
    ->at('01:00');

Schedule::command('products:update-best-selling')
    ->daily()
    ->at('01:30');

// Transaction Cleanup - Delete unpaid/abandoned transactions
Schedule::command('transactions:delete-unpaid')
    ->hourly();

// Discount Management - Start and end scheduled discounts
Schedule::command('discounts:activate-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('discounts:deactivate-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// User Engagement - Update badges and seller levels
Schedule::command('badges:assign-membership')
    ->daily()
    ->at('02:00');

Schedule::command('sellers:assign-levels')
    ->daily()
    ->at('02:30');

// File Cleanup - Remove temporary and expired files
Schedule::command('files:delete-chunks')
    ->hourly();

Schedule::command('files:delete-expired')
    ->hourly();

// SEO - Generate XML sitemap for search engines
Schedule::command('sitemap:generate')
    ->daily()
    ->at('03:00');

// Security - Update disposable email domain list
Schedule::command('disposable:update')
    ->weekly()
    ->sundays()
    ->at('04:00');

// Product Reports - Process expired restrictions and reporter suspensions
Schedule::command('reports:process-expirations')
    ->daily()
    ->at('00:30');

// Admin Notes - Cleanup old notes
Schedule::command('notes:cleanup')
    ->daily()
    ->at('04:00');

// Authentication - Cleanup stale unverified accounts
Schedule::command('auth:clear-unverified')
    ->daily()
    ->at('04:30');

/*
|--------------------------------------------------------------------------
| Closure Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
