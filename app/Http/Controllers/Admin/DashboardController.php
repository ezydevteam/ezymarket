<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Admin,
    User,
    Sale,
    Refund,
    ReferralEarning,
    Product\Product,
    Product\ProductReport,
    Premium\Premium,
    Premium\PremiumEarning,
    Support\Ticket,
    Support\SupportEarning,
    Financial\Payout,
    Financial\Transaction
};
use App\Services\Statistics\StatisticsService;
use App\Traits\HandlesValidation;
use App\Cache\CacheManager;
use App\Facades\Notification;
use Jenssegers\Date\Date;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\{Facades\DB, Collection};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\View\View;

/**
 * Dashboard Controller with Performance Optimizations
 *
 * Caching Strategy:
 * - User Analytics: 5 minutes (300s)
 * - Sales Analytics: 5 minutes (300s)
 * - Statistics Data: 5 minutes (300s)
 * - Top Seller: 1 hour (3600s)
 * - Top Products: 15 minutes (900s)
 * - Top Rated Products: 15 minutes (900s)
 * - Upcoming Birthdays: 30 minutes (1800s)
 * - Admin Logins: 3 minutes (180s)
 *
 * To clear all dashboard caches, run:
 * Cache::forget('user_analytics_*');
 * Cache::forget('sales_analytics_*');
 * Cache::forget('dashboard_stats_*');
 * Cache::forget('dashboard_top_seller_month');
 * Cache::forget('dashboard_top_selling_products');
 * Cache::forget('dashboard_top_rated_products');
 * Cache::forget('dashboard_upcoming_birthdays');
 * Cache::forget('dashboard_admin_logins');
 */
class DashboardController extends Controller
{
    use HandlesValidation;

    /**
     * Cache for date range calculations
     * @var array
     */
    private array $dateRangeCache = [];

    /**
     * Cache manager instance
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * Constructor
     * Initialize cache manager with 'dashboard_' scope and 5 minutes default expiration
     */
    public function __construct()
    {
        $this->cache = CacheManager::scope('dashboard_', 5);
    }

