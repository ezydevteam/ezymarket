<?php

use App\Enums\Page\PageHeaderStyle;
use App\Enums\Page\PageLayout;
use App\Classes\ThemeManager;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;


/**
 * Theme Helper Functions
 *
 * Provides global helper functions for theme management, asset handling,
 * and view rendering within the active theme context.
 *
 * Functions:
 * - themeManager() - Get the ThemeManager instance
 * - activeTheme() - Get the active theme name (string)
 * - theme_asset() - Generate asset URL for active theme
 * - theme_assets_with_version() - Asset URL with cache-busting version
 * - theme_public_path() - Get public path for theme files
 * - theme_resource_path() - Get resource path for theme views
 * - theme_view() - Render a view from the active theme
 * - theme_include() - Include a blade file from active theme
 * - theme_view_exists() - Check if a theme view exists
 * - theme_compose() - Register view composers for theme views
 * - themeSettings() - Get theme settings or specific group
 *
 * Blade Directives:
 * - @themeInclude('partials.menu') - Include theme view
 * - @themeInclude('partials.menu', ['data' => 'value']) - Include with data
 * - @themeIncludeIf('partials.optional') - Include if view exists
 * - @themeIncludeWhen($condition, 'partials.menu', ['data']) - Include when condition true
 *
 * @package App\Helpers
 */

/**
 * Get the ThemeManager instance.
 *
 * This is a convenience helper to avoid repeatedly calling app(ThemeManager::class).
 * Use this in other theme helper functions for cleaner code.
 *
 * @return ThemeManager The theme manager instance
 */
function themeManager(): ThemeManager
{
    return app(ThemeManager::class);
}

/**
 * Get the active theme instance.
 *
 * @return string|null The active theme name (e.g., 'main')
 */
function activeTheme(): ?string
{
    return themeManager()->getActiveTheme();
}

/**
 * Generate an asset URL for the active theme.
 *
 * @param string|null $path Path to asset file (e.g., 'css/style.css')
 * @return string Full asset URL
 */
function theme_asset(?string $path = null): string
{
    $path = $path ? '/' . $path : null;
    return asset(themeManager()->getActiveThemePathPrefix() . $path);
}

/**
 * Generate an asset URL with cache-busting version parameter.
 *
 * Appends ?v={version} to the asset URL to force browser cache refresh
 * when the application version changes.
 *
 * @param string|null $path Path to asset file
 * @return string Asset URL with version parameter
 */
function theme_assets_with_version(?string $path = null): string
{
    return theme_asset($path . '?v=' . config('system.product.version'));
}

/**
 * Get the public path for theme files.
 *
 * @param string|null $path Path relative to theme's public directory
 * @return string Full filesystem path
 */
function theme_public_path(?string $path = null): string
{
    $path = $path ? '/' . $path : null;
    return public_path(themeManager()->getActiveThemePathPrefix() . $path);
}

/**
 * Get the resource path for theme views.
 *
 * @param string|null $path Path relative to theme's view directory
 * @return string Full filesystem path to view resources
 */
function theme_resource_path(?string $path = null): string
{
    $path = $path ? '/' . $path : null;
    return resource_path('views/' . themeManager()->getActiveThemePathPrefix() . $path);
}

/**
 * Render a view from the active theme.
 *
 * Automatically prefixes the view name with the active theme's view prefix.
 *
 * @param string|null $view View name (e.g., 'home.index')
 * @param array<string, mixed> $data Data to pass to the view
 * @param array<string, mixed> $mergeData Additional data to merge
 * @return View The rendered view instance
 */
function theme_view(?string $view = null, array $data = [], array $mergeData = []): View
{
    $view = themeManager()->getActiveThemeViewPrefix() . '.' . $view;
    return ViewFacade::make($view, $data, $mergeData);
}

/**
 * Check if a theme view exists.
 *
 * Useful for conditional includes or checking view availability.
 *
 * @param string $view View path relative to theme
 * @return bool True if view exists, false otherwise
 */
