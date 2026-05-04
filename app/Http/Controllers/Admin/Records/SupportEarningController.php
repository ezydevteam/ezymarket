<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\SupportEarningStatus;
use App\Models\Support\SupportEarning;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * Support Earning Analytics Controller
 *
 * Handles admin analytics and management for support earning records.
 */
class SupportEarningController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of support earnings with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getSupportEarningCounters();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        $query = SupportEarning::query()->with(['seller', 'purchase.product']);
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

        return view('admin.records.support-earnings.index', compact('counters', 'columns', 'filters', 'earningsCount'));
    }

    /**
     * Apply DataTables filters.
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['id', 'seller', 'purchase'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('support_earnings.id', 'like', "%{$search}%")
                    ->orWhereHas('purchase.product', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
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
                    case '3': // Status
                        $query->where('support_earnings.status', $value);
                        break;
                    case '4': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('support_earnings.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('support_earnings.created_at', '<=', $value['to']);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply DataTables sorting.
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
                if ($columnName === 'seller.firstname') {
                    $query->join('users', 'support_earnings.seller_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('support_earnings.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format an earning row for DataTables.
     */
    private function formatEarningRow(SupportEarning $earning): array
    {
        return [
            'purchase' => view('components.product', ['product' => $earning->purchase->product, 'showSubCategory' => true])->render(),
            'seller' => view('components.user', ['user' => $earning->seller, 'avatarSize' => 'sm', 'fontWeight' => 'normal'])->render(),
            'amount' => '<div class="fw-bold text-dark">' . getAmount((float) $earning->seller_earning) . '</div>',
            'status' => $earning->status->badge(),
            'created_at' => '<div class="text-muted">' . dateFormat($earning->created_at) . '</div>',
            'actions' => view('admin.records.support-earnings.draw.actions', compact('earning'))->render(),
        ];
    }

    /**
     * Get DataTable columns configuration.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'purchase', 'name' => 'purchase_id', 'title' => translate('Related Product'), 'orderable' => true],
            ['data' => 'seller', 'name' => 'seller.firstname', 'title' => translate('Seller'), 'orderable' => true],
            ['data' => 'amount', 'name' => 'seller_earning', 'title' => translate('Amount'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'exportable' => false, 'class' => 'text-end no-export'],
        ];
    }

    /**
     * Get DataTables filters.
     */
    private function getDataTableFilters(Request $request): array
    {
        return [
            [
                'type' => 'select',
                'column' => '3',
                'label' => translate('Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], SupportEarningStatus::cases()),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '4',
                'label' => translate('Earning Date'),
                'value' => [
                    'from' => $request->query('from_date'),
                    'to' => $request->query('to_date'),
                ]
            ]
        ];
    }

    /**
     * Display the support earning details modal.
     */
    public function detailsModal(SupportEarning $earning): View
    {
        return view('admin.records.support-earnings.modals.details', compact('earning'));
    }

    /**
     * Apply search and filter criteria to the query (for deep-linking)
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('id')) {
            $query->where('support_earnings.id', $request->id);
        }

        if ($request->filled('status')) {
            $query->where('support_earnings.status', $request->status);
        }

        if ($request->filled('seller')) {
            $query->where('support_earnings.seller_id', $request->seller);
        }

        if ($request->filled('purchase')) {
            $query->where('support_earnings.purchase_id', $request->purchase);
        }
    }

    /**
     * Calculate support earning statistics counters
     */
    private function getSupportEarningCounters(): array
    {
        // Current counts
        $totalCount = SupportEarning::count();
        $activeCount = SupportEarning::where('status', SupportEarningStatus::ACTIVE)->count();
        $refundedCount = SupportEarning::where('status', SupportEarningStatus::REFUNDED)->count();
        $cancelledCount = SupportEarning::where('status', SupportEarningStatus::CANCELLED)->count();

        $counters = [
            'total' => [
                'total' => $totalCount,
                'amount' => SupportEarning::sum('seller_earning'),
            ],
            'active' => [
                'total' => $activeCount,
                'amount' => SupportEarning::where('status', SupportEarningStatus::ACTIVE)->sum('seller_earning'),
            ],
            'refunded' => [
                'total' => $refundedCount,
                'amount' => SupportEarning::where('status', SupportEarningStatus::REFUNDED)->sum('seller_earning'),
            ],
            'cancelled' => [
                'total' => $cancelledCount,
                'amount' => SupportEarning::where('status', SupportEarningStatus::CANCELLED)->sum('seller_earning'),
            ],
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);

        $prevTotal = SupportEarning::where('created_at', '<', $lastWeekStart)->count();
        $prevActive = SupportEarning::where('status', SupportEarningStatus::ACTIVE)->where('created_at', '<', $lastWeekStart)->count();
        $prevRefunded = SupportEarning::where('status', SupportEarningStatus::REFUNDED)->where('created_at', '<', $lastWeekStart)->count();
        $prevCancelled = SupportEarning::where('status', SupportEarningStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $counters['total']['percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['active']['percent'] = $calculatePercent($activeCount, $prevActive);
        $counters['refunded']['percent'] = $calculatePercent($refundedCount, $prevRefunded);
        $counters['cancelled']['percent'] = $calculatePercent($cancelledCount, $prevCancelled);

        return $counters;
    }
}
