<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\{SaleStatus, LicenseType};
use App\Events\SaleCancelled;
use App\Models\Sale;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};

/**
 * Sale Controller
 *
 * Manages sale analytics and operations in the admin panel.
 *
 * @package App\Http\Controllers\Admin\Records
 */
class SaleController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of sales with filters.
     *
     * @return View
     */
    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getSalesCounters();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        $query = Sale::query()->with(['product', 'user', 'seller']);
        $salesCount = $query->count();

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $this->applyDataTableFilters($query);

                $totalRecords = (clone $query)->count();

                $this->applyDataTableSorting($query);

                // Paginate
                $length = (int) $request->input('length', 10);
                $start = (int) $request->input('start', 0);
                $sales = $query->skip($start)->take($length)->get();

                $data = $sales->map(fn($sale) => $this->formatSaleRow($sale));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data,
                ]);
            } catch (Exception $e) {
                return $this->errorJson('Error fetching sales: ' . $e->getMessage(), [], 500);
            }
        }

        return view('admin.records.sales.index', compact('counters', 'columns', 'filters', 'salesCount'));
    }

    /**
     * Cancel the specified sale.
     *
     * @param Sale $sale
     * @return JsonResponse
     */
    public function cancel(Sale $sale): JsonResponse
    {
        abort_if(!$sale->isActive(), 404);

        event(new SaleCancelled($sale));

        return $this->successJson('Sale cancelled successfully');
    }

    /**
     * Remove the specified sale.
     *
     * @param Sale $sale
     * @return JsonResponse
     */
    public function destroy(Sale $sale): JsonResponse
    {
        // Only decrement if the sale was active (counters currently include it)
        try {
            if ($sale->isActive()) {
                $product = $sale->product;
                if ($product) {
                    $product->decrement('total_sales');

                    $taxAmount = 0;
                    if (isset($sale->seller_tax) && is_object($sale->seller_tax)) {
                        $taxAmount = (float) ($sale->seller_tax->amount ?? 0);
                    } elseif (isset($sale->seller_tax['amount'])) {
                        $taxAmount = (float) $sale->seller_tax['amount'];
                    }

                    $product->decrement('total_earnings', (float) $sale->seller_earning + $taxAmount);
                }
            }

            $sale->delete();

            return $this->successJson('Sale deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson('Error deleting sale');
        }
    }

     /**
     * Bulk cancel sales.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkCancel(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $sales = Sale::whereIn('id', $ids)->get();

                $salesToCancel = $sales->filter(function ($sale) {
                    return $sale->isActive();
                });

                if ($salesToCancel->isEmpty()) {
                    throw new Exception(translate('No valid sales to cancel'));
                }

                foreach ($salesToCancel as $sale) {
                    event(new SaleCancelled($sale));
                }

                return $salesToCancel->count();
            },
            Sale::class,
            ':count sale(s) cancelled successfully',
            'An error occurred while cancelling sales'
        );
    }

    /**
     * Bulk delete sales.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $sales = Sale::whereIn('id', $ids)->get();
                $count = 0;

                foreach ($sales as $sale) {
                    // Only decrement if the sale was active (counters currently include it)
                    if ($sale->isActive()) {
                        $product = $sale->product;
                        if ($product) {
                            $product->decrement('total_sales');

                            $taxAmount = 0;
                            if (isset($sale->seller_tax) && is_object($sale->seller_tax)) {
                                $taxAmount = (float) ($sale->seller_tax->amount ?? 0);
                            } elseif (isset($sale->seller_tax['amount'])) {
                                $taxAmount = (float) $sale->seller_tax['amount'];
                            }

                            $product->decrement('total_earnings', (float) $sale->seller_earning + $taxAmount);
                        }
                    }

                    if ($sale->delete()) {
                        $count++;
                    }
                }

                return $count;
            },
            Sale::class,
            ':count sale(s) deleted successfully',
            'An error occurred while deleting sales'
        );
    }

    /**
     * Apply filters to the sale query.
     *
     * @param $query
     * @return void
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller')) {
            $query->where('seller_id', $request->seller);
        }

        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }
    }

    /**
     * Display the sale details modal.
     */
    public function detailsModal(Sale $sale): View
    {
        return view('admin.records.sales.modals.details', compact('sale'));
    }

    /**
     * Format a single sale model row for DataTables.
     */
    private function formatSaleRow(Sale $sale): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $sale->id . '">',
            'product' => view('components.product', ['product' => $sale->product, 'showSubCategory' => true])->render(),
            'buyer' => view('components.user', ['user' => $sale->seller, 'avatarSize' => 'sm', 'fontWeight' => 'normal'])->render(),
            'price' => '<strong class="text-success">' . getAmount($sale->price) . '</strong>',
            'earnings' => '<strong class="text-primary">' . getAmount($sale->seller_earning) . '</strong>',
            'license' => '<span class="status-badge ' . $sale->license_type_badge_class . '">' . $sale->license_type_short_name . '</span>',
            'status' => '<span class="status-badge ' . $sale->status_badge_class . '">' . $sale->status_name . '</span>',
            'created_at' => '<span class="text-muted">' . dateFormat($sale->created_at) . '</span>',
            'actions' => view('admin.records.sales.draw.actions', ['sale' => $sale])->render()
        ];
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'product', 'name' => 'product.name', 'title' => translate('Product'), 'orderable' => true, 'searchable' => true],
            ['data' => 'buyer', 'name' => 'user.firstname', 'title' => translate('Seller'), 'orderable' => true, 'searchable' => true],
            ['data' => 'price', 'name' => 'price', 'title' => translate('Price'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'earnings', 'name' => 'seller_earning', 'title' => translate('Seller Earnings'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'license', 'name' => 'license_type', 'title' => translate('License'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'text-end export-ignore no-export'],
        ];
    }

    /**
     * Get filters configuration for the Datatable.
     */
    private function getDataTableFilters(Request $request): array
    {
        return [
            [
                'type' => 'select',
                'column' => '6', // Status column index
                'label' => translate('Sale Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], SaleStatus::cases()),
                'value' => $request->query('status')
            ],
            [
                'type' => 'select',
                'column' => '5', // License column index
                'label' => translate('License Type'),
                'options' => array_map(fn($license) => ['value' => $license->value, 'label' => $license->label()], LicenseType::cases()),
            ],
            [
                'type' => 'daterange',
                'column' => '7', // Date column index
                'label' => translate('Date Range'),
            ]
        ];
    }

    /**
     * Apply filters to the query for DataTables.
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['id', 'product', 'seller'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters = $request->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '6': // Status
                        $query->where('status', $value);
                        break;
                    case '5': // License
                        $query->where('license_type', $value);
                        break;
                    case '7': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('created_at', '<=', $value['to']);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to the query for DataTables.
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
                    $query->join('products', 'sales.product_id', '=', 'products.id')
                        ->orderBy('products.name', $dir)
                        ->select('sales.*');
                } elseif ($columnName === 'user.firstname') {
                    $query->join('users', 'sales.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('sales.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Calculate status counters from sales.
     */
    private function getSalesCounters(): array
    {
        // Current counts
        $counters['total'] = [
            'total' => Sale::count(),
            'amount' => Sale::sum('price'),
        ];
        $counters['active'] = [
            'total' => Sale::where('status', SaleStatus::ACTIVE)->count(),
            'amount' => Sale::where('status', SaleStatus::ACTIVE)->sum('price'),
        ];
        $counters['refunded'] = [
            'total' => Sale::where('status', SaleStatus::REFUNDED)->count(),
            'amount' => Sale::where('status', SaleStatus::REFUNDED)->sum('price'),
        ];
        $counters['cancelled'] = [
            'total' => Sale::where('status', SaleStatus::CANCELLED)->count(),
            'amount' => Sale::where('status', SaleStatus::CANCELLED)->sum('price'),
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);

        $prevTotalCount = Sale::where('created_at', '<', $lastWeekStart)->count();
        $prevActiveCount = Sale::where('status', SaleStatus::ACTIVE)->where('created_at', '<', $lastWeekStart)->count();
        $prevRefundedCount = Sale::where('status', SaleStatus::REFUNDED)->where('created_at', '<', $lastWeekStart)->count();
        $prevCancelledCount = Sale::where('status', SaleStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        $counters['total']['percent'] = $prevTotalCount > 0 ? round((($counters['total']['total'] - $prevTotalCount) / $prevTotalCount) * 100) : ($counters['total']['total'] > 0 ? 100 : 0);
        $counters['active']['percent'] = $prevActiveCount > 0 ? round((($counters['active']['total'] - $prevActiveCount) / $prevActiveCount) * 100) : ($counters['active']['total'] > 0 ? 100 : 0);
        $counters['refunded']['percent'] = $prevRefundedCount > 0 ? round((($counters['refunded']['total'] - $prevRefundedCount) / $prevRefundedCount) * 100) : ($counters['refunded']['total'] > 0 ? 100 : 0);
        $counters['cancelled']['percent'] = $prevCancelledCount > 0 ? round((($counters['cancelled']['total'] - $prevCancelledCount) / $prevCancelledCount) * 100) : ($counters['cancelled']['total'] > 0 ? 100 : 0);

        return $counters;
    }
}
