<?php

namespace App\Http\Controllers\UserPanel;

use App\Enums\{TransactionStatus, TransactionType};
use App\Http\Controllers\Controller;
use App\Models\Financial\Transaction;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class TransactionController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function index(): View|JsonResponse
    {
        $user = authUser();
        $query = Transaction::where('user_id', $user->id)
            ->whereNot('status', TransactionStatus::UNPAID);

        // Filter out premium memberships if not available
        if (!isPremiumAvailable()) {
            $query->whereNot('type', TransactionType::PREMIUM);
        }

        // Handle DataTables AJAX requests
        if (request()->ajax() && request()->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = request()->input('start', 0);
                $length = request()->input('length', 10);
                $transactions = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $transactions->map(fn($trx) => $this->formatTransactionRow($trx));

                return response()->json([
                    'draw' => intval(request()->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (\Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $transactions = collect([]);
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $hasRecords = $user->transactions()->exists();

        return theme_view('userpanel.transactions.index', compact('transactions', 'columns', 'filters', 'hasRecords'));
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return View|string
     */
    public function show(string $id): View|string
    {
        $trx = Transaction::where('id', $id)
            ->where('user_id', authUser()->id)
            ->whereNot('status', TransactionStatus::UNPAID)
            ->with('paymentGateway')
            ->firstOrFail();

        // Abort if premium type and premium not available
        abort_if(
            $trx->type === TransactionType::PREMIUM && !isPremiumAvailable(),
            404
        );

        if (request()->ajax()) {
            return theme_view('userpanel.transactions.partials.modal_details', compact('trx'))->render();
        }

        return theme_view('userpanel.transactions.show', compact('trx'));
    }

    /**
     * Display the invoice of the specified resource.
     *
     * @param string $id
     * @return View
     */
    public function invoice(string $id): View
    {
        $trx = Transaction::where('id', $id)
            ->where('user_id', authUser()->id)
            ->where('status', TransactionStatus::PAID)
            ->firstOrFail();

        // Abort if premium type and premium not available
        abort_if(
            $trx->type === TransactionType::PREMIUM && !isPremiumAvailable(),
            404
        );

        return theme_view('userpanel.transactions.invoice', compact('trx'));
    }

    /**
     * Remove the transaction record (Soft Delete).
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $trx = Transaction::where('id', $id)
            ->where('user_id', authUser()->id)
            ->firstOrFail();

        if ($trx->isPending()) {
            return $this->errorJson('Pending transactions cannot be deleted.');
        }

        $trx->delete();

        return $this->successJson('Transaction record has been deleted successfully.');
    }

    /**
     * Apply filter and search logic to the transaction query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableFilters(Builder $query): void
    {
        // Global search filter
        if ($search = request()->input('search.value')) {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('id', 'like', "%{$cleanSearch}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('fees', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhereHas('paymentGateway', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });

                // Search in Enum Labels (Type)
                foreach (TransactionType::cases() as $type) {
                    if (str_contains(strtolower($type->label()), strtolower($search))) {
                        $q->orWhere('type', $type->value);
                    }
                }

                // Search in Enum Labels (Status)
                foreach (TransactionStatus::cases() as $status) {
                    if (str_contains(strtolower($status->label()), strtolower($search))) {
                        $q->orWhere('status', $status->value);
                    }
                }
            });
        }

        // Column-specific AJAX filters
        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if (!$value) continue;

                if ($column == '5') { // Type Column
                    $query->where('type', $value);
                } elseif ($column == '6') { // Status Column
                    $query->where('status', $value);
                } elseif ($column == '7') { // Date Range Column
                    if (is_array($value)) {
                        if (!empty($value['from']) && strtotime($value['from'])) {
                            $query->whereDate('created_at', '>=', $value['from']);
                        }
                        if (!empty($value['to']) && strtotime($value['to'])) {
                            $query->whereDate('created_at', '<=', $value['to']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Apply sorting to the transaction query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableSorting(Builder $query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'id',
            1 => 'amount',
            2 => 'tax',
            3 => 'fees',
            4 => 'total',
            5 => 'type',
            6 => 'status',
            7 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 7;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Format a single transaction row for the DataTables AJAX response.
     *
     * @param Transaction $trx
     * @return array
     */
    private function formatTransactionRow(Transaction $trx): array
    {
        return [
            'details' => theme_view('userpanel.transactions.partials.row_details', compact('trx'))->render(),
            'amount'  => '<span class="text-dark">' . getAmount($trx->amount) . '</span>',
            'tax'     => '<span class="text-gray-700">' . getAmount($trx->hasTax() ? ($trx->tax->amount ?? 0) : 0) . '</span>',
            'fees'    => '<span class="text-gray-700">' . getAmount($trx->fees) . '</span>',
            'total'   => '<span class="text-dark fw-bold">' . getAmount($trx->total) . '</span>',
            'type'    => $trx->type_badge,
            'status'  => '<span role="button" data-bs-toggle="modal" data-bs-target="#transactionDetailsModal" data-action="' . route('user.transaction.show', $trx->id) . '">' . $trx->status_badge . '</span>',
            'date'    => '<span class="text-muted">' . dateFormat($trx->created_at) . '</span>',
            'actions' => theme_view('userpanel.transactions.partials.row_actions', compact('trx'))->render()
        ];
    }

    /**
     * Get columns for the DataTables
     *
     * @return array
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'details', 'name' => 'id', 'title' => translate('Transaction'), 'orderable' => true, 'searchable' => true],
            ['data' => 'amount', 'name' => 'amount', 'title' => translate('SubTotal'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'tax', 'name' => 'tax', 'title' => translate('Tax'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'fees', 'name' => 'fees', 'title' => translate('Fees'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'total', 'name' => 'total', 'title' => translate('Total'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'type', 'name' => 'type', 'title' => translate('Type'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'date', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters for the DataTables
     *
     * @return array
     */
    private function getDataTableFilters(): array
    {
        $typeOptions = Transaction::getTypeOptions();
        $statusOptions = Transaction::getStatusOptions();

        return [
            [
                'type' => 'select',
                'column' => 5,
                'label' => translate('Type'),
                'options' => array_map(fn($key, $val) => ['value' => (string) $key, 'label' => $val],
                        array_keys($typeOptions), array_values($typeOptions))
            ],
            [
                'type' => 'select',
                'column' => 6,
                'label' => translate('Status'),
                'options' => array_map(fn($key, $val) => ['value' => (string) $key, 'label' => $val],
                        array_keys($statusOptions), array_values($statusOptions))
            ],
            [
                'type' => 'daterange',
                'column' => 7,
                'label' => translate('Date Range')
            ]
        ];
    }
}
