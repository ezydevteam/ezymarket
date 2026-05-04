<?php

namespace App\Facades;

use App\Widgets\WidgetManager;
use Illuminate\Support\Facades\Facade;

/**
 * Widget Facade
 *
 * @method static \App\Widgets\WidgetManager register(string $class)
 * @method static \App\Widgets\WidgetManager registerMany(array $classes)
 * @method static array getRegistered()
 * @method static \App\Contracts\WidgetContract|null get(string $slug)
 * @method static \Illuminate\Support\Collection getAvailable()
 * @method static \Illuminate\Support\Collection getAreas()
 * @method static string area(string $slug, array $wrapperOptions = [])
 * @method static string renderArea(string $slug, array $wrapperOptions = [])
 * @method static string render(int $instanceId)
 * @method static void syncToDatabase()
 *
 * @see \App\Widgets\WidgetManager
 */
class Widget extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return WidgetManager::class;
    }
}