    /**
     * Display the admin dashboard
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $statistics = $this->getStatisticsData();

        $users = User::user()->withCount(['transactions' => function ($q) {
            $q->typePurchase()->paid();
        }])->orderbyDesc('id')->limit(7)->get();
        $sellers = User::seller()->withCount(['products' => function ($q) {
            $q->approved()->notDeleted();
        }])->orderbyDesc('id')->limit(7)->get();

        // Get top seller of the month
        $topSeller = $this->getTopSellerOfMonth();

        // Get top sold product of the month
        $topSoldProduct = $this->getTopSoldProductOfMonth();

        // Get users with upcoming birthdays
        $upcomingBirthdays = $this->getUpcomingBirthdays();

        $topSellingProducts = $this->getTopSellingProducts();
        $topRatedProducts = $this->getTopRatedProducts();

        // Get recently joined premium members if premium is available
        $premiumData = $this->getPremiumMembers();
        $premiumMembers = $premiumData['members'];
        $premiumMembersCount = $premiumData['count'];

        // Get traffic source data
        $trafficSources = $this->getTrafficSourceData();

        // Get admin login activities
        $adminLoginActivities = $this->getAdminLoginActivities();

        // Get upcoming admin notes
        $adminNotes = $this->getAdminNotes();

        return view('admin.dashboard', compact(
            'statistics',
            'users',
            'sellers',
            'topSeller',
            'topSoldProduct',
            'upcomingBirthdays',
            'topSellingProducts',
            'topRatedProducts',
            'premiumMembers',
            'premiumMembersCount',
            'trafficSources',
            'adminLoginActivities',
            'adminNotes'
        ));
    }

    /**
     * Get admin all notes
     */
    private function getAdminNotes(): Collection
    {
        return DB::table('admin_notes')
            ->orderBy('date_time', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Store a new admin note
     */
    public function storeNote(): JsonResponse
    {
        $validator = $this->validateRequestJson(request(), [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:250',
            'date_time' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            DB::table('admin_notes')->insert([
                'title' => request('title'),
                'description' => request('description'),
                'date_time' => request('date_time') ?? null,
                'priority' => request('priority') ?? null,
                'created_by' => authAdmin()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->successJson('Note created successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Delete a note
     */
    public function deleteNote(int $id): JsonResponse
    {
        try {
            DB::table('admin_notes')
                ->where('id', $id)
                ->delete();

            return $this->successJson('Note deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get top seller of the month based on sales
     *
     * @return array
     */
    private function getTopSellerOfMonth(): array
    {
        return $this->cache->remember('top_seller_month', function () {
            return $this->calculateTopSellerOfMonth();
        }, 60);
    }

    /**
     * Calculate top seller of the month
     *
     * @return array
     */
    private function calculateTopSellerOfMonth(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $topSeller = Sale::active()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('seller_id', DB::raw('SUM(price) as total_sales'), DB::raw('COUNT(*) as sales_count'))
            ->groupBy('seller_id')
            ->orderByDesc('total_sales')
            ->with(['seller' => function ($query) {
                $query->select('id', 'username', 'firstname', 'lastname', 'avatar');
            }])
            ->first();

        if (!$topSeller || !$topSeller->seller) {
            // Fallback to seller with highest all-time sales if no sales this month
            $topSeller = Sale::active()
                ->select('seller_id', DB::raw('SUM(price) as total_sales'), DB::raw('COUNT(*) as sales_count'))
                ->groupBy('seller_id')
                ->orderByDesc('total_sales')
                ->with(['seller' => function ($query) {
                    $query->select('id', 'username', 'firstname', 'lastname', 'avatar');
                }])
                ->first();
        }

        if (!$topSeller || !$topSeller->seller) {
            // Final fallback if no sales at all
            return [
                'name' => 'N/A',
                'total_sales' => 0,
                'sales_count' => 0,
            ];
        }

        // Check if user has been congratulated this month
        $congratulatedThisMonth = $topSeller->seller->notifications()
            ->where('type', 'App\Notifications\CongratsNotification')
            ->whereJsonContains('data->template', 'top_seller')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->exists();

        return [
            'id' => $topSeller->seller->id,
            'name' => $topSeller->seller->full_name,
            'avatar' => $topSeller->seller->avatar_url,
            'total_sales' => $topSeller->total_sales,
            'sales_count' => $topSeller->sales_count,
            'congratulated' => $congratulatedThisMonth,
        ];
    }

    /**
     * Get users with upcoming birthdays (next 7 days)
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpcomingBirthdays(): Collection
    {
        return $this->cache->remember('upcoming_birthdays', function () {
            $today = Date::now();
            $currentMonth = $today->month;
            $currentDay = $today->day;
            $nextWeek = $today->copy()->addDays(3);
            $nextWeekMonth = $nextWeek->month;
            $nextWeekDay = $nextWeek->day;

            // Build query to filter at database level
            $query = User::whereNotNull('basic_info')
                ->select('id', 'username', 'firstname', 'lastname', 'avatar', 'basic_info');

            // Handle month boundary crossing
            if ($currentMonth === $nextWeekMonth) {
                // Same month - simple range
                $query->whereRaw(
                    "MONTH(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) = ? AND DAY(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) BETWEEN ? AND ?",
                    [$currentMonth, $currentDay, $nextWeekDay]
                );
            } else {
                // Crosses month boundary
                $query->where(function ($q) use ($currentMonth, $currentDay, $nextWeekMonth, $nextWeekDay) {
                    $q->whereRaw(
                        "MONTH(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) = ? AND DAY(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) >= ?",
                        [$currentMonth, $currentDay]
                    )->orWhereRaw(
                        "MONTH(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) = ? AND DAY(JSON_UNQUOTE(JSON_EXTRACT(basic_info, '$.birth_date'))) <= ?",
                        [$nextWeekMonth, $nextWeekDay]
                    );
                });
            }

            $users = $query->limit(10)->get();

            $today = Date::now()->startOfDay();

            // Check if birthday wishes have been sent today and calculate days until birthday
            $users->each(function ($user) use ($today) {
                // Check if wishes already sent
                $user->already_wished_today = $user->notifications()
                    ->where('type', 'App\Notifications\BirthdayWishNotification')
                    ->whereDate('created_at', today())
                    ->exists();

                // Calculate days until birthday
                $birthDate = $user->basic_info['birth_date'] ?? null;
                if ($birthDate) {
                    $birthday = Date::parse($birthDate);
                    $birthdayThisYear = $birthday->copy()->setYear($today->year);

                    // If birthday has passed this year, use next year's date
                    if ($birthdayThisYear->startOfDay()->lt($today)) {
                        $birthdayThisYear->addYear();
                    }

                    $user->days_until = $today->diffInDays($birthdayThisYear->startOfDay());
                } else {
                    $user->days_until = null;
                }
            });

            return $users;
        }, 30);
    }

    /**
     * Send congratulations email
     */
    public function sendCongratsEmail(): JsonResponse
    {
        try {
            $template = request('template');
            $userId = request('user_id');
            $productId = request('product_id');

            $user = User::find($userId);
            if (!$user) {
                return $this->errorJson('User not found', [], 404);
            }

            // Get product if product_id is provided
            $product = null;
            if ($productId) {
                $product = Product::find($productId);
            }

            // Send notification to top seller & top sold product seller
            Notification::sendCongratsNotification($user, $product, $template);

            // Clear cache to update button state
            if ($template === 'top_seller') {
                $this->cache->forget('top_seller_month');
            } elseif ($template === 'top_product') {
                $this->cache->forget('top_selling_products');
            }

            return $this->successJson('Congratulations message sent successfully!');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Send birthday wishes to selected users
     */
    public function sendBirthdayWishes(): JsonResponse
    {
        try {
            $userId = request('user_id');

            if (!$userId) {
                return $this->errorJson('No user selected', [], 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return $this->errorJson('User not found', [], 404);
            }

            // Send bithday wish notification
            Notification::sendBirthdayWishNotification($user);

            // Clear cache to update button state
            $this->cache->forget('upcoming_birthdays');

            return $this->successJson('Birthday wishes sent successfully!');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get premium members data
     *
     * @return array{members: Collection, count: int}
     */
    private function getPremiumMembers(): array
    {
        if (!isPremiumAvailable()) {
            return [
                'members' => collect(),
                'count' => 0,
            ];
        }

        return [
            'members' => Premium::with(['user:id,username,firstname,lastname,avatar', 'plan:id,name'])
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
            'count' => Premium::count(),
        ];
    }

    /**
     * Get statistics data for various counters
     */
    public function getStatisticsData(string $period = 'lifetime'): array
    {
        $stats = app(StatisticsService::class);

        // Get date range for period
        $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
        $startDate = $dateRange['start'] ?? null;
        $endDate = $dateRange['end'] ?? null;

        // Get sales-related counters using StatisticsService
        $salesQuery = $stats->forModel(Sale::class)->scope('active');

        if ($dateRange) {
            $salesQuery->dateRange($startDate, $endDate);
        }

        $salesCounters = $salesQuery->counters([
            'sellers_sales' => ['sum', 'price'],
            'sellers_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
            'buyer_fees' => ['sum', 'buyer_fee'],
            'seller_fees' => ['sum', 'seller_fee'],
            'total_sales' => ['count', '*'],
        ]);

        // Calculate buyer and seller taxes from JSON fields using database aggregation
        $salesTaxQuery = Sale::active();
        if ($dateRange) {
            $salesTaxQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $salesTaxes = $salesTaxQuery
            ->selectRaw('SUM(CAST(JSON_EXTRACT(buyer_tax, "$.amount") AS DECIMAL(10,2))) as buyer_tax_total')
            ->selectRaw('SUM(CAST(JSON_EXTRACT(seller_tax, "$.amount") AS DECIMAL(10,2))) as seller_tax_total')
            ->first();

        $salesCounters['buyer_tax'] = $salesTaxes->buyer_tax_total ?? 0;
        $salesCounters['seller_tax'] = $salesTaxes->seller_tax_total ?? 0;

        // Get support earnings counters using StatisticsService
        $supportQuery = $stats->forModel(SupportEarning::class)->scope('active');

        if ($dateRange) {
            $supportQuery->dateRange($startDate, $endDate);
        }

        $supportCounters = $supportQuery->counters([
            'support_earning' => ['sum', 'price'],
            'sellers_support_earnings' => ['sum', \Illuminate\Support\Facades\DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
            'support_earnings_seller_fees' => ['sum', 'seller_fee'],
        ]);

        // Get referral earnings using StatisticsService
        $referralQuery = $stats->forModel(ReferralEarning::class)->scope('active');

        if ($dateRange) {
            $referralQuery->dateRange($startDate, $endDate);
        }

        $referralCounters = $referralQuery->counters([
            'referral_earnings' => ['sum', 'seller_earning'],
        ]);

        // Get payout counters using StatisticsService
        $payoutQuery = $stats->forModel(Payout::class)->scope('completed');

        if ($dateRange) {
            $payoutQuery->dateRange($startDate, $endDate);
        }

        $payoutCounters = $payoutQuery->counters([
            'payout_amount' => ['sum', 'amount'],
            'total_payouts' => ['count', '*'],
        ]);

        // Get product and refund counts using StatisticsService
        $productQuery = $stats->forModel(Product::class)->scope('approved');

        if ($dateRange) {
            $productQuery->dateRange($startDate, $endDate);
        }

        $productCounters = $productQuery->counters([
            'total_products' => ['count', '*'],
        ]);

        $refundQuery = $stats->forModel(Refund::class)->scope('accepted');

        if ($dateRange) {
            $refundQuery->dateRange($startDate, $endDate);
        }

        $refundCounters = $refundQuery->counters([
            'total_refunds' => ['count', '*'],
        ]);

        // Get user counts using StatisticsService
        $userQuery = $stats->forModel(User::class)->scope('user');

        if ($dateRange) {
            $userQuery->dateRange($startDate, $endDate);
        }

        $userCounters = $userQuery->counters([
            'total_users' => ['count', '*'],
        ]);

        $sellerQuery = $stats->forModel(User::class)->scope('seller');

        if ($dateRange) {
            $sellerQuery->dateRange($startDate, $endDate);
        }

        $sellerCounters = $sellerQuery->counters([
            'total_sellers' => ['count', '*'],
        ]);

        // Merge all counters
        $counters = array_merge(
            $salesCounters,
            $supportCounters,
            $referralCounters,
            $payoutCounters,
            $productCounters,
            $refundCounters,
            $userCounters,
            $sellerCounters
        );

        // Calculate site owner revenue
        $counters['platform_total_revenues'] =
            $counters['buyer_fees'] +
            $counters['seller_fees'] +
            $counters['support_earnings_seller_fees'] +
            ($counters['support_earning'] - $counters['sellers_support_earnings']);

        // Calculate total expenses (payments to sellers)
        $counters['platform_total_expenses'] =
            $counters['sellers_earnings'] +
            $counters['sellers_support_earnings'] +
            $counters['referral_earnings'];

        if (isPremiumAvailable()) {
            $counters['free_premium_memberships'] = Premium::whereHas('plan', function ($query) {
                $query->free();
            })->count();
            $counters['paid_premium_memberships'] = Premium::whereHas('plan', function ($query) {
                $query->notFree();
            })->count();
            $counters['total_premium_memberships'] = Premium::active()->count();

            // Premium revenue with period filter
            $premiumTransactionQuery = Transaction::typePremium()->paid();
            if ($period !== 'lifetime') {
                $premiumTransactionQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $counters['premium_total_earnings'] = $premiumTransactionQuery->sum('amount');

            // Current year premium revenue (for dashboard analytics comparison)
            $counters['premium_current_year_earnings'] = Transaction::typePremium()
                ->paid()
                ->whereYear('created_at', date('Y'))
                ->sum('amount');

            // Premium seller earnings with period filter
            $premiumEarningQuery = PremiumEarning::query();
            if ($period !== 'lifetime') {
                $premiumEarningQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $counters['premium_sellers_earnings'] = $premiumEarningQuery->sum('seller_earning');

            // Add premium revenue and expenses to totals
            $counters['platform_total_revenues'] += ($counters['premium_total_earnings'] - $counters['premium_sellers_earnings']);
            $counters['platform_total_expenses'] += $counters['premium_sellers_earnings'];
        }

        return $counters;
    }

    /**
     * Get user registration analytics data via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAnalytics(): JsonResponse
    {
        $type = request('type', 'week');
        $offset = request('offset', 0);

        $cacheKey = "user_analytics_{$type}_{$offset}";
        return $this->cache->remember($cacheKey, function () use ($type, $offset) {
            return $this->buildUserAnalytics($type, $offset);
        }, 5);
    }

    /**
     * Build user analytics data
     */
    private function buildUserAnalytics(string $type, int $offset): JsonResponse
    {
        $result = $this->prepareUserAnalyticsConfig($type, $offset);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel(User::class);

            $analytics = match ($type) {
                'week' => $service->weeklyAnalytics($result['config']),
                'month' => $service->monthlyDailyAnalytics($result['config']),
                'year' => $service->monthlyAnalytics($result['config']),
            };

            return response()->json($analytics);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get user registration comparison data for last 5 years
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserComparisonAnalytics(): JsonResponse
    {
        $type = request('type', 'week');

        $result = $this->prepareUserAnalyticsConfig($type, 0, true);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel(User::class);

            $comparison = match ($type) {
                'week' => $service->weeklyComparison($result['config']),
                'month' => $service->monthlyComparison($result['config']),
                'year' => $service->yearlyComparison($result['config']),
            };

            return response()->json($comparison);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Prepare user analytics configuration
     *
     * @param string $type Period type: week, month, or year
     * @param int $offset Period offset (for navigation)
     * @param bool $isComparison Whether this is for comparison view
     * @return array|JsonResponse Returns config or JsonResponse on validation failure
     */
    private function prepareUserAnalyticsConfig(string $type, int $offset = 0, bool $isComparison = false): array|JsonResponse
    {
        // Validate request
        $rules = [
            'type' => 'required|string|in:week,month,year',
            'offset' => 'nullable|integer',
        ];

        $validator = $this->validateRequestJson(request(), $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Build configuration based on type
        $config = ['type' => 'count'];

        if ($isComparison) {
            // For comparison view, show last 5 periods
            $config['periods'] = 5;
        } else {
            // For single period view
            $config['offset'] = $offset;

            if ($type === 'year') {
                $config['year'] = date('Y') - $offset;
            }
        }

        return ['config' => $config];
    }

    /**
     * Get sales analytics data via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesAnalytics(): JsonResponse
    {
        $type = request('type', 'week');
        $offset = request('offset', 0);

        $cacheKey = "sales_analytics_{$type}_{$offset}";
        return $this->cache->remember($cacheKey, function () use ($type, $offset) {
            return $this->buildSalesAnalytics($type, $offset);
        }, 5);
    }

    /**
     * Build sales analytics data
     */
    private function buildSalesAnalytics(string $type, int $offset): JsonResponse
    {
        $result = $this->prepareSalesAnalyticsConfig($type, $offset);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel(Sale::class)->scope('active');

            $analytics = match ($type) {
                'week' => $service->weeklyAnalytics($result['config']),
                'month' => $service->monthlyDailyAnalytics($result['config']),
                'year' => $service->monthlyAnalytics($result['config']),
            };

            return response()->json($analytics);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get sales comparison data for last 5 years
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesComparisonAnalytics(): JsonResponse
    {
        $type = request('type', 'week');

        $result = $this->prepareSalesAnalyticsConfig($type, 0, true);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel(Sale::class)->scope('active');

            $comparison = match ($type) {
                'week' => $service->weeklyComparison($result['config']),
                'month' => $service->monthlyComparison($result['config']),
                'year' => $service->yearlyComparison($result['config']),
            };

            return response()->json($comparison);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Prepare sales analytics configuration
     *
     * @param string $type Period type: week, month, or year
     * @param int $offset Period offset (for navigation)
     * @param bool $isComparison Whether this is for comparison view
     * @return array|JsonResponse Returns config or JsonResponse on validation failure
     */
    private function prepareSalesAnalyticsConfig(string $type, int $offset = 0, bool $isComparison = false): array|JsonResponse
    {
        // Validate request
        $rules = [
            'type' => 'required|string|in:week,month,year',
            'offset' => 'nullable|integer',
        ];

        $validator = $this->validateRequestJson(request(), $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Build configuration based on type
        $config = ['type' => 'count'];

        if ($isComparison) {
            // For comparison view, show last 5 periods
            $config['periods'] = 5;
        } else {
            // For single period view
            $config['offset'] = $offset;

            if ($type === 'year') {
                $config['year'] = date('Y') - $offset;
            }
        }

        return ['config' => $config];
    }

    /**
     * Get country analytics data via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountryAnalytics(): JsonResponse
    {
        $period = request('period', 'this_month');

        // Validate request
        $rules = ['period' => 'required|string|in:last_7_days,last_28_days,this_month,this_year,lifetime'];
        $validator = $this->validateRequestJson(request(), $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $stats = app(StatisticsService::class)
                ->forModel(Sale::class)
                ->scope('active')
                ->whereNotNull('country');

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;

            // Apply date range if not lifetime
            if ($period !== 'lifetime') {
                $stats->dateRange($startDate, $endDate);
            }

            $currentData = $stats->geoData('byCountry', [
                'aggregation' => 'sum',
                'field' => 'price',
                'limit' => 6,
                'orderBy' => 'total_price',
            ]);

            // Get previous period data for comparison
            $previousData = collect();
            if ($period !== 'lifetime' && $startDate && $endDate) {
                $diff = $startDate->diffInDays($endDate);
                $prevStartDate = $startDate->copy()->subDays($diff + 1);
                $prevEndDate = $startDate->copy()->subDay();

                $previousStats = app(StatisticsService::class)
                    ->forModel(Sale::class)
                    ->scope('active')
                    ->whereNotNull('country')
                    ->dateRange($prevStartDate, $prevEndDate);

                $previousData = $previousStats->geoData('byCountry', [
                    'aggregation' => 'sum',
                    'field' => 'price',
                    'limit' => 7,
                    'orderBy' => 'total_price',
                ]);
            }

            // Calculate percentage changes
            $result = $currentData->map(function ($country) use ($previousData) {
                $previousValue = $previousData->firstWhere('country', $country->country)?->total_price ?? 0;
                $currentValue = $country->total_price ?? 0;

                $percentageChange = 0;
                if ($previousValue > 0) {
                    $percentageChange = (($currentValue - $previousValue) / $previousValue) * 100;
                }

                return [
                    'country' => $country->country,
                    'country_name' => countries($country->country),
                    'flag' => countryFlag($country->country),
                    'amount' => $currentValue,
                    'formatted_amount' => getAmount($currentValue),
                    'percentage_change' => round($percentageChange, 1),
                    'is_positive' => $percentageChange >= 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result->values(),
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get premium analytics data via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPremiumAnalytics(): JsonResponse
    {
        $year = request('year', date('Y'));

        $result = $this->preparePremiumAnalyticsConfig($year);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel($result['model']);

            // Apply scopes if specified (for sales type)
            if (isset($result['scope'])) {
                $service->scope($result['scope']);
            }
            if (isset($result['additionalScope'])) {
                $service->scope($result['additionalScope']);
            }

            $analytics = $service->monthlyAnalytics($result['config']);

            return response()->json($analytics);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get premium analytics comparison data for last 5 years
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPremiumComparisonAnalytics(): JsonResponse
    {
        $result = $this->preparePremiumAnalyticsConfig(null);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        try {
            $service = app(StatisticsService::class)->forModel($result['model']);

            // Apply scopes if specified (for sales type)
            if (isset($result['scope'])) {
                $service->scope($result['scope']);
            }
            if (isset($result['additionalScope'])) {
                $service->scope($result['additionalScope']);
            }

            $comparison = $service->yearlyComparison($result['config']);

            return response()->json($comparison);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Prepare premium analytics configuration
     *
     * @param int|null $year Year for monthly analytics (null for yearly comparison)
     * @return array|JsonResponse Returns config and model or JsonResponse on validation failure
     */
    private function preparePremiumAnalyticsConfig(?int $year = null): array|JsonResponse
    {
        // Build validation rules
        $rules = ['type' => 'required|string|in:sales,revenue,members'];

        if ($year !== null) {
            $rules['year'] = 'nullable|integer|min:1900|max:2100';
        }

        // Validate request
        $validator = $this->validateRequestJson(request(), $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Get request parameters
        $type = request('type', 'sales');
        $years = request('years', 5);

        // Build configuration based on type
        $config = match ($type) {
            'sales' => [
                'type' => 'sum',
                'field' => 'amount',
            ],
            'revenue' => [
                'type' => 'raw',
                'field' => 'SUM(price - seller_earning)',
            ],
            'members' => [
                'type' => 'count',
            ],
        };

        // Add time parameters
        if ($year !== null) {
            $config['year'] = $year;
        } else {
            $config['years'] = $years;
        }

        return [
            'config' => $config,
            'model' => $type === 'revenue' ? PremiumEarning::class : ($type === 'members' ? Premium::class : Transaction::class),
            'scope' => $type === 'sales' ? 'typePremium' : null,
            'additionalScope' => $type === 'sales' ? 'paid' : null,
        ];
    }

    /**
     * Get traffic source analytics data via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrafficSourcesAnalytics(): JsonResponse
    {
        $period = request('period', 'this_month');

        try {
            $trafficSources = $this->getTrafficSourceData($period);

            return response()->json([
                'success' => true,
                'data' => $trafficSources,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get top selling products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTopSellingProducts(): EloquentCollection
    {
        return $this->cache->remember('top_selling_products', function () {
            return Sale::active()
                ->select('product_id', DB::raw('COUNT(*) as total_sales'))
                ->groupBy('product_id')
                ->orderByDesc('total_sales')
                ->limit(6)
                ->get();
        }, 15);
    }

    /**
     * Get top rated products
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTopRatedProducts(): Collection
    {
        return $this->cache->remember('top_rated_products', function () {
            return Product::approved()
                ->select('id', 'name', 'slug', 'preview_image', 'preview_type')
                ->with(['reviews' => function ($query) {
                    $query->select('product_id', DB::raw('AVG(stars) as avg_rating'), DB::raw('COUNT(*) as total_reviews'))
                        ->groupBy('product_id');
                }])
                ->whereHas('reviews')
                ->withAvg('reviews', 'stars')
                ->withCount('reviews')
                ->orderByDesc('reviews_avg_stars')
                ->limit(6)
                ->get();
        }, 15);
    }

    /**
     * Get top sold product of the month
     *
     * @return array
     */
    private function getTopSoldProductOfMonth(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $topProduct = Sale::active()
            ->whereMonth('created_at', Date::now()->month)
            ->whereYear('created_at', Date::now()->year)
            ->select('product_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(price) as total_sales'))
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(price)'))
            ->first();

        // Fallback to all-time if no sales this month
        if (!$topProduct) {
            $topProduct = Sale::active()
                ->select('product_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(price) as total_sales'))
                ->groupBy('product_id')
                ->orderByDesc(DB::raw('SUM(price)'))
                ->first();
        }

        if (!$topProduct || !$topProduct->product) {
            return [
                'id' => null,
                'name' => translate('No Product Yet'),
                'thumbnail' => asset('images/illustrations/empty-cart.png'),
                'total_sales' => 0,
                'sales_count' => 0,
            ];
        }

        // Check if product seller has been congratulated this month for this product
        $productSeller = $topProduct->product->seller;
        $congratulatedThisMonth = false;
        if ($productSeller) {
            $congratulatedThisMonth = $productSeller->notifications()
                ->where('type', 'App\Notifications\CongratsNotification')
                ->whereJsonContains('data->template', 'top_product')
                ->whereJsonContains('data->product_id', $topProduct->product->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->exists();
        }

        return [
            'id' => $topProduct->product->id,
            'name' => $topProduct->product->name,
            'thumbnail' => $topProduct->product->thumbnail_url,
            'total_sales' => $topProduct->total_sales,
            'sales_count' => $topProduct->sales_count,
            'congratulated' => $congratulatedThisMonth,
        ];
    }

    /**
     * Get support tracker analytics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupportTicketAnalytics(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Validate period
            if (!in_array($period, ['last_7_days', 'last_28_days', 'this_month', 'this_year', 'lifetime'])) {
                return $this->errorJson('Invalid period', [], 400);
            }

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;

            // Get ticket statistics
            $totalTicketsQuery = Ticket::query();
            if ($period !== 'lifetime') {
                $totalTicketsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $totalTickets = $totalTicketsQuery->count();

            $openedTicketsQuery = Ticket::opened();
            if ($period !== 'lifetime') {
                $openedTicketsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $openedTickets = $openedTicketsQuery->count();

            $closedTicketsQuery = Ticket::closed();
            if ($period !== 'lifetime') {
                $closedTicketsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $closedTickets = $closedTicketsQuery->count();

            // Calculate new tickets (created in period)
            $newTicketsQuery = Ticket::query();
            if ($period !== 'lifetime') {
                $newTicketsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $newTickets = $newTicketsQuery->count();

            // Calculate completion percentage
            $completion = $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100) : 0;

            $response = [
                'success' => true,
                'data' => [
                    'total' => $totalTickets,
                    'new' => $newTickets,
                    'open' => $openedTickets,
                    'percentage' => $completion,
                ],
                'period' => $period,
            ];

            // Only include dates if not lifetime
            if ($period !== 'lifetime') {
                $response['startDate'] = $startDate->format('Y-m-d');
                $response['endDate'] = $endDate->format('Y-m-d');
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get refund statistics (declined vs accepted refunds)
     *
     * @return JsonResponse
     */
    public function getRefundStats(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Validate period
            if (!in_array($period, ['last_7_days', 'last_28_days', 'this_month', 'this_year', 'lifetime'])) {
                return $this->errorJson('Invalid period', [], 400);
            }

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;

            // Get refund statistics for the period
            $totalRefundsQuery = Refund::query();
            if ($period !== 'lifetime') {
                $totalRefundsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $totalRefunds = $totalRefundsQuery->count();

            $declinedQuery = Refund::declined();
            if ($period !== 'lifetime') {
                $declinedQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $declinedTotal = $declinedQuery->count();

            $acceptedQuery = Refund::accepted();
            if ($period !== 'lifetime') {
                $acceptedQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $acceptedTotal = $acceptedQuery->count();

            // Calculate percentage of accepted refunds
            $percentage = $totalRefunds > 0 ? round(($acceptedTotal / $totalRefunds) * 100) : 0;

            $response = [
                'success' => true,
                'data' => [
                    'total' => $totalRefunds,
                    'new' => $declinedTotal,
                    'open' => $acceptedTotal,
                    'percentage' => $percentage
                ],
                'period' => $period,
            ];

            // Only include dates if not lifetime
            if ($period !== 'lifetime') {
                $response['startDate'] = $startDate->format('Y-m-d');
                $response['endDate'] = $endDate->format('Y-m-d');
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get statistics data with period filter
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $period = request('period', 'lifetime');

            // Validate period
            if (!in_array($period, ['today', 'last_7_days', 'last_28_days', 'this_month', 'this_year', 'lifetime'])) {
                return $this->errorJson('Invalid period', [], 400);
            }

            $cacheKey = "stats_{$period}";
            $counters = $this->cache->remember($cacheKey, function () use ($period) {
                return $this->getStatisticsData($period);
            }, 5);

            return response()->json([
                'success' => true,
                'counters' => $counters,
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get revenue and expenses data with period filter and comparison
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueExpense(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Validate period
            if (!in_array($period, ['last_7_days', 'last_28_days', 'this_month', 'this_year', 'lifetime'])) {
                return $this->errorJson('Invalid period', [], 400);
            }

            // Get current period dates
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $currentStartDate = $dateRange['start'] ?? null;
            $currentEndDate = $dateRange['end'] ?? null;

            // Get previous period dates for comparison
            $previousStartDate = null;
            $previousEndDate = null;
            if ($period !== 'lifetime' && $currentStartDate) {
                $diff = $currentStartDate->diffInDays($currentEndDate);
                $previousStartDate = $currentStartDate->copy()->subDays($diff + 1);
                $previousEndDate = $currentStartDate->copy()->subDay();
            }

            // Get current period data
            $currentCounters = $this->getStatisticsData($period === 'lifetime' ? 'lifetime' : 'custom');
            if ($period !== 'lifetime') {
                $currentCounters = $this->getStatisticsDataForDateRange($currentStartDate, $currentEndDate);
            }

            // Get previous period data for comparison
            $previousCounters = ['platform_total_revenues' => 0, 'platform_total_expenses' => 0];
            if ($period !== 'lifetime') {
                $previousCounters = $this->getStatisticsDataForDateRange($previousStartDate, $previousEndDate);
            }

            // Calculate comparison percentages
            $revenueChange = $this->calculatePercentageChange(
                $currentCounters['platform_total_revenues'],
                $previousCounters['platform_total_revenues']
            );

            $expensesChange = $this->calculatePercentageChange(
                $currentCounters['platform_total_expenses'],
                $previousCounters['platform_total_expenses']
            );

            $responseData = [
                'success' => true,
                'data' => [
                    'revenue' => $currentCounters['platform_total_revenues'],
                    'expense' => $currentCounters['platform_total_expenses'],
                    'previous_revenue' => $previousCounters['platform_total_revenues'],
                    'previous_expense' => $previousCounters['platform_total_expenses'],
                    'revenue_change' => $revenueChange,
                    'expense_change' => $expensesChange,
                ],
                'period' => $period,
            ];

            // For lifetime, include year-by-year breakdown
            if ($period === 'lifetime') {
                $responseData['data']['yearly_breakdown'] = $this->getYearlyRevenueExpenseBreakdown();
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get yearly revenue and expense breakdown for lifetime period
     */
    private function getYearlyRevenueExpenseBreakdown()
    {
        $currentYear = Date::now()->year;
        $years = range($currentYear - 4, $currentYear); // Last 5 years
        $breakdown = [];

        foreach ($years as $year) {
            $startDate = Date::create($year, 1, 1)->startOfYear();
            $endDate = Date::create($year, 12, 31)->endOfYear();

            $counters = $this->getStatisticsDataForDateRange($startDate, $endDate);

            $breakdown[] = [
                'year' => (string) $year,
                'revenue' => $counters['platform_total_revenues'],
                'expense' => $counters['platform_total_expenses'],
            ];
        }

        return $breakdown;
    }

    /**
     * Get counters data for specific date range
     */
    private function getStatisticsDataForDateRange($startDate, $endDate)
    {
        $stats = app(StatisticsService::class);

        // Get sales-related counters
        $salesCounters = $stats->forModel(Sale::class)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'sellers_sales' => ['sum', 'price'],
                'sellers_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
                'buyer_fees' => ['sum', 'buyer_fee'],
                'seller_fees' => ['sum', 'seller_fee'],
            ]);

        // Get support earnings
        $supportCounters = $stats->forModel(SupportEarning::class)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'support_earning' => ['sum', 'price'],
                'sellers_support_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
                'support_earnings_seller_fees' => ['sum', 'seller_fee'],
            ]);

        // Get referral earnings
        $referralCounters = $stats->forModel(ReferralEarning::class)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'referral_earnings' => ['sum', 'seller_earning'],
            ]);

        // Calculate platform revenue
        $counters['platform_total_revenues'] =
            $salesCounters['buyer_fees'] +
            $salesCounters['seller_fees'] +
            $supportCounters['support_earnings_seller_fees'] +
            ($supportCounters['support_earning'] - $supportCounters['sellers_support_earnings']);

        // Calculate total expenses
        $counters['platform_total_expenses'] =
            $salesCounters['sellers_earnings'] +
            $supportCounters['sellers_support_earnings'] +
            $referralCounters['referral_earnings'];

        // Add premium if available
        if (isPremiumAvailable()) {
            $premiumRevenue = Transaction::typePremium()
                ->paid()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $premiumSellerEarnings = PremiumEarning::whereBetween('created_at', [$startDate, $endDate])
                ->sum('seller_earning');

            $counters['platform_total_revenues'] += ($premiumRevenue - $premiumSellerEarnings);
            $counters['platform_total_expenses'] += $premiumSellerEarnings;
        }

        return $counters;
    }

    /**
     * Get traffic source data with statistics
     */
    /**
     * Get traffic source data from page views analytics
     * Tracks all visitor page views with accurate referrer source detection
     */
    private function getTrafficSourceData(string $period = 'this_month'): array
    {
        // Get date ranges
        $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
        $startDate = $dateRange['start'] ?? null;
        $endDate = $dateRange['end'] ?? null;

        // Get previous period for comparison
        $prevStartDate = null;
        $prevEndDate = null;
        if ($period !== 'lifetime' && $startDate && $endDate) {
            $dateRangeDiff = $startDate->diffInDays($endDate);
            $prevStartDate = $startDate->copy()->subDays($dateRangeDiff + 1);
            $prevEndDate = $startDate->copy()->subDay();
        }

        // Get total unique visitors (unique sessions) for current period
        $totalVisitorsQuery = DB::table('page_views')->distinct('session_id');
        if ($period !== 'lifetime') {
            $totalVisitorsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalVisitors = $totalVisitorsQuery->count('session_id');

        // Get traffic by source for current period
        $currentTrafficQuery = DB::table('page_views')
            ->select('traffic_source', DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->whereNotNull('traffic_source')
            ->groupBy('traffic_source');

        if ($period !== 'lifetime') {
            $currentTrafficQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $currentTrafficSources = $currentTrafficQuery->get()->keyBy('traffic_source');

        // Get traffic by source for previous period
        $previousTrafficQuery = DB::table('page_views')
            ->select('traffic_source', DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->whereNotNull('traffic_source')
            ->groupBy('traffic_source');

        if ($period !== 'lifetime' && $prevStartDate && $prevEndDate) {
            $previousTrafficQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        }

        $previousTrafficSources = $previousTrafficQuery->get()->keyBy('traffic_source');

        // Define traffic sources with their display information
        $sourceDefinitions = [
            'Direct' => [
                'name' => translate('Direct Traffic'),
                'description' => parse_url(@settings('general')->site_url, PHP_URL_HOST) . ' ' . translate('visit directly'),
                'icon' => 'bi-link-45deg',
            ],
            'Social' => [
                'name' => translate('Social Network'),
                'description' => translate('Social Channels'),
                'icon' => 'bi-share',
            ],
            'Email' => [
                'name' => translate('Email Newsletter'),
                'description' => translate('Mail Campaigns'),
                'icon' => 'bi-envelope',
            ],
            'Referral' => [
                'name' => translate('Referrals'),
                'description' => translate('Referral Links'),
                'icon' => 'bi-box-arrow-up-right',
            ],
            'Search' => [
                'name' => translate('Search Engines'),
                'description' => translate('Google, Bing & Others'),
                'icon' => 'bi-search',
            ],
            'Ads' => [
                'name' => translate('Advertising'),
                'description' => translate('Paid Advertising'),
                'icon' => 'bi-badge-ad',
            ],
        ];

        // Build sources array with actual data
        $sources = [];
        foreach ($sourceDefinitions as $sourceKey => $sourceInfo) {
            $currentCount = $currentTrafficSources->get($sourceKey)?->visitors ?? 0;
            $previousCount = $previousTrafficSources->get($sourceKey)?->visitors ?? 0;

            $sources[] = array_merge($sourceInfo, [
                'count' => $currentCount,
                'previous_count' => $previousCount,
            ]);
        }

        // Add "Other" category for any remaining traffic
        $otherCountQuery = DB::table('page_views')
            ->whereNotIn('traffic_source', array_keys($sourceDefinitions))
            ->distinct('session_id');

        if ($period !== 'lifetime') {
            $otherCountQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $otherCount = $otherCountQuery->count('session_id');

        $otherPrevCountQuery = DB::table('page_views')
            ->whereNotIn('traffic_source', array_keys($sourceDefinitions))
            ->distinct('session_id');

        if ($period !== 'lifetime' && $prevStartDate && $prevEndDate) {
            $otherPrevCountQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        }

        $otherPrevCount = $otherPrevCountQuery->count('session_id');

        $sources[] = [
            'name' => translate('Other'),
            'description' => translate('Other Sources'),
            'icon' => 'bi-star',
            'count' => $otherCount,
            'previous_count' => $otherPrevCount,
        ];

        // Calculate percentage changes and format
        $sources = collect($sources)->map(function ($source) {
            $change = $this->calculatePercentageChange($source['count'], $source['previous_count']);

            return array_merge($source, [
                'percentage_change' => $change,
                'is_positive' => $change >= 0,
                'formatted_count' => numberFormat($source['count']),
            ]);
        });

        return [
            'total_visitors' => numberFormat($totalVisitors),
            'sources' => $sources->toArray(),
        ];
    }

    /**
     * Get recent admin login activities (successful and failed)
     */
    private function getAdminLoginActivities(): array
    {
        return $this->cache->remember('admin_logins', function () {
            // Get successful admin logins (last 5) using Eloquent for proper enum casting
            $successfulLogins = DB::table('admin_login_activities')
                ->orderBy('admin_login_activities.created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($activity) {
                    $admin = Admin::find($activity->admin_id);
                    if ($admin) {
                        $activity->admin_name = $admin->full_name;
                        $activity->role_name = $admin->role_label;
                        $activity->admin_avatar = $admin->avatar_url;
                    }
                    return $activity;
                })
                ->filter(fn($activity) => isset($activity->admin_name));

            // Get failed login attempts (last 5)
            $failedAttempts = collect();

            // Check if we have a failed_login_attempts table
            if (DB::getSchemaBuilder()->hasTable('failed_login_attempts')) {
                $failedAttempts = DB::table('failed_login_attempts')
                    ->where('guard', 'admin')
                    ->orderBy('attempted_at', 'desc')
                    ->limit(5)
                    ->get();
            }

            return [
                'successful_logins' => $successfulLogins,
                'failed_attempts' => $failedAttempts,
            ];
        }, 3);
    }

    /**
     * Get product status analytics
     */
    public function getProductStatus(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;
            $useWhere = $period !== 'lifetime';

            // Get product counts by status
            $pendingQuery = Product::pending();
            $approvedQuery = Product::approved();
            $hardRejectedQuery = Product::softRejected();
            $softRejectedQuery = Product::hardRejected();
            $resubmittedQuery = Product::resubmitted();

            if ($useWhere) {
                $pendingQuery->whereBetween('created_at', [$startDate, $endDate]);
                $approvedQuery->whereBetween('created_at', [$startDate, $endDate]);
                $hardRejectedQuery->whereBetween('created_at', [$startDate, $endDate]);
                $softRejectedQuery->whereBetween('created_at', [$startDate, $endDate]);
                $resubmittedQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $pending = $pendingQuery->count();
            $approved = $approvedQuery->count();
            $resubmitted = $resubmittedQuery->count();
            $rejected = $hardRejectedQuery->count() + $softRejectedQuery->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'segment1' => $approved,
                    'segment2' => $pending,
                    'segment3' => $rejected,
                    'segment4' => $resubmitted,
                    'display' => $approved,
                ],
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get user role distribution analytics
     */
    public function getUserRole(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;
            $useWhere = $period !== 'lifetime';

            // Get user counts by role
            $userQuery = User::query();
            $buyerQuery = User::query();
            $sellerQuery = User::query();

            if ($useWhere) {
                $userQuery->whereBetween('created_at', [$startDate, $endDate]);
                $buyerQuery->whereBetween('created_at', [$startDate, $endDate]);
                $sellerQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Regular users (not sellers, not buyers)
            $users = $userQuery->where('is_seller', 0)->doesntHave('purchases')->count();

            // Buyers (users who have made purchases but are not sellers)
            $buyers = $buyerQuery->where('is_seller', 0)->has('purchases')->count();

            // Sellers
            $sellers = $sellerQuery->where('is_seller', 1)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'segment1' => $users,
                    'segment2' => $buyers,
                    'segment3' => $sellers,
                    'segment4' => 0, // Not used
                    'display' => $buyers,
                ],
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get revenue sources analytics (Platform Income)
     */
    public function getRevenueSource(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;
            $useWhere = $period !== 'lifetime';

            $stats = app(StatisticsService::class);

            // Get sales fees (buyer + seller fees)
            $salesQuery = $stats->forModel(Sale::class)->scope('active');
            if ($useWhere) {
                $salesQuery->dateRange($startDate, $endDate);
            }
            $salesCounters = $salesQuery->counters([
                'buyer_fees' => ['sum', 'buyer_fee'],
                'seller_fees' => ['sum', 'seller_fee'],
            ]);

            // Get support earnings (platform cut from support)
            $supportQuery = $stats->forModel(SupportEarning::class)->scope('active');
            if ($useWhere) {
                $supportQuery->dateRange($startDate, $endDate);
            }
            $supportCounters = $supportQuery->counters([
                'support_earning' => ['sum', 'price'],
                'sellers_support_earnings' => ['sum', 'seller_earning'],
                'support_fees' => ['sum', 'seller_fee'],
            ]);

            // Calculate revenue components
            $buyerFees = $salesCounters['buyer_fees'];
            $sellerFees = $salesCounters['seller_fees'] + $supportCounters['support_fees'];
            $supportRevenue = $supportCounters['support_earning'] - $supportCounters['sellers_support_earnings'];
            $premiumRevenue = 0;

            // Get premium revenue if available
            if (isPremiumAvailable()) {
                $premiumTransactionQuery = Transaction::typePremium()->paid();
                if ($useWhere) {
                    $premiumTransactionQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
                $premiumTotal = $premiumTransactionQuery->sum('amount');

                $premiumEarningQuery = PremiumEarning::query();
                if ($useWhere) {
                    $premiumEarningQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
                $premiumSellerEarnings = $premiumEarningQuery->sum('seller_earning');

                $premiumRevenue = $premiumTotal - $premiumSellerEarnings;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'segment1' => $buyerFees,
                    'segment2' => $sellerFees,  // Seller Fees (Sales + Support)
                    'segment3' => $supportRevenue,
                    'segment4' => $premiumRevenue,
                    'display' => $buyerFees + $salesCounters['seller_fees'],  // Only Sales Fees
                ],
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get expenses type analytics (Platform Payouts)
     */
    public function getExpensesType(): JsonResponse
    {
        try {
            $period = request('period', 'this_month');

            // Get date range using helper method
            $dateRange = $period !== 'lifetime' ? $this->getDateRangeForPeriod($period) : null;
            $startDate = $dateRange['start'] ?? null;
            $endDate = $dateRange['end'] ?? null;
            $useWhere = $period !== 'lifetime';

            $stats = app(StatisticsService::class);

            // Get sellers earnings from sales
            $salesQuery = $stats->forModel(Sale::class)->scope('active');
            if ($useWhere) {
                $salesQuery->dateRange($startDate, $endDate);
            }
            $salesCounters = $salesQuery->counters([
                'sellers_earnings' => ['sum', 'seller_earning'],
            ]);

            // Get support earnings paid to sellers
            $supportQuery = $stats->forModel(SupportEarning::class)->scope('active');
            if ($useWhere) {
                $supportQuery->dateRange($startDate, $endDate);
            }
            $supportCounters = $supportQuery->counters([
                'sellers_support_earnings' => ['sum', 'seller_earning'],
            ]);

            // Get referral earnings
            $referralQuery = $stats->forModel(ReferralEarning::class)->scope('active');
            if ($useWhere) {
                $referralQuery->dateRange($startDate, $endDate);
            }
            $referralCounters = $referralQuery->counters([
                'referral_earnings' => ['sum', 'seller_earning'],
            ]);

            // Calculate expense components
            $salesEarnings = $salesCounters['sellers_earnings'];
            $supportEarnings = $supportCounters['sellers_support_earnings'];
            $referralEarnings = $referralCounters['referral_earnings'];
            $premiumEarnings = 0;

            // Get premium earnings paid to sellers if available
            if (isPremiumAvailable()) {
                $premiumEarningQuery = PremiumEarning::query();
                if ($useWhere) {
                    $premiumEarningQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
                $premiumEarnings = $premiumEarningQuery->sum('seller_earning');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'segment1' => $salesEarnings,
                    'segment2' => $supportEarnings,
                    'segment3' => $referralEarnings,
                    'segment4' => $premiumEarnings,
                    'display' => $salesEarnings,
                ],
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get geographic sales data for map visualization
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGeoChartData(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'this_month');

            // Build statistics query
            $stats = app(StatisticsService::class)
                ->forModel(Sale::class)
                ->scope('active')
                ->whereNotNull('country');

            // Apply period filtering
            $dateFilter = $this->getDateRangeForPeriod($period);
            if ($dateFilter) {
                $stats->where('created_at', '>=', $dateFilter['start'])
                    ->where('created_at', '<=', $dateFilter['end']);
            }

            $geoCountries = $stats->geoData('byCountry', [
                'aggregation' => 'count',
                'field' => '*',
            ]);

            // Filter out any null country values and ensure valid counts
            $geoCountries = $geoCountries->filter(function ($item) {
                return !empty($item->country) && isset($item->total_count) && $item->total_count > 0;
            });

            // Format data for Google GeoChart
            $chartData = [['Country', translate('Sales')]];
            foreach ($geoCountries as $country) {
                $chartData[] = [$country->country, (int) $country->total_count];
            }

            return response()->json([
                'success' => true,
                'data' => $chartData,
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get product issues statistics (reported and restricted) with period filtering
     *
     * @return JsonResponse
     */
    public function getProductIssues(): JsonResponse
    {
        try {
            // Build product query
            $productQuery = Product::query();

            // Get total products count for percentage calculation
            $totalProducts = $productQuery->count();

            // Get reported products data
            $reportedTotal = ProductReport::distinct('product_id')->count('product_id');

            // Get restricted products data - lifetime
            $restrictedTotal = $productQuery->restricted()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'reported' => [
                        'total' => numberFormat($reportedTotal),
                        'percentage' => $totalProducts > 0 ? round(($reportedTotal / $totalProducts) * 100, 1) : 0
                    ],
                    'restricted' => [
                        'total' => numberFormat($restrictedTotal),
                        'percentage' => $totalProducts > 0 ? round(($restrictedTotal / $reportedTotal) * 100, 1) : 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get user verification statistics (email and ID verification)
     *
     * @return JsonResponse
     */
    public function getUserVerification(): JsonResponse
    {
        try {
            // Build user query
            $userQuery = User::query();

            // Get total users count
            $totalUsers = $userQuery->count();

            // Get email verified users - lifetime
            $emailVerifiedTotal = $userQuery->emailVerified()->count();

            // Get ID verified users from email verified users - lifetime
            $idVerifiedTotal = $userQuery->emailVerified()->idVerified()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'email_verified' => [
                        'total' => numberFormat($emailVerifiedTotal),
                        'percentage' => $totalUsers > 0 ? round(($emailVerifiedTotal / $totalUsers) * 100, 1) : 0
                    ],
                    'id_verified' => [
                        'total' => numberFormat($idVerifiedTotal),
                        'percentage' => $emailVerifiedTotal > 0 ? round(($idVerifiedTotal / $emailVerifiedTotal) * 100, 1) : 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get date range for a given period
     *
     * @param string $period The period identifier
     * @return array|null Array with 'start' and 'end' keys, or null for lifetime
     */
    private function getDateRangeForPeriod(string $period): ?array
    {
        // Return cached result if available
        if (isset($this->dateRangeCache[$period])) {
            return $this->dateRangeCache[$period];
        }

        $now = now();

        $result = match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_7_days' => [
                'start' => $now->copy()->subDays(7)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_28_days' => [
                'start' => $now->copy()->subDays(28)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_90_days' => [
                'start' => $now->copy()->subDays(90)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'lifetime' => null,
            default => null,
        };

        // Cache the result
        $this->dateRangeCache[$period] = $result;
        return $result;
    }
}
