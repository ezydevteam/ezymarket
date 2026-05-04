<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\PurchaseStatus;
use App\Traits\HandlesValidation;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;


/**
 * Purchase Analytics Controller
 *
 * Handles admin analytics and management for purchase records.
 */
class PurchaseController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of purchases with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getPurchaseCounters();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        $query = Purchase::query()->with(['product', 'user', 'sale']);
        $purchasesCount = $query->count();

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            $this->applyDataTableFilters($query);

            $totalFiltered = $query->count();

            $this->applyDataTableSorting($query);

            $limit = $request->input('length', 10);
            $offset = $request->input('start', 0);
            $purchases = $query->limit($limit)->offset($offset)->get();

            $data = $purchases->map(fn($purchase) => $this->formatPurchaseRow($purchase))->toArray();

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $purchasesCount,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        }

        return view('admin.records.purchases.index', compact('counters', 'columns', 'filters', 'purchasesCount'));
    }

    /**
     * Display the purchase details modal.
     */
    public function detailsModal(Purchase $purchase): View
    {
        return view('admin.records.purchases.modals.details', compact('purchase'));
    }

    /**
     * Apply DataTables filters.
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['purchase', 'product', 'user'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchases.id', 'like', "%{$search}%")
                    ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($uq) use ($search) {
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
                        $query->where('purchases.status', $value);
                        break;
                    case '4': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('purchases.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('purchases.created_at', '<=', $value['to']);
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

        if ($request->hasAny(['id', 'product', 'user'])) {
            $this->applyFilters($query);
        }

        $order = $request->input('order');
        $columns = $this->getDataTableColumns();

        if ($order && isset($order[0])) {
            $columnIdx = $order[0]['column'];
            $dir = $order[0]['dir'];
            $columnName = $columns[$columnIdx]['name'] ?? null;

            if ($columnName) {
                if ($columnName === 'product.name') {
                    $query->join('products', 'purchases.product_id', '=', 'products.id')
                        ->orderBy('products.name', $dir)
                        ->select('purchases.*');
                } elseif ($columnName === 'user.firstname') {
                    $query->join('users', 'purchases.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('purchases.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a purchase row for DataTables.
     */
    private function formatPurchaseRow(Purchase $purchase): array
    {
        return [
            'product' => view('components.product', ['product' => $purchase->product, 'showSubCategory' => true])->render(),
            'buyer' => view('components.user', ['user' => $purchase->user, 'avatarSize' => 'sm'])->render(),
            'price' => '<div class="fw-bold text-dark">' . getAmount((float) ($purchase->sale?->price ?? 0)) . '</div>',
            'status' => '<span class="status-badge ' . $purchase->status_badge_class . '">' . $purchase->status_name . '</span>',
            'created_at' => '<div class="text-muted">' . dateFormat($purchase->created_at) . '</div>',
            'actions' => view('admin.records.purchases.draw.actions', compact('purchase'))->render(),
        ];
    }

    /**
     * Get DataTable columns configuration.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'product', 'name' => 'product.name', 'title' => translate('Product'), 'orderable' => true],
            ['data' => 'buyer', 'name' => 'user.firstname', 'title' => translate('Buyer'), 'orderable' => true],
            ['data' => 'price', 'name' => 'sale.price', 'title' => translate('Price'), 'orderable' => true, 'class' => 'text-center'],
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
                'label' => translate('Purchase Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], PurchaseStatus::cases()),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '4',
                'label' => translate('Purchase Date'),
                'value' => [
                    'from' => $request->query('from_date'),
                    'to' => $request->query('to_date'),
                ]
            ]
        ];
    }

    /**
     * Apply old-style filters (for backward compatibility and deep-linking).
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('id')) {
            $query->where('purchases.id', $request->id);
        }

        if ($request->filled('status')) {
            $query->where('purchases.status', $request->status);
        }

        if ($request->filled('user')) {
            $query->where('purchases.user_id', $request->user);
        }

        if ($request->filled('product')) {
            $query->where('purchases.product_id', $request->product);
        }
    }

    /**
     * Calculate purchase statistics counters
     */
    private function getPurchaseCounters(): array
    {
        // Current counts
        $total = Purchase::count();
        $active = Purchase::where('status', PurchaseStatus::ACTIVE)->count();
        $refunded = Purchase::where('status', PurchaseStatus::REFUNDED)->count();
        $cancelled = Purchase::where('status', PurchaseStatus::CANCELLED)->count();

        $counters = [
            'total' => [
                'total' => $total,
                'amount' => Purchase::join('sales', 'purchases.sale_id', '=', 'sales.id')->sum('sales.price'),
            ],
            'active' => [
                'total' => $active,
                'amount' => Purchase::where('purchases.status', PurchaseStatus::ACTIVE)
                    ->join('sales', 'purchases.sale_id', '=', 'sales.id')
                    ->sum('sales.price'),
            ],
            'refunded' => [
                'total' => $refunded,
                'amount' => Purchase::where('purchases.status', PurchaseStatus::REFUNDED)
                    ->join('sales', 'purchases.sale_id', '=', 'sales.id')
                    ->sum('sales.price'),
            ],
            'cancelled' => [
                'total' => $cancelled,
                'amount' => Purchase::where('purchases.status', PurchaseStatus::CANCELLED)
                    ->join('sales', 'purchases.sale_id', '=', 'sales.id')
                    ->sum('sales.price'),
            ],
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);

        $prevTotal = Purchase::where('created_at', '<', $lastWeekStart)->count();
        $prevActive = Purchase::where('status', PurchaseStatus::ACTIVE)->where('created_at', '<', $lastWeekStart)->count();
        $prevRefunded = Purchase::where('status', PurchaseStatus::REFUNDED)->where('created_at', '<', $lastWeekStart)->count();
        $prevCancelled = Purchase::where('status', PurchaseStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $counters['total']['percent'] = $calculatePercent($total, $prevTotal);
        $counters['active']['percent'] = $calculatePercent($active, $prevActive);
        $counters['refunded']['percent'] = $calculatePercent($refunded, $prevRefunded);
        $counters['cancelled']['percent'] = $calculatePercent($cancelled, $prevCancelled);

        return $counters;
    }
}
