<?php

namespace App\Providers;

use App\Enums\Menu\MenuLocation;
use App\View\Composers\{HeaderComposer, FooterComposer};
use App\Models\{
    Feedback,
    IdVerification,
    Refund,
    User,
    UserBadge
};
use App\Models\Product\{
    Product,
    ProductCategory,
    ProductUpdate,
    ProductReport,
    ProductCommentReport
};
use App\Models\Financial\{Payout, Transaction};
use App\Models\Notification\AdminNotification;
use App\Models\Support\Ticket;
use App\Models\Blog\BlogComment;
use App\Facades\ThemeView;
use App\Observers\{ProductObserver, UserBadgeObserver, UserObserver};
use App\Rules\{BlockPatterns, Username};
use App\Services\{HomePageService, MessageFilterService, NotificationService};
use App\Widgets\WidgetManager;
use App\Widgets\Types\{TextWidget, CustomHtmlWidget, ImageWidget, MenuWidget, RecentProductsWidget};
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{Blade, Config, Event, RateLimiter, Validator, View, Auth};
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 *
 * Main application service provider for registering services, view composers,
 * Blade directives, and application-wide configurations.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerBladeDirectives();
        $this->registerAssetsDirectives();
        $this->registerServices();
    }

    /**
     * Bootstrap application services
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configurePagination();
        $this->registerObservers();
        $this->registerWidgets();
        $this->configureRateLimiting();
        $this->registerEvents();
        $this->registerValidationExtensions();

        if ($this->shouldLoadViewComposers()) {
            $this->configureSslForcing();
            $this->registerGlobalViewData();
            $this->registerViewComposers();
            $this->configureToastrForRtl();
        }
    }

    /**
     * Register application services
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(MessageFilterService::class);
        $this->app->singleton(HomePageService::class);
        $this->app->singleton(WidgetManager::class);
    }

    /**
     * Configure pagination
     *
     * @return void
     */
    protected function configurePagination(): void
    {
        Paginator::useBootstrap();
    }

    /**
     * Register model observers
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        User::observe(UserObserver::class);
        UserBadge::observe(UserBadgeObserver::class);
        Product::observe(ProductObserver::class);
    }

    /**
     * Register default widget types
     *
     * @return void
     */
    protected function registerWidgets(): void
    {
        $this->app->make(WidgetManager::class)->registerMany([
            TextWidget::class,
            CustomHtmlWidget::class,
            ImageWidget::class,
            MenuWidget::class,
            RecentProductsWidget::class,
            \App\Widgets\Types\BlogCategoriesWidget::class,
            \App\Widgets\Types\BlogPostsWidget::class,
        ]);
    }

    /**
     * Check if view composers should be loaded
     *
     * @return bool
     */
    protected function shouldLoadViewComposers(): bool
    {
        return config('system.install.complete', false) && !$this->app->runningUnitTests();
    }

    /**
     * Configure SSL forcing if enabled
     *
     * @return void
     */
    protected function configureSslForcing(): void
    {
        if (@settings('actions')->force_ssl) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Register global view data available to all views
     *
     * @return void
     */
    protected function registerGlobalViewData(): void
    {
        View::composer('*', function ($view): void {
            $view->with([
                'settings' => @settings(),
                'themeSettings' => themeSettings() ?: (object) []
            ]);
        });
    }

    /**
     * Register all view composers
     *
     * @return void
     */
    protected function registerViewComposers(): void
    {
        $this->themeViewComposers();
        $this->adminViewComposers();
    }

    /**
     * Configure toastr position for RTL languages
     *
     * @return void
     */
    protected function configureToastrForRtl(): void
    {
        if (getDirection() === 'rtl') {
            Config::set('toastr.options.positionClass', 'codebay-toast-top-left');
        }
    }

    /**
     * Register theme view composers
     *
     * @return void
     */
    protected function themeViewComposers(): void
    {
        $this->registerGlobalLaravelJsData();
        $this->registerHeaderComposer();
        $this->registerFooterComposer();
        $this->registerUserPanelComposers();
    }

    /**
     * Register global Laravel JS data for all theme views
     *
     * @return void
     */
    protected function registerGlobalLaravelJsData(): void
    {
        ThemeView::composer('*', function ($view): void {
            $view->with('laravelJs', [
                'csrfToken'   => csrf_token(),
                'isLoggedIn'  => Auth::check(),
                /*'isEmailVerifyRoute'  => route('verification.notice'),
                'routes'      => [
                    'checkUsernameAvailability' => route('check.username.availability'),
                    'liveSearch' => route('product.live_search'),
                    'productIndex'  => route('products.search')
                ]*/
            ]);
        });
    }

    /**
     * Register header view composer
     *
     * @return void
     */
    protected function registerHeaderComposer(): void
    {
        ThemeView::composer(['includes.header', 'includes.styles'], function ($view): void {
            (new HeaderComposer())->compose($view);
        });
    }

    /**
     * Register footer view composer
     *
     * @return void
     */
    protected function registerFooterComposer(): void
    {
        ThemeView::composer(['includes.footer', 'includes.styles'], function ($view): void {
            (new FooterComposer())->compose($view);
        });
    }

    /**
     * Register user-panel view composers
     *
     * @return void
     */
    protected function registerUserPanelComposers(): void
    {
        ThemeView::composer('userpanel.includes.navbar', function ($view): void {
            $view->with('categories', ProductCategory::all());
        });

        ThemeView::composer('userpanel.includes.sidebar', function ($view): void {
            $counters['pending_refunds'] = Refund::where('seller_id', authUser()->id)
                ->pending()
                ->count();

            $view->with('counters', $counters);
        });
    }

    /**
     * Register admin-specific view composers.
     */
    protected function adminViewComposers(): void
    {
        View::composer('admin.includes.navbar', function ($view) {
            $view->with('navbarNotifications', $this->getAdminNavbarNotifications());
        });

        View::composer('admin.includes.sidebar', function ($view) {
            $view->with('sidebar_counters', $this->getAdminSidebarCounters());
        });
    }

    /**
     * Get admin navbar notifications.
     */
    protected function getAdminNavbarNotifications(): array
    {
        return [
            'list' => AdminNotification::orderbyDesc('id')->limit(20)->get(),
            'unread' => AdminNotification::unread()->count(),
        ];
    }

    /**
     * Get admin sidebar counters.
     */
    protected function getAdminSidebarCounters(): array
    {
        return [
            'products_pending' => Product::pending()->count(),
            'products_resubmitted' => Product::resubmitted()->count(),
            'products_updated' => ProductUpdate::count(),
            'products_trashed' => Product::onlyTrashed()->count(),
            'users_trashed' => User::onlyTrashed()->count(),
            'payouts' => Payout::pending()->count(),
            'transactions' => Transaction::pending()->count(),
            'id_verifications' => IdVerification::pending()->count(),
            'refunds' => Refund::pending()->count(),
            'tickets' => Ticket::opened()->whereDate('created_at', '>=', now()->subDays(7))->count(),
            'reports.product_comments' => ProductCommentReport::count(),
            'reports.product-reports' => ProductReport::pending()->count(),
            'reports.feedback' => Feedback::pending()->count(),
            'blog_comments' => BlogComment::pending()->count(),
        ];
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiting - 60 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limiting - 5 attempts per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Registration rate limiting - 3 attempts per hour per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Password reset rate limiting - 5 attempts per hour per IP
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        // Global rate limiting - 1000 requests per minute per IP
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });
    }

    /**
     * Register custom validation rules.
     */
    protected function registerValidationExtensions(): void
    {
        // Register username validation rule
        Validator::extend('username', function ($attribute, $value, $parameters, $validator) {
            $rule = new Username();
            $failed = false;

            $rule->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });

            return !$failed;
        });

        // Register block_patterns validation rule
        Validator::extend('block_patterns', function ($attribute, $value, $parameters, $validator) {
            $rule = new BlockPatterns();
            $failed = false;

            $rule->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });

            return !$failed;
        });
    }

    /**
     * Register custom Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('notOnPage', function ($expression) {
            return "<?php
				\$routes = is_array($expression) ? $expression : [$expression];
				if(!is_array(\$routes)) \$routes = [\$routes];
				if(!isOnPage(\$routes)): ?>";
        });
        Blade::directive('endnotOnPage', function () {
            return "<?php endif; ?>";
        });

        // Theme include directive - inherits all local variables like @include
        Blade::directive('themeInclude', function ($expression) {
            // Parse the expression to separate view name and additional data
            return "<?php echo theme_include_with_vars($expression, \\Illuminate\\Support\\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
        });

        // Theme include if exists
        Blade::directive('themeIncludeIf', function ($expression) {
            return "<?php if(theme_view_exists($expression)) { echo theme_include_with_vars($expression, \\Illuminate\\Support\\Arr::except(get_defined_vars(), ['__data', '__path'])); } ?>";
        });

        // Theme include when condition is true
        Blade::directive('themeIncludeWhen', function ($expression) {
            return "<?php
				\$args = is_array($expression) ? $expression : [$expression];
				if(count(\$args) >= 2 && \$args[0]) {
					echo theme_include_with_vars(\$args[1], array_merge(\\Illuminate\\Support\\Arr::except(get_defined_vars(), ['__data', '__path']), \$args[2] ?? []));
				}
			?>";
        });
    }

    /**
     * Register asset-related Blade directives.
     */
    protected function registerAssetsDirectives(): void
    {
        Blade::directive('bootstrap', function () {
            $file = getDirection() == 'rtl' ? 'bootstrap-rtl.min.css' : 'bootstrap.min.css';
            return '<link rel="stylesheet" href="{{ asset("vendor/libs/bootstrap/' . $file . '") }}">' .
                '<link rel="stylesheet" href="{{ asset("vendor/libs/bootstrap/bootstrap-icons.min.css") }}">';
        });

        Blade::directive('themeColors', function () {
            return '<link rel="stylesheet" href="' . theme_assets_with_version(config('theme.style.colors')) . '">';
        });

        Blade::directive('themeCustomStyle', function () {
            return '<link rel="stylesheet" href="' . theme_assets_with_version(config('theme.style.custom_css')) . '">';
        });

        Blade::directive('adminColors', function () {
            return '<link rel="stylesheet" href="' . asset_with_version(config('system.admin.colors')) . '">';
        });

        Blade::directive('adminCustomStyle', function () {
            return '<link rel="stylesheet" href="' . asset_with_version(config('system.admin.custom_css')) . '">';
        });
    }

    /**
     * Register application events.
     */
    protected function registerEvents(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', \SocialiteProviders\Microsoft\Provider::class);
            $event->extendSocialite('envato', \SocialiteProviders\Envato\Provider::class);
        });
    }
}
