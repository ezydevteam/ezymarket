<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Enums\{TransactionStatus, TransactionType};
use App\Events\TransactionPaid;
use App\Facades\Notification;
use App\Models\Financial\Transaction;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, Response, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\Storage;
use Exception;

class TransactionController extends Controller
{
    use HandlesValidation, HandlesSorting;

    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getTransactionCounters();
        $query = Transaction::withUserTrashed()->whereNot('status', TransactionStatus::UNPAID)->with('user');

        if (!isPremiumAvailable()) {
            $query->whereNot('type', TransactionType::PREMIUM);
        }

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $transactions = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $transactions->map(fn($trx) => $this->formatTransactionRow($trx));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);
        $trashedCount = Transaction::onlyAdminTrashed()->count();
        $trxCount = Transaction::withUserTrashed()->count();

        return view('admin.financial.transactions.index', compact('counters', 'columns', 'filters', 'trashedCount', 'trxCount'));
    }

    /**
     * Display the payment proof file
     */
    public function paymentProof(Transaction $transaction): Response
    {
        $this->checkPremiumAccess($transaction);

        abort_if(!$transaction->payment_proof, 404);

        try {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            $file = $disk->get($transaction->payment_proof);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $disk->mimeType($transaction->payment_proof));
            return $response;
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Mark transaction as paid
     */
    public function paid(Request $request, Transaction $transaction): JsonResponse
    {
        $this->checkPremiumAccess($transaction);

        try {
            if ($transaction->isPending()) {
                $updated = Transaction::where('id', $transaction->id)
                    ->where('status', TransactionStatus::PENDING)
                    ->update(['status' => TransactionStatus::PAID]);

                if ($updated) {
                    $transaction->refresh();
                    event(new TransactionPaid($transaction));
                    return $this->successJson('Transaction has been paid successfully');
                }
            }
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }

        return $this->errorJson('Failed to mark transaction as paid');
    }

    /**
     * Cancel transaction with reason
     */
    public function cancel(Request $request, Transaction $transaction): JsonResponse
    {
        $this->checkPremiumAccess($transaction);

        $validator = $this->validateRequestJson($request, [
            'reason' => ['required', 'string', 'block_patterns', 'max:255'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            if (!$transaction->isCancelled()) {
                $transaction->update([
                    'reason' => $request->reason,
                    'status' => TransactionStatus::CANCELLED,
                ]);

                Notification::sendTransactionCancelledNotification($transaction);

                return $this->successJson('Transaction has been cancelled successfully');
            }
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }

        return $this->errorJson('Failed to cancel transaction');
    }

    /**
     * Mark multiple transactions as paid
     */
    public function bulkPaid(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $transactions = Transaction::whereIn('id', $ids)->get();

                $transactionsToPay = $transactions->filter(function ($transaction) {
                    return $transaction->isPending();
                });

                if ($transactionsToPay->isEmpty()) {
                    throw new \Exception(translate('No unpaid transactions found to mark as paid'));
                }

                $successCount = 0;
                foreach ($transactionsToPay as $transaction) {
                    $updated = Transaction::where('id', $transaction->id)
                        ->where('status', TransactionStatus::PENDING)
                        ->update(['status' => TransactionStatus::PAID]);

                    if ($updated) {
                        $transaction->refresh();
                        event(new TransactionPaid($transaction));
                        $successCount++;
                    }
                }

                return $successCount;
            },
            Transaction::class,
            ':count of :total transaction(s) have been marked as paid successfully',
            'Failed to mark transactions as paid'
        );
    }

    /**
     * Cancel multiple transactions
     */
    public function bulkCancel(Request $request): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($request) {
                $transactions = Transaction::whereIn('id', $ids)->get();

                $transactionsToCancel = $transactions->filter(function ($transaction) {
                    return !$transaction->isCancelled();
                });

                if ($transactionsToCancel->isEmpty()) {
                    throw new \Exception(translate('No active transactions found to cancel'));
                }

                $successCount = 0;
                foreach ($transactionsToCancel as $transaction) {
                    $updated = Transaction::where('id', $transaction->id)
                        ->where('status', '!=', TransactionStatus::CANCELLED)
                        ->update([
                            'reason' => $request->rejection_reason,
                            'status' => TransactionStatus::CANCELLED,
                        ]);

                    if ($updated) {
                        $transaction->refresh();
                        Notification::sendTransactionCancelledNotification($transaction);
                        $successCount++;
                    }
                }

                return $successCount;
            },
            Transaction::class,
            ':count of :total transaction(s) have been cancelled successfully',
            'Failed to cancel transactions'
        );
    }

    /**
     * Delete multiple transactions
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $transactions = Transaction::withTrashed()->whereIn('id', $ids)->get();

                if ($transactions->isEmpty()) {
                    throw new \Exception(translate('No transactions found to delete'));
                }

                $successCount = 0;
                foreach ($transactions as $transaction) {
                    if ($transaction->trashed()) {
                        if ($transaction->isArchivedByAdmin()) {
                            $transaction->forceDelete();
                        } else {
                            $transaction->moveToAdminTrash();
                        }
                    } else {
                        $transaction->delete();
                    }
                    $successCount++;
                }

                return $successCount;
            },
            Transaction::class,
            ':count transaction(s) processed successfully',
            'Failed to delete transactions'
        );
    }

    /**
     * Delete the transaction
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            $this->checkPremiumAccess($transaction);

            if ($transaction->trashed()) {
                if ($transaction->isArchivedByAdmin()) {
                    $transaction->forceDelete();
                    return $this->successJson('Transaction permanently deleted successfully');
                }

                $transaction->moveToAdminTrash();
                return $this->successJson('Transaction deleted successfully');
            }

            $transaction->delete();
            return $this->successJson('Transaction deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Restore multiple deleted transactions.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $transactions = Transaction::onlyTrashed()->whereIn('id', $ids)->get();

                if ($transactions->isEmpty()) {
                    throw new \Exception(translate('No trashed transactions found to restore'));
                }

                $successCount = 0;
                foreach ($transactions as $transaction) {
                    $transaction->restore();
                    $successCount++;
                }

                return $successCount;
            },
            Transaction::class,
            ':count of :total transaction(s) have been restored successfully',
            'Failed to restore transactions'
        );
    }

    /**
     * Restore a deleted transaction.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $transaction = Transaction::onlyTrashed()->findOrFail($id);
            $transaction->restore();

            return $this->successJson('Transaction restored successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Display trashed (soft deleted) transactions.
     */
    public function trash(Request $request): View|JsonResponse
    {
        $counters = $this->getTransactionCounters();

        $query = Transaction::onlyAdminTrashed()->whereNot('status', TransactionStatus::UNPAID)->with('user');

        if (!isPremiumAvailable()) {
            $query->whereNot('type', TransactionType::PREMIUM);
        }

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $transactions = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $transactions->map(fn($trx) => $this->formatTransactionRow($trx));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        return view('admin.financial.transactions.trash', compact('counters', 'columns', 'filters'));
    }

    /**
     * Display the specified transaction in a modal.
     */
    public function detailsModal(Transaction $transaction): string
    {
        $this->checkPremiumAccess($transaction);
        $transaction->load(['user', 'paymentGateway', 'trxProducts.product', 'premiumPlan']);

        return view('admin.financial.transactions.modals.modal_details', ['trx' => $transaction])->render();
    }

    /**
     * Permanently delete the transaction.
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        try {
            $transaction = Transaction::onlyTrashed()->findOrFail($id);
            $transaction->forceDelete();

            return $this->successJson('Transaction permanently deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'user', 'name' => 'user.firstname', 'title' => translate('User'), 'orderable' => true, 'searchable' => true],
            ['data' => 'amount', 'name' => 'amount', 'title' => translate('Amount'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'tax', 'name' => 'tax_id', 'title' => translate('Tax'), 'orderable' => false, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'fees', 'name' => 'fees', 'title' => translate('Fees'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'total', 'name' => 'total', 'title' => translate('Total'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'type', 'name' => 'type', 'title' => translate('Type'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters configuration for the Datatable.
     * Get filters configuration for the Datatable.
     */
    private function getDataTableFilters(Request $request): array
    {
        return [
            [
                'type' => 'select',
                'column' => '6', // Type column index
                'label' => translate('Transaction Type'),
                'options' => array_map(fn($type) => ['value' => $type->value, 'label' => $type->label()], TransactionType::cases()),
                'value' => $request->query('type')
            ],
            [
                'type' => 'select',
                'column' => '7', // Status column index
                'label' => translate('Transaction Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], [
                    TransactionStatus::PENDING,
                    TransactionStatus::PAID,
                    TransactionStatus::CANCELLED
                ]),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '8', // Date column index
                'label' => translate('Transaction Date'),
                'value' => [
                    'from' => $request->query('from'),
                    'to' => $request->query('to')
                ]
            ]
        ];
    }

    /**
     * Format a single transaction model row for DataTables.
     */
    private function formatTransactionRow(Transaction $trx): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $trx->id . '">',
            'user' => view('components.user', ['user' => $trx->user, 'avatarSize' => 'sm', 'fontWeight' => 'normal'])->render(),
            'amount' => '<span class="text-dark">' . getAmount($trx->amount) . '</span>',
            'tax' => '<span class="text-dark">' . getAmount($trx->hasTax() ? $trx->tax->amount : 0) . '</span>',
            'fees' => '<span class="text-dark">' . getAmount($trx->fees) . '</span>',
            'total' => '<strong>' . getAmount($trx->total) . '</strong>',
            'type' => $trx->type_badge,
            'status' => view('admin.financial.transactions.draw.status', compact('trx'))->render(),
            'created_at' => '<span class="text-muted">' . dateFormat($trx->created_at) . '</span>',
            'actions' => view('admin.financial.transactions.draw.actions', [
                'trx' => $trx,
                'isTrash' => request()->routeIs('*.trash.*')
            ])->render(),
            'DT_RowClass' => ($trx->trashed() && !$trx->isArchivedByAdmin()) ? 'trashed-row' : ''
        ];
    }

    /**
     * Apply filters to the transactions query for DataTables.
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();
        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('id', $search);
            });
        }

        // Apply column-specific filters from our custom filter system
        if ($filters = $request->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '6': // Type
                        $query->where('type', $value);
                        break;
                    case '7': // Status
                        $query->where('status', $value);
                        break;
                    case '8': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) {
                                $query->whereDate('created_at', '>=', $value['from']);
                            }
                            if (!empty($value['to'])) {
                                $query->whereDate('created_at', '<=', $value['to']);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to the transactions query for DataTables.
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
                if ($columnName === 'user.firstname') {
                    $query->join('users', 'transactions.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('transactions.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Get transaction statistics counters with percentage changes.
     *
     * @return array<string, mixed>
     */
    private function getTransactionCounters(): array
    {
        // Current counts
        $counters['total_transactions'] = Transaction::withUserTrashed()->count();
        $counters['paid_transactions'] = Transaction::withUserTrashed()->where('status', TransactionStatus::PAID)->count();
        $counters['pending_transactions'] = Transaction::withUserTrashed()->where('status', TransactionStatus::PENDING)->count();
        $counters['cancelled_transactions'] = Transaction::withUserTrashed()->where('status', TransactionStatus::CANCELLED)->count();

        // Previous week counts for percentage calculation
        $lastWeekStart = now()->subDays(7);

        $previousWeekTotal = Transaction::withUserTrashed()->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekPaid = Transaction::withUserTrashed()->where('status', TransactionStatus::PAID)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekPending = Transaction::withUserTrashed()->where('status', TransactionStatus::PENDING)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekCancelled = Transaction::withUserTrashed()->where('status', TransactionStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        // Calculate percentages
        $counters['total_transactions_percent'] = $previousWeekTotal > 0
            ? round((($counters['total_transactions'] - $previousWeekTotal) / $previousWeekTotal) * 100)
            : ($counters['total_transactions'] > 0 ? 100 : 0);

        $counters['paid_transactions_percent'] = $previousWeekPaid > 0
            ? round((($counters['paid_transactions'] - $previousWeekPaid) / $previousWeekPaid) * 100)
            : ($counters['paid_transactions'] > 0 ? 100 : 0);

        $counters['pending_transactions_percent'] = $previousWeekPending > 0
            ? round((($counters['pending_transactions'] - $previousWeekPending) / $previousWeekPending) * 100)
            : ($counters['pending_transactions'] > 0 ? 100 : 0);

        $counters['cancelled_transactions_percent'] = $previousWeekCancelled > 0
            ? round((($counters['cancelled_transactions'] - $previousWeekCancelled) / $previousWeekCancelled) * 100)
            : ($counters['cancelled_transactions'] > 0 ? 100 : 0);

        return $counters;
    }

    /**
     * Apply filters to the transactions query
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('trx')) {
            $query->where('id', $request->trx);
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    /**
     * Check premium access for transaction
     */
    private function checkPremiumAccess(Transaction $transaction): void
    {
        abort_if($transaction->isTypePremium() && !isPremiumAvailable(), 404);
    }
}
