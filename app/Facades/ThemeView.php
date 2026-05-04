<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * ThemeView Facade
 *
 * Provides static access to theme view operations including view composers.
 *
 * @method static void composer(string|array $views, callable $callback)
 * @method static string getViewPrefix()
 * @method static string getPathPrefix()
 * @method static object getActiveTheme()
 *
 * @see \App\Classes\ThemeManager
 */
class ThemeView extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'App\Classes\ThemeManager';
    }
}