function theme_view_exists(string $view): bool
{
    $prefixedView = themeManager()->getActiveThemeViewPrefix() . '.' . $view;
    return ViewFacade::exists($prefixedView);
}

/**
 * Include a blade file from the active theme.
 *
 * This is a convenience helper for @include() directive that automatically
 * prefixes the view path with the active theme's view prefix.
 *
 * Usage in blade files:
 * @themeInclude('partials.menus.top-menu')
 * @themeInclude('partials.header', ['title' => 'My Page'])
 * @themeIncludeIf('partials.optional-section')
 * @themeIncludeWhen($showMenu, 'partials.menu', ['items' => $items])
 *
 * Or using the function directly:
 * {!! theme_include('partials.menus.top-menu') !!}
 * {!! theme_include('partials.header', ['title' => 'My Page']) !!}
 *
 * @param string $view View path relative to theme (e.g., 'partials.menus.top-menu')
 * @param array<string, mixed> $data Data to pass to the included view
 * @return string Rendered HTML content
 */
function theme_include(string $view, array $data = [], bool $mergeData = true): string
{
    $prefixedView = themeManager()->getActiveThemeViewPrefix() . '.' . $view;

    if (!ViewFacade::exists($prefixedView)) {
        return "<!-- Theme view not found: {$prefixedView} -->";
    }

    // If mergeData is true, merge with shared data from the view factory
    if ($mergeData) {
        $sharedData = ViewFacade::getShared();
        $data = array_merge($sharedData, $data);
    }

    return ViewFacade::make($prefixedView, $data)->render();
}

/**
 * Include a theme view with automatic variable inheritance.
 *
 * This function merges parent scope variables with any explicitly passed data.
 * Explicitly passed data takes precedence over inherited variables.
 *
 * @param string $view The view name (without theme prefix)
 * @param array $arg2 Either parent vars (if 2 args) or explicit data (if 3 args)
 * @param array $arg3 Parent vars (if 3 args) or empty (default)
 * @return string Rendered HTML content
 */
function theme_include_with_vars(string $view, array $arg2 = [], array $arg3 = []): string
{
    // Handle variable arguments based on Blade directive generation
    // If 3 args: theme_include_with_vars('view', ['data'], parentVars) -> arg2=explicit, arg3=parent
    // If 2 args: theme_include_with_vars('view', parentVars) -> arg2=parent, arg3=[]

    if (func_num_args() >= 3) {
        $explicitData = $arg2;
        $parentVars = $arg3;
    } else {
        $parentVars = $arg2;
        $explicitData = [];
    }

    // Merge: parent vars < explicit data (explicit takes precedence)
    $data = array_merge($parentVars, $explicitData);

    return theme_include($view, $data, true);
}

/**
 * Register view composers for theme views.
 *
 * Automatically prefixes view names with the active theme's view prefix.
 * This function now delegates to the ThemeView facade for consistency.
 *
 * @param string|array<int, string> $views Single view or array of view names
 * @param callable $callback Composer callback function
 * @return void
 */
function theme_compose(string|array $views, callable $callback): void
{
    themeManager()->composer($views, $callback);
}

/**
 * Get theme settings or a specific settings group.
 *
 * Returns theme configuration settings from the theme's settings file.
 * If a group is specified, returns only that group's settings.
 *
 * @param string|null $group Optional settings group name
 * @return object Theme settings object or empty object on error
 */
function themeSettings(?string $group = null): object
{
    try {
        $themeSettings = themeManager()->getThemeSettings();

        if ($group) {
            return $themeSettings->$group ?? (object) [];
        }

        return $themeSettings ?? (object) [];
    } catch (\Throwable $e) {
        return (object) [];
    }
}

/**
 * Get theme settings for the current layout context automatically.
 *
 * This provides a safe way to get the settings and layout metadata
 * (index, category, search, etc.) without manually passing a context.
 *
 * @return object Current layout settings group with injected metadata
 */
