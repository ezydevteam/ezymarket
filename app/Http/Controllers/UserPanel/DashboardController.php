<?php
namespace App\Http\Controllers\UserPanel;

use App\Enums\LicenseType;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Services\Statistics\StatisticsService;
use App\Models\Product\{Product, ProductView};
use App\Models\{Purchase, Sale, Refund, ReferralEarning, Financial\Transaction, Support\Ticket};
use Illuminate\Contracts\View\View;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @var StatisticsService
     */
    public function __construct(private StatisticsService $statisticsService) {}

    /**
     * Display the seller dashboard with performance analytics.
     *
     * @return View
     */
    public function index(): view
    {
        $currentPeriod = request('period', 'last_28_days');
        [$startDate, $endDate] = $this->getPeriodDates($currentPeriod);

        // Initialize statistics service
        $stats = $this->statisticsService;

        // Gather core statistics
        $counters = $this->generateCounters($startDate, $endDate);
        $comparisonData = $this->getComparisonData($stats, $currentPeriod, $counters);
        $charts = $this->getDashboardCharts($stats, $startDate, $endDate);

        // Gather distribution data
        $topSellingProducts = $this->getTopSellingProducts($stats, $startDate, $endDate);
        $geoData = $this->getGeoData($stats, $startDate, $endDate);
        $referrals = $this->getReferralData($stats, $startDate, $endDate);

        $licenseDistribution = $this->getLicenseDistribution($stats, $startDate, $endDate);
        $refundsDistribution = $this->getRefundsDistribution($stats, $startDate, $endDate);

        return theme_view('userpanel.dashboard', [
            'counters' => $counters,
            'comparisonData' => $comparisonData,
            'charts' => $charts,
            'licenseDistribution' => $licenseDistribution,
            'refundsDistribution' => $refundsDistribution,
            'topSellingProducts' => $topSellingProducts,
            'topPurchasingCountries' => $geoData['topPurchasingCountries'],
            'geoCountries' => $geoData['geoCountries'],
            'referrals' => $referrals,
            'currentPeriod' => $currentPeriod,
            'previousPeriodLabel' => $this->getPeriodLabel($currentPeriod, 'M Y'),
        ]);
    }

    /**
     * Export comprehensive dashboard report as PDF.
     *
     * @param string $format Export format: pdf or excel
     * @return \Illuminate\Http\Response
     */
    public function exportDashboardReport(string $format = 'pdf')
    {
        $currentPeriod = request('period', 'last_28_days');
        [$startDate, $endDate] = $this->getPeriodDates($currentPeriod);

        // Initialize statistics service
        $stats = $this->statisticsService;

        // Gather all dashboard data
        $counters = $this->generateCounters($startDate, $endDate);

        // Get sales data - still using Eloquent for relationships
        $sales = Sale::active()
            ->where('seller_id', authUser()->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->with(['product:id,name', 'user:id,username'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get top selling products using StatisticsService
        $topSellingProducts = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->topItems('product_id', [
                'aggregations' => [
                    'total_sales' => ['count', '*'],
                    'total_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
                ],
                'orderBy' => 'total_sales',
                'limit' => 10,
            ])
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'title' => $product ? $product->name : 'N/A',
                    'sales' => $item->total_sales,
                    'earnings' => $item->total_earnings,
                ];
            });

        // Get geographic data using StatisticsService
        $salesByCountry = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->whereNotNull('country')
            ->dateRange($startDate, $endDate)
            ->topItems('country', [
                'aggregations' => [
                    'total_sales' => ['count', '*'],
                    'total_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
                ],
                'orderBy' => 'total_sales',
                'limit' => 15,
            ]);

        // Get referral data using StatisticsService
        $referrals = $stats->forModel(ProductView::class)
            ->whereHas('product', fn($query) => $query->where('seller_id', authUser()->id))
            ->whereNotNull('referrer')
            ->dateRange($startDate, $endDate)
            ->referrals([
                'field' => 'referrer',
                'limit' => 15,
            ]);

        // Get views data using StatisticsService
        $viewsCounters = $stats->forModel(ProductView::class)
            ->whereHas('product', fn($query) => $query->where('seller_id', authUser()->id))
            ->dateRange($startDate, $endDate)
            ->counters([
                'total_views' => ['count', '*'],
            ]);

        $totalViews = $viewsCounters['total_views'];

        // Generate distribution data
        $licenseDistribution = $this->getLicenseDistribution($stats, $startDate, $endDate);
        $refundsDistribution = $this->getRefundsDistribution($stats, $startDate, $endDate);

        // Format period for display
        $periodLabel = $this->getPeriodLabel($currentPeriod);

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('vendor.pdf.user.dashboard-report', [
            'user' => authUser(),
            'period' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'counters' => $counters,
            'sales' => $sales,
            'topSellingProducts' => $topSellingProducts,
            'salesByCountry' => $salesByCountry,
            'referrals' => $referrals,
            'totalViews' => $totalViews,
            'licenseDistribution' => $licenseDistribution,
            'refundsDistribution' => $refundsDistribution,
        ])->setPaper('a4', 'portrait')
          ->setOption('margin-top', 10)
          ->setOption('margin-bottom', 10)
          ->setOption('margin-left', 10)
          ->setOption('margin-right', 10);

        return $pdf->download('dashboard-report-' . $currentPeriod . '.pdf');
    }

    /**
     * Calculate period-over-period comparison data.
     *
     * @param StatisticsService $stats
     * @param string $currentPeriod
     * @param array $counters
     * @return array
     */
    private function getComparisonData(StatisticsService $stats, string $currentPeriod, array $counters): array
    {
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriodDates($currentPeriod);
        $prevCounters = $this->generateCounters($prevStartDate, $prevEndDate);

        return [
            'total_sales' => $this->calculatePercentageChange($prevCounters['total_sales'], $counters['total_sales']),
            'sales_earnings' => $this->calculatePercentageChange($prevCounters['sales_earnings'], $counters['sales_earnings']),
            'referrals_earnings' => $this->calculatePercentageChange($prevCounters['referrals_earnings'], $counters['referrals_earnings']),
            'total_views' => $this->calculatePercentageChange($prevCounters['total_views'], $counters['total_views']),
            'net_earnings' => $this->calculatePercentageChange(
                $prevCounters['sales_earnings'] + $prevCounters['referrals_earnings'],
                $counters['sales_earnings'] + $counters['referrals_earnings']
            ),
        ];
    }

    /**
     * Generate sales and views chart data.
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function getDashboardCharts(StatisticsService $stats, $startDate, $endDate): array
    {
        return [
            'sales' => $stats->forModel(Sale::class)
                ->where('seller_id', authUser()->id)
                ->scope('active')
                ->dateRange($startDate, $endDate)
                ->chart('timeSeries', [
                    'title' => translate('Sales'),
                    'dateField' => 'created_at',
                    'aggregation' => 'count',
                ]),
            'views' => $this->generateViewsChartData($startDate, $endDate),
        ];
    }

    /**
     * Get geographic distribution data by country.
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function getGeoData(StatisticsService $stats, $startDate, $endDate): array
    {
        $geoCountries = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->whereNotNull('country')
            ->dateRange($startDate, $endDate)
            ->geoData('byCountry', [
                'aggregation' => 'count',
                'field' => '*',
            ])
            ->filter(fn($item) => !empty($item->country) && isset($item->total_count) && $item->total_count > 0);

        $topPurchasingCountries = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->whereNotNull('country')
            ->dateRange($startDate, $endDate)
            ->geoData('byCountry', [
                'aggregation' => 'sum',
                'field' => DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)'),
                'limit' => 6,
                'orderBy' => 'total_seller_earning',
            ])
            ->filter(fn($item) => !empty($item->country) && isset($item->total_seller_earning) && $item->total_seller_earning > 0);

        return [
            'geoCountries' => $geoCountries,
            'topPurchasingCountries' => $topPurchasingCountries,
        ];
    }

    /**
     * Get the most popular products for the current seller.
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingProducts(StatisticsService $stats, $startDate, $endDate)
    {
        return $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->topItems('product_id', [
                'aggregations' => [
                    'total_sales' => ['count', '*'],
                ],
                'orderBy' => 'total_sales',
                'limit' => 4,
            ]);
    }

    /**
     * Get external traffic sources for products.
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getReferralData(StatisticsService $stats, $startDate, $endDate)
    {
        return $stats->forModel(ProductView::class)
            ->whereHas('product', fn($query) => $query->where('seller_id', authUser()->id))
            ->whereNotNull('referrer')
            ->dateRange($startDate, $endDate)
            ->referrals([
                'field' => 'referrer',
                'limit' => 10,
            ]);
    }

    /**
     * Get human-readable label for the selected period.
     *
     * @param string $period
     * @return string
     */
    private function getPeriodLabel(string $period, string $date_format = 'F Y', string $lifetime = ''): string
    {
        return match ($period) {
            'last_28_days' => 'Last 28 Days',
            'last_90_days' => 'Last 90 Days',
            'last_6_months' => 'Last 6 Months',
            'last_1_year' => 'Last 1 Year',
            'lifetime' => $lifetime,
            default => preg_match('/^\d{4}-\d{2}$/', $period) ? Date::parse($period)->format($date_format) : 'Last 28 Days',
        };
    }

    /**
     * Generate license distribution data (Regular vs Extended).
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function getLicenseDistribution(StatisticsService $stats, $startDate, $endDate): array
    {
        $distribution = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->chart('pie', [
                'aggregation' => 'count',
                'groupBy' => 'license_type',
            ]);

        $distribution['labels'] = array_map(function ($type) {
            $enum = LicenseType::tryFrom((int) $type);
            return $enum ? $enum->label() : $type;
        }, $distribution['labels']);

        return $distribution;
    }

    /**
     * Generate refunds distribution data (Successful vs Refunded).
     *
     * @param StatisticsService $stats
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function getRefundsDistribution(StatisticsService $stats, $startDate, $endDate): array
    {
        $distribution = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->dateRange($startDate, $endDate)
            ->chart('pie', [
                'aggregation' => 'count',
                'groupBy' => 'status',
            ]);

        $distribution['labels'] = array_map(function ($status) {
            $enum = SaleStatus::tryFrom($status);
            return $enum ? $enum->label() : $status;
        }, $distribution['labels']);

        return $distribution;
    }

    /**
     * Get start and end dates for a predefined or custom period.
     *
     * @param string $period
     * @return array [\Carbon\Carbon, \Carbon\Carbon]
     */
    private function getPeriodDates(string $period): array
	{
		switch ($period) {
		    case 'last_28_days':
				$startDate = Date::now()->subDays(28)->startOfDay();
				$endDate = Date::now()->endOfDay();
				break;

			case 'last_90_days':
				$startDate = Date::now()->subDays(90)->startOfDay();
				$endDate = Date::now()->endOfDay();
				break;

			case 'last_6_months':
				$startDate = Date::now()->subMonths(6)->startOfDay();
				$endDate = Date::now()->endOfDay();
				break;

			case 'last_1_year':
				$startDate = Date::now()->subYear()->startOfDay();
				$endDate = Date::now()->endOfDay();
				break;

			case 'lifetime':
				$startDate = Date::now()->subYears(10)->startOfDay(); // Far enough back
				$endDate = Date::now()->endOfDay();
				break;

			default:
				if (preg_match('/^\d{4}-\d{2}$/', $period)) {
					$startDate = Date::parse($period)->startOfMonth();
					$endDate = Date::parse($period)->endOfMonth();
				} else {
					$startDate = Date::now()->subDays(28)->startOfDay();
					$endDate = Date::now()->endOfDay();
				}
				break;
		}

		return [$startDate, $endDate];
	}

    /**
     * Get start and end dates for the period immediately preceding the current selection.
     *
     * @param string $period
     * @return array [\Carbon\Carbon, \Carbon\Carbon]
     */
    private function getPreviousPeriodDates(string $period): array
    {
        [$startDate, $endDate] = $this->getPeriodDates($period);
        $diff = $startDate->diffInDays($endDate) + 1;

        $previousStartDate = (clone $startDate)->subDays($diff);
        $previousEndDate = (clone $endDate)->subDays($diff);

        return [$previousStartDate, $previousEndDate];
    }

    /**
     * Calculate relative percentage change between two values.
     *
     * @param float|int $previous
     * @param float|int $current
     * @return float
     */
    private function calculatePercentageChange($previous, $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Generate aggregate count/sum statistics for the primary performance counters.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function generateCounters($startDate, $endDate): array
    {
        $stats = app(StatisticsService::class);

        // Get sales counters
        $salesCounters = $stats->forModel(Sale::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'total_sales' => ['count', '*'],
                'sales_earnings' => ['sum', DB::raw('seller_earning + IFNULL(JSON_UNQUOTE(JSON_EXTRACT(seller_tax, "$.amount")), 0)')],
            ]);

        // Get referral earnings
        $referralCounters = $stats->forModel(ReferralEarning::class)
            ->where('seller_id', authUser()->id)
            ->scope('active')
            ->dateRange($startDate, $endDate)
            ->counters([
                'referrals_earnings' => ['sum', 'seller_earning'],
            ]);

        // Get total views
        $viewsCounters = $stats->forModel(ProductView::class)
            ->whereHas('product', fn($query) => $query->where('seller_id', authUser()->id))
            ->dateRange($startDate, $endDate)
            ->counters([
                'total_views' => ['count', '*'],
            ]);

        return array_merge($salesCounters, $referralCounters, $viewsCounters);
    }

    /**
     * Generate time-series views chart data.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    private function generateViewsChartData($startDate, $endDate): array
    {
        $stats = app(StatisticsService::class);

        return $stats->forModel(ProductView::class)
            ->whereHas('product', fn($query) => $query->where('seller_id', authUser()->id))
            ->dateRange($startDate, $endDate)
            ->chart('timeSeries', [
                'title' => translate('Views'),
                'dateField' => 'created_at',
                'aggregation' => 'count',
            ]);
    }

    /**
     * Unified search for dashboard navigation and entities.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(): \Illuminate\Http\JsonResponse
    {
        $query = (string) request('query');
        if (empty($query)) return response()->json([]);

        $results = [];

        // 1. Search Menu Items (Hardcoded for performance)
        $menuItems = [
            ['title' => translate('Dashboard'), 'url' => route('user.dashboard'), 'icon' => 'bi-layout-split'],
            ['title' => translate('My Products'), 'url' => route('user.product.index'), 'icon' => 'bi-collection'],
            ['title' => translate('Purchases'), 'url' => route('user.purchase.index'), 'icon' => 'bi-bag-check'],
            ['title' => translate('Transactions'), 'url' => route('user.transaction.index'), 'icon' => 'bi-receipt'],
            ['title' => translate('Payouts'), 'url' => route('user.payout.index'), 'icon' => 'bi-bank'],
            ['title' => translate('Referrals'), 'url' => route('user.referrals'), 'icon' => 'bi-people'],
            ['title' => translate('Refunds'), 'url' => route('user.refund.index'), 'icon' => 'bi-recycle'],
            ['title' => translate('Tickets'), 'url' => route('user.ticket.index'), 'icon' => 'bi-envelope-open'],
            ['title' => translate('Settings'), 'url' => route('user.settings.account'), 'icon' => 'bi-gear'],
        ];

        foreach ($menuItems as $item) {
            if (stripos($item['title'], $query) !== false) {
                $results[] = [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'icon' => $item['icon'],
                    'type' => translate('Menu')
                ];
            }
        }

        // 2. Search User Products
        $products = Product::where('seller_id', authUser()?->id)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($products as $product) {
            $results[] = [
                'title' => $product->name,
                'url' => route('user.product.edit', $product->id),
                'icon' => 'bi-box-seam',
                'type' => translate('Product')
            ];
        }

        // 3. Search Purchases
        $purchases = Purchase::where('user_id', authUser()?->id)
            ->where(function ($q) use ($query) {
                $q->where('code', 'LIKE', "%{$query}%")
                    ->orWhereHas('product', function ($pq) use ($query) {
                        $pq->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->limit(5)
            ->get();

        foreach ($purchases as $purchase) {
            $results[] = [
                'title' => $purchase->product->name . " (#{$purchase->code})",
                'url' => route('user.purchase.index'),
                'icon' => 'bi-bag-check',
                'type' => translate('Purchase')
            ];
        }

        // 4. Search Transactions
        $transactions = Transaction::where('user_id', authUser()?->id)
            ->where(function ($q) use ($query) {
                $q->where('payment_id', 'LIKE', "%{$query}%")
                    ->orWhere('type', 'LIKE', "%{$query}%");
            })
            ->limit(3)
            ->get();

        foreach ($transactions as $trx) {
            $results[] = [
                'title' => $trx->type->label() . " (#{$trx->id})",
                'url' => route('user.transaction.index'),
                'icon' => 'bi-receipt',
                'type' => translate('Transaction')
            ];
        }

        // 5. Search Tickets
        $tickets = Ticket::where('user_id', authUser()?->id)
            ->where('subject', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($tickets as $ticket) {
            $results[] = [
                'title' => $ticket->subject,
                'url' => route('user.ticket.show', $ticket->id),
                'icon' => 'bi-envelope-open',
                'type' => translate('Ticket')
            ];
        }

        // 6. Search Refunds
        $refunds = Refund::where('user_id', authUser()?->id)
            ->whereHas('purchase', function ($q) use ($query) {
                $q->where('code', 'LIKE', "%{$query}%")
                    ->orWhereHas('product', function ($pq) use ($query) {
                        $pq->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->limit(3)
            ->get();

        foreach ($refunds as $refund) {
            $results[] = [
                'title' => translate('Refund for') . " " . $refund->purchase->product->name,
                'url' => route('user.refund.index'),
                'icon' => 'bi-recycle',
                'type' => translate('Refund')
            ];
        }

        return response()->json($results);
    }
}

















