<?php

namespace Codebay\Installer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Codebay\Installer\App\Http\Middleware\InstallerMiddleware;
use Codebay\Installer\App\Http\Middleware\NotInstalledMiddleware;
use Codebay\Installer\App\Helpers\Language;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        
        require_once __DIR__ . '/app/Helpers/functions.php';

        // Register the translate_text helper function globally
        if (!function_exists('translate_text')) {
            function translate_text($text) {
                return Language::translate($text);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('installed', InstallerMiddleware::class);
        $router->aliasMiddleware('notInstalled', NotInstalledMiddleware::class);

        $this->loadRoutesFrom(__DIR__ . '/Routes.php');

        // Register views location
        $viewPath = __DIR__ . '/resources/views';
        $this->loadViewsFrom($viewPath, 'installer');

        // Make sure old views are not being loaded
        if ($this->app['view']->exists('installer::layouts.app')) {
            $this->app['view']->replaceNamespace('installer', $viewPath);
        }

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/installer'),
        ], 'installer-views');
    }
}


















