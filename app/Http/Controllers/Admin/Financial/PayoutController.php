<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Enums\{PayoutStatus, StatementType};
use App\Models\Financial\{Payout, Statement};
use App\Facades\Notification;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\View\View;
use Illuminate\Http\{Request, JsonResponse};

class PayoutController extends Controller
{
    use HandlesValidation;

    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getPayoutCounters();
        $trashedCount = Payout::onlyAdminTrashed()->count();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        $query = Payout::withUserTrashed()
            ->with(['seller', 'payoutMethod'])
            ->where('status', '!=', PayoutStatus::RECALLED);
        $payoutsCount = $query->count();

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $this->applyDataTableFilters($query);

                $totalRecords = (clone $query)->count();

                // Sort
                if ($request->has('order')) {
                    $orderColumnIndex = $request->input('order.0.column');
                    $orderColumnName = $request->input("columns.{$orderColumnIndex}.name") ?: $request->input("columns.{$orderColumnIndex}.data");
                    $orderDirection = $request->input('order.0.dir');
                    $query->orderBy($orderColumnName, $orderDirection);
                } else {
                    $query->latest();
                }

                // Paginate
                $length = $request->input('length', 10);
                $start = $request->input('start', 0);
                $payouts = $query->skip($start)->take($length)->get();

