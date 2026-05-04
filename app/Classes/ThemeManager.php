<?php

namespace App\Classes;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

/**
 * ThemeManager
 *
 * Manages theme operations including configuration, view composers,
 * and theme settings.
 */
class ThemeManager
{
    /**
     * Get the active theme alias
     *
     * @return string|null
     */
    public function getActiveTheme(): ?string
    {
        return Config::get('theme.active');
    }

    /**
     * Set the active theme
     *
     * @param string $theme_alias
     * @return void
     */
    public function setActiveTheme(string $theme_alias): void
    {
        Config::set('theme.active', $theme_alias);
    }

    /**
     * Get the active theme view prefix
     *
     * @return string
     */
    public function getActiveThemeViewPrefix(): string
    {
        return 'themes.' . $this->getActiveTheme();
    }

    /**
     * Get the active theme path prefix
     *
     * @return string
     */
    public function getActiveThemePathPrefix(): string
    {
        return 'themes/' . $this->getActiveTheme();
    }

    /**
     * Register view composers for theme views
     *
     * Automatically prefixes view names with the active theme's view prefix.
     *
     * @param string|array<int, string> $views Single view or array of view names
     * @param callable $callback Composer callback function
     * @return void
     */
    public function composer(string|array $views, callable $callback): void
    {
        if (is_array($views)) {
            foreach ($views as $view) {
                $prefixedView = $this->getActiveThemeViewPrefix() . '.' . $view;
                View::composer($prefixedView, $callback);
            }
        } else {
            $prefixedView = $this->getActiveThemeViewPrefix() . '.' . $views;
            View::composer($prefixedView, $callback);
        }
    }

    /**
     * Get theme settings
     *
     * @return object
     */
    public function getThemeSettings(): object
    {
        $settingsPath = resource_path("views/themes/{$this->getActiveTheme()}/settings.json");

        if (!File::exists($settingsPath)) {
            return (object) [];
        }

        $themeSettings = json_decode(File::get($settingsPath), true);

        if ($themeSettings === null) {
            return (object) [];
        }

        $themeSettings = collect($themeSettings)->map(function ($group) {
            return collect($group)->pluck('value', 'key');
        });

        return json_decode(json_encode($themeSettings));
    }
}


















