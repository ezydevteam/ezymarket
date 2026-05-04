<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Mail;
use Exception;

/**
 * Admin Refund Analytics Controller
 *
 * Manages refund analytics and reporting in the admin panel.
 */
class RefundController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of refunds with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getRefundCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);

            $query = Refund::withUserTrashed()->with(['purchase.product', 'purchase.sale', 'seller', 'user']);
            $refundsCount = $query->count();

            // Handle DataTables AJAX requests
            if ($request->ajax() && $request->has('draw')) {
                $this->applyDataTableFilters($query);

                $totalFiltered = $query->count();

                $this->applyDataTableSorting($query);

                $limit = $request->input('length', 10);
                $offset = $request->input('start', 0);
                $refunds = $query->limit($limit)->offset($offset)->get();

                $data = $refunds->map(fn($refund) => $this->formatRefundRow($refund))->toArray();

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $refundsCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.records.refunds.index', compact('counters', 'columns', 'filters', 'refundsCount'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified refund details modal
     */
    public function detailsModal(Refund $refund): View
    {
        $refund->load(['purchase.product', 'purchase.sale', 'seller', 'user', 'replies.user']);
        return view('admin.records.refunds.modals.details', compact('refund'));
    }

    /**
     * Remove the specified refund
     */
    public function destroy(Refund $refund): JsonResponse
    {
        if ($refund->trashed()) {
            if ($refund->isArchivedByAdmin()) {
                $refund->forceDelete();
                return $this->successJson(translate('Refund permanently deleted successfully'));
            }

            $refund->moveToAdminTrash();
            return $this->successJson(translate('Refund moved to administrative trash successfully'));
        }

        $refund->delete();
        return $this->successJson(translate('Refund moved to trash successfully'));
    }

    /**
     * Restore multiple deleted refunds
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $refunds = Refund::onlyTrashed()->whereIn('id', $ids)->get();
                foreach ($refunds as $refund) {
                    $refund->restore();
                }
                return count($refunds);
            },
            Refund::class,
            translate(':count refund(s) restored successfully'),
            translate('An error occurred while restoring refunds')
        );
    }

    /**
     * Restore a deleted refund
     */
    public function restore(int $id): JsonResponse
    {
        $refund = Refund::onlyTrashed()->findOrFail($id);
        $refund->restore();
        return $this->successJson(translate('Refund restored successfully'));
    }

    /**
     * Display trashed (soft deleted) refunds
     */
    public function trash(): View
    {
        $counters = $this->getRefundCounters();
        $query = Refund::onlyAdminTrashed()->with(['purchase.product', 'purchase.sale', 'seller', 'user']);
        $this->applyFilters($query);
        $refunds = $query->get();
        return view('admin.records.refunds.trash', compact('counters', 'refunds'));
    }

    /**
     * Permanently delete a refund
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        $refund = Refund::onlyTrashed()->findOrFail($id);
        $refund->forceDelete();
        return $this->successJson(translate('Refund permanently deleted successfully'));
    }

    /**
     * Bulk action for refunds (delete/force delete/move to trash)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $refunds = Refund::withTrashed()->whereIn('id', $ids)->get();
                foreach ($refunds as $refund) {
                    if ($refund->trashed()) {
                        if ($refund->isArchivedByAdmin()) {
                            $refund->forceDelete();
                        } else {
                            $refund->moveToAdminTrash();
                        }
                    } else {
                        $refund->delete();
                    }
                }
                return count($refunds);
            },
            Refund::class,
            translate(':count refund(s) processed successfully'),
            translate('An error occurred while processing refunds')
        );
    }

    /**
     * Send email notification to seller about refund
     */
    public function sendEmail(Request $request, Refund $refund): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $seller = $refund->seller;
            Mail::send([], [], function ($message) use ($seller, $request) {
                $message->to($seller->email, $seller->name)
                    ->subject($request->subject)
                    ->html(nl2br(e($request->message)));
            });
            return $this->successJson(translate('Email sent successfully to the seller'));
        } catch (Exception $e) {
            return $this->errorJson(translate('Failed to send email.'));
        }
    }

    /**
     * Apply DataTables filters
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['refund', 'status', 'seller', 'user'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('refunds.id', 'like', "%{$search}%")
                    ->orWhere('refunds.subject', 'like', "%{$search}%")
                    ->orWhereHas('purchase.product', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
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
                    case '5': // Status
                        $query->where('refunds.status', $value);
                        break;
                    case '6': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('refunds.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('refunds.created_at', '<=', $value['to']);
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
                    $query->join('purchases', 'refunds.purchase_id', '=', 'purchases.id')
                        ->join('products', 'purchases.product_id', '=', 'products.id')
                        ->orderBy('products.name', $dir)
                        ->select('refunds.*');
                } elseif ($columnName === 'user.firstname') {
                    $query->join('users', 'refunds.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('refunds.*');
                } elseif ($columnName === 'seller.firstname') {
                    $query->join('users as sellers', 'refunds.seller_id', '=', 'sellers.id')
                        ->orderBy('sellers.firstname', $dir)
                        ->select('refunds.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a refund row for DataTables
     */
    private function formatRefundRow(Refund $refund): array
    {
        $product = $refund->purchase?->product;
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $refund->id . '">',
            'product' => view('components.product', ['product' => $product, 'showSubCategory' => true])->render(),
            'buyer' => view('components.user', ['user' => $refund->user, 'avatarSize' => 'sm'])->render(),
            'seller' => view('components.user', ['user' => $refund->seller, 'avatarSize' => 'sm'])->render(),
            'amount' => '<div class="fw-bold text-dark">' . getAmount((float) ($refund->purchase?->sale?->price ?? 0)) . '</div>',
            'status' => '<span class="status-badge ' . $refund->status->badgeClass() . '">' . $refund->status->label() . '</span>',
            'created_at' => '<div class="text-muted">' . dateFormat($refund->created_at) . '</div>',
            'actions' => view('admin.records.refunds.draw.actions', compact('refund'))->render(),
            'DT_RowClass' => ($refund->trashed() && !$refund->isArchivedByAdmin()) ? 'trashed-row' : ''
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
            ['data' => 'buyer', 'name' => 'user.firstname', 'title' => translate('Buyer'), 'orderable' => true],
            ['data' => 'seller', 'name' => 'seller.firstname', 'title' => translate('Seller'), 'orderable' => true],
            ['data' => 'amount', 'name' => 'amount', 'title' => translate('Amount'), 'orderable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'class' => 'text-center'],
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
                'type' => 'select',
                'column' => '5',
                'label' => translate('Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], RefundStatus::cases()),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '6',
                'label' => translate('Request Date'),
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

        if ($request->filled('refund')) {
            $query->where('refunds.id', $request->refund);
        }

        if ($request->filled('status')) {
            $query->where('refunds.status', $request->status);
        }

        if ($request->filled('seller')) {
            $query->where('refunds.seller_id', $request->seller);
        }

        if ($request->filled('user')) {
            $query->where('refunds.user_id', $request->user);
        }
    }

    /**
     * Calculate refund statistics counters
     */
    private function getRefundCounters(): array
    {
        $baseQuery = Refund::withUserTrashed();

        $totalCount = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', RefundStatus::PENDING)->count();
        $acceptedCount = (clone $baseQuery)->where('status', RefundStatus::ACCEPTED)->count();
        $declinedCount = (clone $baseQuery)->where('status', RefundStatus::DECLINED)->count();

        $sumAmount = function ($status = null) use ($baseQuery) {
            $q = clone $baseQuery;
            if ($status) $q->where('refunds.status', $status);
            return (float) $q->join('purchases', 'refunds.purchase_id', '=', 'purchases.id')
                ->join('sales', 'purchases.sale_id', '=', 'sales.id')
                ->sum('sales.price');
        };

        $counters = [
            'total' => [
                'total' => $totalCount,
                'amount' => $sumAmount(),
            ],
            'pending' => [
                'total' => $pendingCount,
                'amount' => $sumAmount(RefundStatus::PENDING),
            ],
            'accepted' => [
                'total' => $acceptedCount,
                'amount' => $sumAmount(RefundStatus::ACCEPTED),
            ],
            'declined' => [
                'total' => $declinedCount,
                'amount' => $sumAmount(RefundStatus::DECLINED),
            ],
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);
        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $prevTotal = (clone $baseQuery)->where('created_at', '<', $lastWeekStart)->count();
        $prevPending = (clone $baseQuery)->where('status', RefundStatus::PENDING)->where('created_at', '<', $lastWeekStart)->count();
        $prevAccepted = (clone $baseQuery)->where('status', RefundStatus::ACCEPTED)->where('created_at', '<', $lastWeekStart)->count();
        $prevDeclined = (clone $baseQuery)->where('status', RefundStatus::DECLINED)->where('created_at', '<', $lastWeekStart)->count();

        $counters['total']['percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['pending']['percent'] = $calculatePercent($pendingCount, $prevPending);
        $counters['accepted']['percent'] = $calculatePercent($acceptedCount, $prevAccepted);
        $counters['declined']['percent'] = $calculatePercent($declinedCount, $prevDeclined);

        return $counters;
    }
}