function themePageSettings(?Page $page = null): object
{
    $routeName = request()->route()?->getName() ?? '';
    $commonPage = in_array($routeName, [
        'products.review',
        'products.comment',
        'cart.index',
        'favorites.index',
        'contact.index'
    ]);

    // Determine settings group context
    $context = match (true) {
        $routeName === 'blog.article' => 'blog_article_page', //keep it top of str_starts_with blog.
        str_starts_with($routeName, 'blog.') => 'blog_index_page',
        str_starts_with($routeName, 'categories.') => 'product_category_page',
        str_starts_with($routeName, 'checkout.') => 'checkout_page',
        $routeName === 'products.search' => 'search_page',
        $routeName === 'products.index' => 'product_index_page',
        $routeName === 'products.show' => 'single_product_page',
        $commonPage => 'no_sidebar_page',
        default => 'general_page',
    };

    // Determine sidebar type
    $sidebarType = match (true) {
        str_starts_with($routeName, 'blog.') => 'blog-sidebar',
        str_starts_with($routeName, 'categories.') => 'product-category-sidebar',
        $routeName === 'products.search' => 'product-page-sidebar',
        $routeName === 'products.index' => 'product-page-sidebar',
        $routeName === 'products.show' => 'single-product-sidebar',
        default => 'page-sidebar',
    };

    $settings = themeSettings($context);

    // Map blog-specific settings to common keys if in blog context
    if (str_starts_with($context, 'blog_')) {
        $blogPrefix = $routeName === 'blog.article' ? 'blog_article_' : 'blog_index_';
        $settings->container_width = $settings->{$blogPrefix . 'container_width'} ?? 'default';
        $settings->sidebar_layout = $settings->{$blogPrefix . 'sidebar_layout'} ?? 'right_sidebar';
        $settings->header_style = $settings->{$blogPrefix . 'header_style'} ?? 'minimal';
        $settings->show_breadcrumbs = $settings->{$blogPrefix . 'show_breadcrumbs'} ?? true;
        $settings->show_description = $settings->{$blogPrefix . 'show_description'} ?? true;
    }

    // Individual Page Model Overrides
    $page = $page ?? ViewFacade::getShared()['page'] ?? null;
    $isPageModel = $page instanceof Page;

    if ($isPageModel) {
        $pageAttributes = $page->getAttributes();

        // Header Style Override - Only if 'header' attribute is explicitly set in DB
        // Check for 'style' presence in the casted array to ensure it's not just the default
        if (isset($pageAttributes['header'])) {
             $settings->header_style = match($page->getHeaderStyle()) {
                PageHeaderStyle::STYLE_1 => 'split',
                PageHeaderStyle::STYLE_2 => 'centered',
                PageHeaderStyle::STYLE_3 => 'minimal',
                PageHeaderStyle::STYLE_4 => 'gradient',
                PageHeaderStyle::NO_HEADER => 'no_header',
                default => $settings->header_style ?? 'minimal',
            };

            // These are part of the header configuration
            $settings->show_breadcrumbs = $page->showBreadcrumb();
            $settings->show_description = $page->showDescription();
        }

        // Layout Overrides - Only if 'layout' attribute is explicitly set in DB AND not DEFAULT
        if (isset($pageAttributes['layout']) && $page->getLayout() !== PageLayout::DEFAULT) {
            $settings->sidebar_layout = $page->isSidebarLayout() ? 'right_sidebar' : 'no_sidebar';

            // Container Width Override
            $settings->container_width = match(true) {
                $page->isFullLayout() => 'fluid',
                $page->isBoxedLayout() => 'boxed',
                default => $settings->container_width ?? 'default',
            };
        }
    }

    // Resolve Container Class
    $containerWidth = $settings->container_width ?? 'default';
    $containerClass = match ($containerWidth) {
        'boxed' => 'container container-boxed',
        'fluid' => 'container-fluid',
        default => 'container container-default',
    };

    // Resolve Header Classes
    $headerStyle = $settings->header_style ?? 'minimal';
    $headerClasses = 'page-header header-' . str_replace('_', '-', $headerStyle);

    // Inject metadata for use in templates
    $settings->context = $context;
    $settings->sidebar_type = $sidebarType;
    $settings->container_class = $containerClass;
    $settings->header_classes = $headerClasses;

    return $settings;
}
