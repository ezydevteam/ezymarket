<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\ReferralEarningStatus;
use App\Models\ReferralEarning;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * Referral Earning Analytics Controller
 *
 * Handles admin analytics and management for referral earning records.
 */
class ReferralEarningController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of referral earnings with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getReferralCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);

            $query = ReferralEarning::query()->with(['referral.user', 'seller', 'sale.product']);
            $referralCount = $query->count();

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
                    'recordsTotal' => $referralCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.records.referral-earnings.index', compact('counters', 'columns', 'filters', 'referralCount'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the referral earning details modal.
     */
    public function detailsModal(ReferralEarning $earning): View
    {
        $earning->load(['referral.user', 'seller', 'sale.product']);
        return view('admin.records.referral-earnings.modals.details', compact('earning'));
    }

    /**
     * Remove the specified referral earning.
     */
    public function destroy(ReferralEarning $earning): JsonResponse
    {
        $earning->delete();
        return $this->successJson(translate('Referral earning record deleted successfully'));
    }

    /**
     * Bulk delete referral earnings.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                return ReferralEarning::whereIn('id', $ids)->delete();
            },
            ReferralEarning::class,
            translate(':count referral earning(s) deleted successfully'),
            translate('An error occurred while deleting referral earnings')
        );
    }

    /**
     * Apply DataTables filters.
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['id', 'referral', 'seller', 'status'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('referral_earnings.id', 'like', "%{$search}%")
                    ->orWhereHas('referral.user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
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
                        $query->where('referral_earnings.status', $value);
                        break;
                    case '4': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('referral_earnings.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('referral_earnings.created_at', '<=', $value['to']);
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
                if ($columnName === 'referred_user.firstname') {
                    $query->join('referrals', 'referral_earnings.referral_id', '=', 'referrals.id')
                        ->join('users as referred_users', 'referrals.user_id', '=', 'referred_users.id')
                        ->orderBy('referred_users.firstname', $dir)
                        ->select('referral_earnings.*');
                } elseif ($columnName === 'seller.firstname') {
                    $query->join('users as sellers', 'referral_earnings.seller_id', '=', 'sellers.id')
                        ->orderBy('sellers.firstname', $dir)
                        ->select('referral_earnings.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a referral earning row for DataTables.
     */
    private function formatEarningRow(ReferralEarning $earning): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $earning->id . '">',
            'referred_user' => view('components.user', ['user' => $earning->referral?->user, 'avatarSize' => 'sm'])->render(),
            'seller' => view('components.user', ['user' => $earning->seller, 'avatarSize' => 'sm'])->render(),
            'amount' => '<div class="fw-bold text-dark">' . getAmount((float) $earning->seller_earning) . '</div>',
            'status' => '<span class="status-badge ' . $earning->status->badgeClass() . '">' . $earning->status->label() . '</span>',
            'created_at' => '<div class="text-muted">' . dateFormat($earning->created_at) . '</div>',
            'actions' => view('admin.records.referral-earnings.draw.actions', compact('earning'))->render(),
        ];
    }

    /**
     * Get DataTable columns configuration.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'no-export'],
            ['data' => 'referred_user', 'name' => 'referred_user.firstname', 'title' => translate('Referred User'), 'orderable' => true],
            ['data' => 'seller', 'name' => 'seller.firstname', 'title' => translate('Referred By'), 'orderable' => true],
            ['data' => 'amount', 'name' => 'seller_earning', 'title' => translate('Earning'), 'orderable' => true, 'class' => 'text-center'],
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
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], ReferralEarningStatus::cases()),
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
     * Apply search and filter criteria to the query (for deep-linking)
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('id')) {
            $query->where('referral_earnings.id', $request->id);
        }

        if ($request->filled('status')) {
            $query->where('referral_earnings.status', $request->status);
        }

        if ($request->filled('seller')) {
            $query->where('referral_earnings.seller_id', $request->seller);
        }

        if ($request->filled('referral')) {
            $query->where('referral_earnings.referral_id', $request->referral);
        }
    }

    /**
     * Calculate referral earning statistics counters
     */
    private function getReferralCounters(): array
    {
        // Current counts
        $totalCount = ReferralEarning::count();
        $activeCount = ReferralEarning::where('status', ReferralEarningStatus::ACTIVE)->count();
        $refundedCount = ReferralEarning::where('status', ReferralEarningStatus::REFUNDED)->count();
        $cancelledCount = ReferralEarning::where('status', ReferralEarningStatus::CANCELLED)->count();

        $counters = [
            'total' => [
                'total' => $totalCount,
                'amount' => ReferralEarning::sum('seller_earning'),
            ],
            'active' => [
                'total' => $activeCount,
                'amount' => ReferralEarning::where('status', ReferralEarningStatus::ACTIVE)->sum('seller_earning'),
            ],
            'refunded' => [
                'total' => $refundedCount,
                'amount' => ReferralEarning::where('status', ReferralEarningStatus::REFUNDED)->sum('seller_earning'),
            ],
            'cancelled' => [
                'total' => $cancelledCount,
                'amount' => ReferralEarning::where('status', ReferralEarningStatus::CANCELLED)->sum('seller_earning'),
            ],
        ];

        // Previous week for percentage
        $lastWeekStart = now()->subDays(7);

        $prevTotal = ReferralEarning::where('created_at', '<', $lastWeekStart)->count();
        $prevActive = ReferralEarning::where('status', ReferralEarningStatus::ACTIVE)->where('created_at', '<', $lastWeekStart)->count();
        $prevRefunded = ReferralEarning::where('status', ReferralEarningStatus::REFUNDED)->where('created_at', '<', $lastWeekStart)->count();
        $prevCancelled = ReferralEarning::where('status', ReferralEarningStatus::CANCELLED)->where('created_at', '<', $lastWeekStart)->count();

        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $counters['total']['percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['active']['percent'] = $calculatePercent($activeCount, $prevActive);
        $counters['refunded']['percent'] = $calculatePercent($refundedCount, $prevRefunded);
        $counters['cancelled']['percent'] = $calculatePercent($cancelledCount, $prevCancelled);

        return $counters;
    }
}
