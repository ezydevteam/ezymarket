<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Models\Premium\PremiumEarning;
use App\Models\Premium\Premium;
use App\Models\Product\Product;
use App\Models\Sale;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;
use Exception;

/**
 * Premium Earning Analytics Controller
 *
 * Handles admin analytics and management for premium earning records.
 */
class PremiumEarningController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of premium earnings with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getPremiumEarningCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);

            $query = PremiumEarning::query()->with(['seller', 'product', 'premium.plan']);
            $earningsCount = $query->count();

            // Handle DataTables AJAX requests
            if ($request->ajax() && $request->has('draw')) {
                $this->applyDataTableFilters($query);

                $totalFiltered = $query->count();

                $this->applyDataTableSorting($query);

                $limit = $request->input('length', 10);
                $offset = $request->input('start', 0);
                $earnings = $query->limit($limit)->offset($offset)->get();

                $data = $earnings->map(fn($earning) => $this->formatEarningRow($earning))->toArray();

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $earningsCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.records.premium-earnings.index', compact('counters', 'columns', 'filters', 'earningsCount'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified premium earning modal
     */
    public function detailsModal(PremiumEarning $earning): View
    {
        $earning->load(['seller', 'product', 'premium.plan']);
        return view('admin.records.premium-earnings.modals.details', compact('earning'));
    }

    /**
     * Remove the specified premium earning
     */
    public function destroy(PremiumEarning $premiumEarning): JsonResponse
    {
        $premiumEarning->delete();
        return $this->successJson(translate('Premium earning record deleted successfully'));
    }

    /**
     * Bulk delete premium earnings
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                return PremiumEarning::whereIn('id', $ids)->delete();
            },
            PremiumEarning::class,
            translate(':count premium earning(s) deleted successfully'),
            translate('An error occurred while deleting premium earnings')
        );
    }

    /**
     * Apply DataTables filters
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['earning', 'seller'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('premium_earnings.id', 'like', "%{$search}%")
                    ->orWhere('premium_earnings.name', 'like', "%{$search}%")
                    ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('seller', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters = $request->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '3': // Plan (name)
                        $query->whereHas('premium.plan', fn($pq) => $pq->where('name', $value));
                        break;
                    case '5': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('premium_earnings.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('premium_earnings.created_at', '<=', $value['to']);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply DataTables sorting
     */
    private function applyDataTableSorting($query): void
    {
        $request = request();
        $order = $request->input('order');
        $columns = $this->getDataTableColumns();

        if ($order && isset($order[0])) {
            $columnIdx = $order[0]['column'];
            $dir = $order[0]['dir'];
            $columnName = $columns[$columnIdx]['name'] ?? null;

            if ($columnName) {
                if ($columnName === 'product.name') {
                    $query->join('products', 'premium_earnings.product_id', '=', 'products.id')
                        ->orderBy('products.name', $dir)
                        ->select('premium_earnings.*');
                } elseif ($columnName === 'seller.firstname') {
                    $query->join('users', 'premium_earnings.seller_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('premium_earnings.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a premium earning row for DataTables
     */
    private function formatEarningRow(PremiumEarning $earning): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $earning->id . '">',
            'product' => view('components.product', ['product' => $earning->product, 'showSubCategory' => true])->render(),
            'seller' => view('components.user', ['user' => $earning->seller, 'avatarSize' => 'sm'])->render(),
            'plan' => '<span class="badge bg-primary-subtle text-primary fw-medium">' . ($earning->premium?->plan?->name ?? translate('N/A')) . '</span>',
            'earning' => '<div class="fw-bold text-success">' . getAmount((float) $earning->seller_earning) . '</div>' .
                         '<div class="text-muted small">' . $earning->percentage . '% ' . translate('commission') . '</div>',
            'created_at' => '<div class="text-muted">' . dateFormat($earning->created_at) . '</div>',
            'actions' => view('admin.records.premium-earnings.draw.actions', compact('earning'))->render(),
        ];
    }

    /**
     * Get DataTable columns configuration
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'no-export'],
            ['data' => 'product', 'name' => 'product.name', 'title' => translate('Product'), 'orderable' => true],
            ['data' => 'seller', 'name' => 'seller.firstname', 'title' => translate('Seller'), 'orderable' => true],
            ['data' => 'plan', 'name' => 'premium.plan.name', 'title' => translate('Plan'), 'orderable' => false, 'class' => 'text-center'],
            ['data' => 'earning', 'name' => 'seller_earning', 'title' => translate('Earning'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'exportable' => false, 'class' => 'text-end no-export'],
        ];
    }

    /**
     * Get DataTables filters
     */
    private function getDataTableFilters(Request $request): array
    {
        return [
            [
                'type' => 'daterange',
                'column' => '5',
                'label' => translate('Earning Date'),
                'value' => [
                    'from' => $request->query('from_date'),
                    'to' => $request->query('to_date'),
                ]
            ]
        ];
    }

    /**
     * Apply search and filter criteria to the query (for deep-linking)
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('id')) {
            $query->where('premium_earnings.id', $request->id);
        }

        if ($request->filled('seller')) {
            $query->where('premium_earnings.seller_id', $request->seller);
        }
    }

    /**
     * Calculate premium earning statistics counters
     */
    private function getPremiumEarningCounters(): array
    {
        $premiumProductIds = Product::premium()->approved()->pluck('id');

        $totalCount = PremiumEarning::count();
        $totalAmount = PremiumEarning::sum('seller_earning');
        $premiumProdCount = $premiumProductIds->count();
        $premiumProdAmount = Sale::whereIn('product_id', $premiumProductIds)->sum('price');
        $premiumSellersCount = Product::premium()->approved()->distinct('seller_id')->count('seller_id');
        $premiumSalesCount = PremiumEarning::count();
        $premiumMembersCount = Premium::active()->count();

        $counters = [
            'total' => [
                'total' => $totalCount,
                'amount' => $totalAmount,
            ],
            'premium_products' => [
                'total' => $premiumProdCount,
                'amount' => $premiumProdAmount,
            ],
            'premium_sellers' => [
                'total' => $premiumSellersCount,
                'sales' => $premiumSalesCount,
            ],
            'premium_members' => [
                'total' => $premiumMembersCount,
            ],
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);

        $prevTotal = PremiumEarning::where('created_at', '<', $lastWeekStart)->count();
        $prevProdCount = Product::premium()->approved()->where('created_at', '<', $lastWeekStart)->count();
        $prevSellersCount = Product::premium()->approved()->where('created_at', '<', $lastWeekStart)->distinct('seller_id')->count('seller_id');
        $prevMembersCount = Premium::active()->where('created_at', '<', $lastWeekStart)->count();

        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $counters['total']['percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['premium_products']['percent'] = $calculatePercent($premiumProdCount, $prevProdCount);
        $counters['premium_sellers']['percent'] = $calculatePercent($premiumSellersCount, $prevSellersCount);
        $counters['premium_members']['percent'] = $calculatePercent($premiumMembersCount, $prevMembersCount);

        return $counters;
    }
}