                $data = $payouts->map(fn($payout) => $this->formatPayoutRow($payout));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data,
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('admin.financial.payouts.index', compact('counters', 'trashedCount', 'payoutsCount', 'columns', 'filters'));
    }

    /**
     * Update the payout status.
     */
    public function updateStatus(Request $request, Payout $payout): JsonResponse
    {
        try {
            // Validate request with status and admin_note
            if ($error = $this->getValidationRules($request, true)) {
                return $error;
            }

            $newStatus = PayoutStatus::from($request->status);

            // Check if status actually changed
            if ($newStatus === $payout->status) {
                return $this->errorJson('The status has not changed');
            }

            if ($payout->status === PayoutStatus::PENDING && $newStatus === PayoutStatus::COMPLETED) {
                throw new Exception(translate('Payout can not be completed from pending status. Approve it first.'));
            }

            // Validate status transition using hierarchy
            if (!$this->isValidStatusTransition($payout->status, $newStatus)) {
                return $this->errorJson(
                    ':current status can not revert to :new',
                    [],
                    400,
                    ['current' => $payout->status_name, 'new' => $newStatus->label()]
                );
            }

            // Update payout
            $payout->status = PayoutStatus::from($request->status);

            if ($request->filled('admin_note')) {
                $payout->admin_note = $request->admin_note;
            }

            $payout->save();

            $user = $payout->user;

            // Return balance to user if payout is returned
            if ($payout->isReturned()) {
                $user->increment('balance', $payout->amount);
            }

            // Send status notification
            Notification::sendPayoutStatusNotification($payout);

            // Create debit statement when payout is completed
            if ($payout->isCompleted()) {
                $this->createPayoutStatement($payout);
            }

            return $this->successJson('Payout :status_name Successfully', [], 200, ['status_name' => $payout->status_name]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Mark multiple payouts as returned.
     */
    public function bulkReturn(Request $request): JsonResponse
    {
        if ($error = $this->getValidationRules($request)) {
            return $error;
        }

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($request) {
                $payouts = Payout::whereIn('id', $ids)->get();

                $payoutsToReturn = $payouts->filter(function ($payout) {
                    return $this->isValidStatusTransition($payout->status, PayoutStatus::RETURNED);
                });

                if ($payoutsToReturn->isEmpty()) {
                    throw new Exception(translate('No valid payouts to mark as returned'));
                }

                foreach ($payoutsToReturn as $payout) {
                    $payout->update([
                        'status' => PayoutStatus::RETURNED,
                        'admin_note' => $request->admin_note,
                    ]);
                    $payout->user->increment('balance', $payout->amount);
                    Notification::sendPayoutStatusNotification($payout);
                }

                return $payoutsToReturn->count();
            },
            Payout::class,
            ':count payout(s) have been marked as returned successfully',
            'Failed to mark payouts as returned'
        );
    }

    /**
     * Mark multiple payouts as approved.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        if ($error = $this->getValidationRules($request)) {
            return $error;
        }

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($request) {
                $payouts = Payout::whereIn('id', $ids)->get();

                $payoutsToApprove = $payouts->filter(function ($payout) {
                    return $this->isValidStatusTransition($payout->status, PayoutStatus::APPROVED);
                });

                if ($payoutsToApprove->isEmpty()) {
                    throw new Exception(translate('No valid payouts to mark as approved'));
                }

                foreach ($payoutsToApprove as $payout) {
                    $payout->update([
                        'status' => PayoutStatus::APPROVED,
                        'admin_note' => $request->admin_note,
                    ]);
                    Notification::sendPayoutStatusNotification($payout);
                }

                return $payoutsToApprove->count();
            },
            Payout::class,
            ':count payout(s) have been marked as approved successfully',
            'Failed to mark payouts as approved'
        );
    }

    /**
     * Mark multiple payouts as completed.
     */
    public function bulkCompleted(Request $request): JsonResponse
    {
        if ($error = $this->getValidationRules($request)) {
            return $error;
        }

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($request) {
                $payouts = Payout::whereIn('id', $ids)->get();

                $payoutsToComplete = $payouts->filter(function ($payout) {
                    return $this->isValidStatusTransition($payout->status, PayoutStatus::COMPLETED);
                });

                if ($payoutsToComplete->isEmpty()) {
                    throw new Exception(translate('No valid payouts to mark as completed'));
                }

                foreach ($payoutsToComplete as $payout) {
                    $payout->update([
                        'status' => PayoutStatus::COMPLETED,
                        'admin_note' => $request->admin_note,
                    ]);
                    $this->createPayoutStatement($payout);
                    Notification::sendPayoutStatusNotification($payout);
                }

                return $payoutsToComplete->count();
            },
            Payout::class,
            ':count payout(s) have been marked as completed successfully',
            'Failed to mark payouts as completed'
        );
    }

    /**
     * Cancel multiple payouts.
     */
    public function bulkCancel(Request $request): JsonResponse
    {
        if ($error = $this->getValidationRules($request)) {
            return $error;
        }

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($request) {
                $payouts = Payout::whereIn('id', $ids)->get();

                $payoutsToCancel = $payouts->filter(function ($payout) {
                    return $this->isValidStatusTransition($payout->status, PayoutStatus::CANCELLED);
                });

                if ($payoutsToCancel->isEmpty()) {
                    throw new Exception(translate('No valid payouts to cancel'));
                }

                foreach ($payoutsToCancel as $payout) {
                    $payout->update([
                        'status' => PayoutStatus::CANCELLED,
                        'admin_note' => $request->admin_note,
                    ]);
                    Notification::sendPayoutStatusNotification($payout);
                }

                return $payoutsToCancel->count();
            },
            Payout::class,
            ':count payout(s) have been cancelled successfully',
            'Failed to cancel payouts'
        );
    }

    /**
     * Delete multiple payouts.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $payouts = Payout::withTrashed()->whereIn('id', $ids)->get();

                if ($payouts->isEmpty()) {
                    throw new Exception(translate('No valid payouts found to delete'));
                }

                foreach ($payouts as $payout) {
                    if ($payout->trashed()) {
                        if ($payout->isArchivedByAdmin()) {
                            $payout->forceDelete();
                        } else {
                            $payout->moveToAdminTrash();
                        }
                    } else {
                        // If payout was approved or pending, return balance to user before soft-deletion
                        if ($payout->isPending() || $payout->isApproved()) {
                            $payout->user->increment('balance', $payout->amount);
                        }
                        $payout->delete();
                    }
                }

                return $payouts->count();
            },
            Payout::class,
            ':count payout(s) processed successfully',
            'Failed to delete payouts'
        );
    }

    /**
     * Delete the payout.
     */
    public function destroy(Payout $payout): JsonResponse
    {
        try {
            if ($payout->trashed()) {
                if ($payout->isArchivedByAdmin()) {
                    $payout->forceDelete();
                    return $this->successJson('Payout permanently deleted successfully');
                }

                $payout->moveToAdminTrash();
                return $this->successJson('Payout moved to administrative trash successfully');
            }

            // If payout was approved or pending, return balance to user
            if ($payout->isPending() || $payout->isApproved()) {
                $payout->user->increment('balance', $payout->amount);
            }

            $payout->delete();

            return $this->successJson('Payout moved to trash successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Restore multiple deleted payouts.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $payouts = Payout::onlyTrashed()->whereIn('id', $ids)->get();

                if ($payouts->isEmpty()) {
                    throw new Exception(translate('No trashed payouts found to restore'));
                }

                $successCount = 0;
                foreach ($payouts as $payout) {
                    $payout->restore();
                    $successCount++;
                }

                return $successCount;
            },
            Payout::class,
            ':count of :total payout(s) have been restored successfully',
            'Failed to restore payouts'
        );
    }

    /**
     * Restore a deleted payout.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $payout = Payout::onlyTrashed()->findOrFail($id);
            $payout->restore();

            return $this->successJson('Payout restored successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified payout in a modal.
     */
    public function detailsModal(Payout $payout): string
    {
        $payout->load(['seller', 'payoutMethod']);

        return view('admin.financial.payouts.modals.details', compact('payout'))->render();
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
                if ($columnName === 'seller.firstname') {
                    $query->join('users', 'payouts.seller_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('payouts.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Display trashed (soft deleted) payouts.
     */
    public function trash(Request $request): View|JsonResponse
    {
        $counters = $this->getPayoutCounters();
        $trashedCount = Payout::onlyAdminTrashed()->count();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters($request);

        $query = Payout::onlyAdminTrashed()->with(['seller', 'payoutMethod']);

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $this->applyDataTableFilters($query);

                $totalRecords = (clone $query)->count();

                // Sort
                if ($request->has('order')) {
                    $orderColumnIndex = $request->input('order.0.column');
                    $orderColumnName = $request->input("columns.{$orderColumnIndex}.name") ?: $request->input("columns.{$orderColumnIndex}.data");
                    $orderDirection = $request->input('order.0.dir');
                    $query->orderBy($orderColumnName, $orderDirection);
                } else {
                    $query->latest();
                }

                // Paginate
                $length = $request->input('length', 10);
                $start = $request->input('start', 0);
                $payouts = $query->skip($start)->take($length)->get();

                $data = $payouts->map(fn($payout) => $this->formatPayoutRow($payout));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data,
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('admin.financial.payouts.trash', compact('counters', 'trashedCount', 'columns', 'filters'));
    }

    /**
     * Permanently delete the payout.
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        try {
            $payout = Payout::onlyTrashed()->findOrFail($id);
            $payout->forceDelete();

            return $this->successJson('Payout permanently deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Get payout statistics counters with percentage changes.
     *
     * @return array<string, mixed>
     */
    private function getPayoutCounters(): array
    {
        // Current counts
        $counters['total_payouts'] = Payout::withUserTrashed()->where('status', '!=', PayoutStatus::RECALLED)->count();
        $counters['pending_payouts'] = Payout::withUserTrashed()->where('status', PayoutStatus::PENDING)->count();
        $counters['approved_payouts'] = Payout::withUserTrashed()->where('status', PayoutStatus::APPROVED)->count();
        $counters['completed_payouts'] = Payout::withUserTrashed()->where('status', PayoutStatus::COMPLETED)->count();
        $counters['returned_payouts'] = Payout::withUserTrashed()->where('status', PayoutStatus::RETURNED)->count();
        $counters['cancelled_payouts'] = Payout::withUserTrashed()->where('status', PayoutStatus::CANCELLED)->count();

        // Previous week counts for percentage calculation
        $lastWeekStart = now()->subDays(7);

        $previousWeekTotal = Payout::withUserTrashed()->where('status', '!=', PayoutStatus::RECALLED)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekPending = Payout::withUserTrashed()->where('status', PayoutStatus::PENDING)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekApproved = Payout::withUserTrashed()->where('status', PayoutStatus::APPROVED)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekCompleted = Payout::withUserTrashed()->where('status', PayoutStatus::COMPLETED)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekReturned = Payout::withUserTrashed()->where('status', PayoutStatus::RETURNED)->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekCancelled = Payout::withUserTrashed()->where('status', PayoutStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        // Calculate percentages
        $counters['total_payouts_percent'] = $previousWeekTotal > 0
            ? round((($counters['total_payouts'] - $previousWeekTotal) / $previousWeekTotal) * 100)
            : ($counters['total_payouts'] > 0 ? 100 : 0);

        $counters['pending_payouts_percent'] = $previousWeekPending > 0
            ? round((($counters['pending_payouts'] - $previousWeekPending) / $previousWeekPending) * 100)
            : ($counters['pending_payouts'] > 0 ? 100 : 0);

        $counters['approved_payouts_percent'] = $previousWeekApproved > 0
            ? round((($counters['approved_payouts'] - $previousWeekApproved) / $previousWeekApproved) * 100)
            : ($counters['approved_payouts'] > 0 ? 100 : 0);

        $counters['completed_payouts_percent'] = $previousWeekCompleted > 0
            ? round((($counters['completed_payouts'] - $previousWeekCompleted) / $previousWeekCompleted) * 100)
            : ($counters['completed_payouts'] > 0 ? 100 : 0);

        $counters['returned_payouts_percent'] = $previousWeekReturned > 0
            ? round((($counters['returned_payouts'] - $previousWeekReturned) / $previousWeekReturned) * 100)
            : ($counters['returned_payouts'] > 0 ? 100 : 0);

        $counters['cancelled_payouts_percent'] = $previousWeekCancelled > 0
            ? round((($counters['cancelled_payouts'] - $previousWeekCancelled) / $previousWeekCancelled) * 100)
            : ($counters['cancelled_payouts'] > 0 ? 100 : 0);

        return $counters;
    }

    /**
     * Format a single payout model row for DataTables.
     */
    private function formatPayoutRow(Payout $payout): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $payout->id . '">',
            'user' => view('components.user', ['user' => $payout->seller, 'avatarSize' => 'sm', 'fontWeight' => 'normal'])->render(),
            'requested' => '<strong class="text-success">' . getAmount($payout->amount) . '</strong>',
            'fees' => '<span class="text-danger">' . $payout->fees_label . '</span>',
            'transferable' => '<strong class="text-primary">' . getAmount($payout->net_amount) . '</strong>',
            'method' => $payout->payout_method_label,
            'status' => $payout->status_badge,
            'created_at' => '<span class="text-muted">' . dateFormat($payout->created_at) . '</span>',
            'actions' => view('admin.financial.payouts.draw.actions', [
                'payout' => $payout,
                'isTrash' => request()->routeIs('*.trash.*')
            ])->render(),
            'DT_RowClass' => ($payout->trashed() && !$payout->isArchivedByAdmin()) ? 'trashed-row' : ''
        ];
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'user', 'name' => 'seller.firstname', 'title' => translate('User Details'), 'orderable' => true, 'searchable' => true],
            ['data' => 'requested', 'name' => 'amount', 'title' => translate('Requested'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'fees', 'name' => 'fees', 'title' => translate('Fees'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'transferable', 'name' => 'amount', 'title' => translate('Transferable'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'method', 'name' => 'method', 'title' => translate('Method'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'text-end'],
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
                'label' => translate('Payout Status'),
                'options' => collect(PayoutStatus::cases())->filter(fn($status) => $status !== PayoutStatus::RECALLED)->map(fn($status) => ['value' => $status->value, 'label' => $status->label()])->values()->toArray(),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '7', // Date column index
                'label' => translate('Date Range'),
                'value' => [
                    'from' => $request->query('from'),
                    'to' => $request->query('to')
                ]
            ]
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
                $q->whereHas('seller', function ($uq) use ($search) {
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
                    case '6': // Status
                        $query->where('status', $value);
                        break;
                    case '7': // Date Range
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
     * Apply filters to the transactions query
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('payout')) {
            $query->where('id', $request->payout);
        }

        if ($request->filled('user')) {
            $query->where('seller_id', $request->user);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    /**
     * Validate admin note requirement with optional status validation.
     *
     * @param bool $includeStatus Whether to include status validation
     * @return JsonResponse|null Returns JsonResponse on validation failure, null on success
     */
    private function getValidationRules(Request $request, bool $includeStatus = false): ?JsonResponse
    {
        $rules = [];

        if ($request->status === PayoutStatus::PENDING->value) {
            $rules['admin_note'] = ['nullable', 'string', 'block_patterns', 'max:1000'];
        } else {
            $rules['admin_note'] = ['required', 'string', 'block_patterns', 'max:1000'];
        }

        if ($includeStatus) {
            $rules['status'] = ['required', 'string', 'in:' . implode(',', PayoutStatus::toArray())];
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        return null;
    }

    /**
     * Validate if a status transition is allowed based on hierarchy rules.
     *
     * Hierarchy:
     * - PENDING → APPROVED, RETURNED, CANCELLED
     * - APPROVED → COMPLETED, RETURNED, CANCELLED
     * - COMPLETED, RETURNED, CANCELLED → Terminal (no transitions)
     */
    private function isValidStatusTransition(PayoutStatus $current, PayoutStatus $new): bool
    {
        // Terminal states cannot be changed
        if (in_array($current, [PayoutStatus::COMPLETED, PayoutStatus::RETURNED, PayoutStatus::CANCELLED], true)) {
            return false;
        }

        // PENDING can transition to: APPROVED, RETURNED, CANCELLED
        if ($current === PayoutStatus::PENDING) {
            return in_array($new, [PayoutStatus::APPROVED, PayoutStatus::RETURNED, PayoutStatus::CANCELLED], true);
        }

        // APPROVED can transition to: COMPLETED, RETURNED, CANCELLED
        if ($current === PayoutStatus::APPROVED) {
            return in_array($new, [PayoutStatus::COMPLETED, PayoutStatus::RETURNED, PayoutStatus::CANCELLED], true);
        }

        return false;
    }

    /**
     * Create a statement record for completed payout.
     */
    private function createPayoutStatement(Payout $payout): void
    {
        Statement::create([
            'user_id' => $payout->seller_id,
            'title' => translate('[Payout] #:id', ['id' => $payout->id]),
            'amount' => $payout->amount,
            'total' => $payout->transferable_amount,
            'type' => StatementType::DEBIT,
        ]);
    }
}
